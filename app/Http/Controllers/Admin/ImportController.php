<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookTranslate;
use App\Models\Characteristic;
use App\Models\CharacteristicTranslate;
use App\Models\CharacteristicValue;
use App\Models\CharacteristicValueTranslate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\IValueBinder;

class ImportController extends Controller
{
    public function index() {
        $booksToApprove = Book::where('need_approve', true)
            ->with('translates')
            ->get()
            ->transform(function ($book) {
                $book->setRelation('translates', $book->translates->keyBy('lang'));
                return $book;
            });

        return view('admin.import.index', [
            'title' => 'Імпорт',
            'booksToApprove' => $booksToApprove
        ]);
    }

    public function approve() {
        DB::table('books')->where('need_approve', true)->update(['need_approve' => false]);
        DB::table('characteristics')->where('need_approve', true)->update(['need_approve' => false]);
        DB::table('char_vals')->where('need_approve', true)->update(['need_approve' => false]);

        return redirect()->route('admin.import')->with('success', 'Дані успішно підтверджені');
    }

    public function add(Request $request) {
        $langs = ['ua', 'en'];

        // Очистка предыдущих не одобренных данных перед новым импортом
        Characteristic::where('need_approve', true)->delete();
        CharacteristicValue::where('need_approve', true)->delete();
        Book::where('need_approve', true)->delete();

        $request->validate(['exel_file' => 'required|mimes:xlsx']);

        $data = Excel::toArray(new StringValueBinder, $request->file('exel_file'))[0];
        if (empty($data)) return back()->with('error', 'Файл пуст');

        $headers = array_shift($data);
        array_shift($headers); // Убираем ID/A колонку

        $static_book_fields = ['Порядковий номер видання' => 'sort'];
        $static_book_translates_fields = [
            'Назва' => 'name',
            'Анотація' => 'anotation',
        ];

        $fields_chars_exception_with_one_lang = [
            'Рік(роки) / Year(s)' => ['ua' => 'Рік(роки)', 'en' => 'Year(s)'],
            'ISBN' => ['ua' => 'ISBN', 'en' => 'ISBN'],
            'ISSN' => ['ua' => 'ISSN', 'en' => 'ISSN'],
            'Number of volumes' => ['ua' => 'Кількість томів', 'en' => 'Number of volumes'],
            'Volume / Issue' => ['ua' => 'Том / Випуск', 'en' => 'Volume / Issue'],
            'DOI' => ['ua' => 'DOI', 'en' => 'DOI'],
            'Website' => ['ua' => 'Website', 'en' => 'Website'],
            'URL' => ['ua' => 'URL', 'en' => 'URL'],
        ];

        $fields_to_ignore = ['Дизайн', 'Ключові слова', 'Key words'];

        foreach ($data as $bookRow) {
            array_shift($bookRow); // Убираем первую колонку (ID)
    
            if (empty($bookRow[0])) continue;
        
            // --- ПРОВЕРКА НА ПОВТОРЯЮЩИЙСЯ ЗАГОЛОВОК ---
            // Если в колонке, где должно быть Название, написано "Назва" 
            // или в Типе издания написано "Тип видання" — пропускаем всю строку.
            $checkName = trim($bookRow[8] ?? ''); // Индекс колонки "Назва" (проверьте в вашем файле)
            if ($checkName === 'Назва' || $checkName === 'Title') {
                continue; 
            }
            
            // Дополнительная проверка: если это пустая строка по ключевым полям
            if (empty(trim($bookRow[3] ?? '')) && empty(trim($bookRow[4] ?? ''))) continue;

            $current_book_static_fields = [];
            $current_book_static_translates = [];
            $current_book_dynamic_translates = [];

            for ($i = 0; $i < count($bookRow); $i++) {
                $current_val = trim($bookRow[$i] ?? '');

                $header_name = trim($headers[$i] ?? '');

                if (!$header_name) continue;

               // сбор статических полей без перевода ( пока только sort )
                if (isset($static_book_fields[$header_name])) {
                    $current_book_static_fields[$static_book_fields[$header_name]] = $current_val;
                    continue;
                }

                // Статические переводы (Название, Аннотация)
                if (isset($static_book_translates_fields[$header_name])) {
                    $field_model_name = $static_book_translates_fields[$header_name];
                    $val_ua = $current_val;
                    $val_en = trim($bookRow[$i + 1] ?? '');
                    
                    $current_book_static_translates[$header_name] = [
                        'ua' => $val_ua ?: $val_en,
                        'en' => $val_en ?: $val_ua,
                        'field_model_name' => $field_model_name
                    ];
                    $i++; continue;
                }

                if (in_array($header_name, $fields_to_ignore)) continue;

                // Динамические поля без перевода (ISBN и т.д.)
                if (isset($fields_chars_exception_with_one_lang[$header_name])) {
                    $current_book_dynamic_translates[] = [
                        'header_ua' => $fields_chars_exception_with_one_lang[$header_name]['ua'],
                        'header_en' => $fields_chars_exception_with_one_lang[$header_name]['en'],
                        'val_ua' => $current_val,
                        'val_en' => $current_val
                    ];
                    continue;
                }

                // Динамические поля с переводом
                $val_ua = $current_val;
                $val_en = trim($bookRow[$i + 1] ?? '');
                $header_ua = $header_name;
                $header_en = trim($headers[$i + 1] ?? '');

                if ($header_ua && $header_en) {
                    $current_book_dynamic_translates[] = [
                        'header_ua' => $header_ua,
                        'header_en' => $header_en,
                        'val_ua' => $val_ua ?: $val_en,
                        'val_en' => $val_en ?: $val_ua
                    ];
                    $i++;
                }
            }

            // --- 2. СОЗДАНИЕ КНИГИ ---
            $book = new Book();
            $book->sort = (!empty($current_book_static_fields['sort'])) ? $current_book_static_fields['sort'] : 1;
            $book->need_approve = true;
            $book->save();

            foreach ($langs as $lang) {
                $book_translate = new BookTranslate();
                $book_translate->book_id = $book->id;
                $book_translate->lang = $lang;
                
                $raw_name = $current_book_static_translates['Назва'][$lang] ?? 'no-name';
                $slug = Str::slug($raw_name);
                $slug_i = 1;
                while (BookTranslate::where('slug', $slug)->exists()) {
                    $slug = Str::slug($raw_name . '-' . $slug_i++);
                }
                $book_translate->slug = $slug;

                foreach ($current_book_static_translates as $trans_data) {
                    $book_translate[$trans_data['field_model_name']] = $trans_data[$lang];
                }
                $book_translate->save();
            }

            // --- 3. ОБРАБОТКА ХАРАКТЕРИСТИК И ЗНАЧЕНИЙ ---
            $assigned_val_ids = [];

            foreach ($current_book_dynamic_translates as $charArr) {
                $val_ua_name = trim($charArr['val_ua']);
                $char_ua_name = trim($charArr['header_ua']);

                if (empty($val_ua_name) || $val_ua_name === $char_ua_name) {
                    continue;
                }

                // Поиск/Создание Характеристики
                $char_trans = CharacteristicTranslate::where('lang', 'ua')
                    ->where('name', $char_ua_name)
                    ->first();

                if (!$char_trans) {
                    $char = new Characteristic(['active' => 1, 'need_approve' => true, 'sort' => $book->sort]);
                    $char->save();

                    foreach (['ua' => 'header_ua', 'en' => 'header_en'] as $lang => $key) {
                        $c_slug = Str::slug($charArr[$key]);
                        $c_slug_i = 1;
                        while (CharacteristicTranslate::where('slug', $c_slug)->exists()) {
                            $c_slug = Str::slug($charArr[$key] . '-' . $c_slug_i++);
                        }
                        CharacteristicTranslate::create([
                            'characteristic_id' => $char->id,
                            'lang' => $lang,
                            'name' => $charArr[$key],
                            'slug' => $c_slug,
                            'description' => ''
                        ]);
                    }
                } else {
                    $char = Characteristic::find($char_trans->characteristic_id);
                }

                // Поиск/Создание Значения (Поиск привязан к ID характеристики)
                $char_val = CharacteristicValue::where('characteristic_id', $char->id)
                    ->whereHas('translates', function($q) use ($val_ua_name) {
                        $q->where('lang', 'ua')->where('name', $val_ua_name);
                    })->first();

                if (!$char_val) {
                    $char_val = new CharacteristicValue([
                        'characteristic_id' => $char->id, 
                        'active' => 1, 
                        'need_approve' => true
                    ]);
                    $char_val->save();

                    foreach (['ua' => 'val_ua', 'en' => 'val_en'] as $lang => $key) {
                        $v_slug = Str::slug($charArr[$key]);
                        $v_slug_i = 1;
                        while (CharacteristicValueTranslate::where('slug', $v_slug)->exists()) {
                            $v_slug = Str::slug($charArr[$key] . '-' . $v_slug_i++);
                        }
                        CharacteristicValueTranslate::create([
                            'char_val_id' => $char_val->id,
                            'lang' => $lang,
                            'name' => $charArr[$key],
                            'slug' => $v_slug,
                            'description' => ''
                        ]);
                    }
                }
                
                // Защита от дублей связей через ключи массива
                $assigned_val_ids[$char_val->id] = [
                    'book_id' => $book->id,
                    'char_val_id' => $char_val->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Вставка связей для текущей книги
            if (!empty($assigned_val_ids)) {
                DB::table('books_char_val')->insert(array_values($assigned_val_ids));
            }
        }

        return redirect()->route('admin.import')->with('success', 'Імпорт завершено (очікує підтвердження)');
    }
}

class StringValueBinder extends DefaultValueBinder implements IValueBinder
{
    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            if (floor($value) == $value) {
                $value = (string) $value;
            } else {
                $value = (string) round($value, 2);
            }
        }

        $value = $value ?? '';
        $cell->setValueExplicit($value, DataType::TYPE_STRING);
        return true;
    }
}

// class StringValueBinder extends DefaultValueBinder implements IValueBinder
// {
//     public function bindValue(Cell $cell, $value)
//     {
//         if (is_numeric($value)) {
//             $value = (string)(float)$value;
//         }
//         $value = $value ?? '';
//         $cell->setValueExplicit($value, DataType::TYPE_STRING);
//         return true;
//     }
// }