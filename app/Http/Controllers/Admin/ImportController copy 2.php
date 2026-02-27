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

    /**
     * Глубокая очистка строки от мусора Excel и невидимых символов
     */
    private function cleanString($value)
    {
        if (is_null($value)) return '';
        $value = (string)$value;

        // Удаляем неразрывные пробелы и артефакты UTF-8
        $value = str_replace(["\xc2\xa0", "\xa0", "&nbsp;", "\xEF\xBB\xBF"], ' ', $value);
        // Удаляем управляющие символы и лишние пробелы
        $value = str_replace(["\r", "\n", "\t"], ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim($value);
    }

    public function add(Request $request) {
        $langs = ['ua', 'en'];
        $fields_to_ignore = ['Дизайн', 'Ключові слова', 'Key words'];

        // Очистка старых данных
        Characteristic::where('need_approve', true)->delete();
        CharacteristicValue::where('need_approve', true)->delete();
        Book::where('need_approve', true)->delete();

        $request->validate(['exel_file' => 'required|mimes:xlsx']);

        $data = Excel::toArray(new StringValueBinder, $request->file('exel_file'))[0];
        if (empty($data)) return back()->with('error', 'Файл пуст');

        $headers = array_shift($data);
        array_shift($headers); // Убираем колонку ID/A

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

        foreach ($data as $bookRow) {
            array_shift($bookRow); 
    
            if (empty($bookRow[0])) continue;
        
            // Проверка на заголовок (индекс 8 - Назва)
            $checkName = $this->cleanString($bookRow[8] ?? ''); 
            if ($checkName === 'Назва' || $checkName === 'Title' || empty($checkName)) continue;
            
            if (empty(trim($bookRow[3] ?? '')) && empty(trim($bookRow[4] ?? ''))) continue;

            $current_book_static_fields = [];
            $current_book_static_translates = [];
            $current_book_dynamic_translates = [];

            for ($i = 0; $i < count($bookRow); $i++) {
                $current_val = $this->cleanString($bookRow[$i] ?? '');
                $header_name = $this->cleanString($headers[$i] ?? '');

                if (!$header_name) continue;

                if (isset($static_book_fields[$header_name])) {
                    $current_book_static_fields[$static_book_fields[$header_name]] = $current_val;
                    continue;
                }

                if (isset($static_book_translates_fields[$header_name])) {
                    $field_model_name = $static_book_translates_fields[$header_name];
                    $val_ua = $current_val;
                    $val_en = $this->cleanString($bookRow[$i + 1] ?? '');
                    
                    $current_book_static_translates[$header_name] = [
                        'ua' => $val_ua ?: $val_en,
                        'en' => $val_en ?: $val_ua,
                        'field_model_name' => $field_model_name
                    ];
                    $i++; continue;
                }

                if (in_array($header_name, $fields_to_ignore)) continue;

                if (isset($fields_chars_exception_with_one_lang[$header_name])) {
                    $current_book_dynamic_translates[] = [
                        'header_ua' => $fields_chars_exception_with_one_lang[$header_name]['ua'],
                        'header_en' => $fields_chars_exception_with_one_lang[$header_name]['en'],
                        'val_ua' => $current_val,
                        'val_en' => $current_val
                    ];
                    continue;
                }

                $val_ua = $current_val;
                $val_en = $this->cleanString($bookRow[$i + 1] ?? '');
                $header_ua = $header_name;
                $header_en = $this->cleanString($headers[$i + 1] ?? '');

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

            // СОЗДАНИЕ КНИГИ
            $book = new Book();
            $book->sort = (!empty($current_book_static_fields['sort'])) ? (int)$current_book_static_fields['sort'] : 1;
            $book->need_approve = true;
            $book->save();

            foreach ($langs as $lang) {
                $book_translate = new BookTranslate();
                $book_translate->book_id = $book->id;
                $book_translate->lang = $lang;
                
                $raw_name = $current_book_static_translates['Назва'][$lang] ?? 'no-name';
                $slug = Str::slug($raw_name) . '-' . $lang . '-' . $book->id;
                $book_translate->slug = $slug;

                foreach ($current_book_static_translates as $trans_data) {
                    $book_translate[$trans_data['field_model_name']] = $trans_data[$lang];
                }
                $book_translate->save();
            }

            // ХАРАКТЕРИСТИКИ
            $assigned_val_ids = [];

            foreach ($current_book_dynamic_translates as $charArr) {
                $val_ua_name = $this->cleanString($charArr['val_ua']);
                $char_ua_name = $this->cleanString($charArr['header_ua']);

                if (empty($val_ua_name) || $val_ua_name === $char_ua_name) continue;

                $char_trans = CharacteristicTranslate::where('lang', 'ua')
                    ->where('name', $char_ua_name)
                    ->first();

                if (!$char_trans) {
                    $char = new Characteristic(['active' => 1, 'need_approve' => true, 'sort' => $book->sort]);
                    $char->save();

                    foreach (['ua' => 'header_ua', 'en' => 'header_en'] as $lang => $key) {
                        $name = $this->cleanString($charArr[$key]);
                        CharacteristicTranslate::create([
                            'characteristic_id' => $char->id,
                            'lang' => $lang,
                            'name' => $name,
                            'slug' => Str::slug($name) . '-' . $lang . '-' . $char->id,
                            'description' => ''
                        ]);
                    }
                } else {
                    $char = Characteristic::find($char_trans->characteristic_id);
                }

                $char_val = CharacteristicValue::where('characteristic_id', $char->id)
                    ->whereHas('translates', function($q) use ($val_ua_name) {
                        $q->where('lang', 'ua')->where('name', $val_ua_name);
                    })->first();

                if (!$char_val) {
                    $char_val = new CharacteristicValue(['characteristic_id' => $char->id, 'active' => 1, 'need_approve' => true]);
                    $char_val->save();

                    foreach (['ua' => 'val_ua', 'en' => 'val_en'] as $lang => $key) {
                        $name = $this->cleanString($charArr[$key]);
                        CharacteristicValueTranslate::create([
                            'char_val_id' => $char_val->id,
                            'lang' => $lang,
                            'name' => $name,
                            'slug' => Str::slug($name) . '-' . $lang . '-' . $char_val->id,
                            'description' => ''
                        ]);
                    }
                }
                
                $assigned_val_ids[$char_val->id] = [
                    'book_id' => $book->id,
                    'char_val_id' => $char_val->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (!empty($assigned_val_ids)) {
                DB::table('books_char_val')->insert(array_values($assigned_val_ids));
            }
        }

        return redirect()->route('admin.import')->with('success', 'Імпорт завершено');
    }
}

class StringValueBinder extends DefaultValueBinder implements IValueBinder
{
    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            $value = (floor($value) == $value) ? (string)$value : (string)round($value, 2);
        }
        $cell->setValueExplicit($value ?? '', DataType::TYPE_STRING);
        return true;
    }
}