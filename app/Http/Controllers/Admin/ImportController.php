<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookTranslate;
use App\Models\Characteristic;
use App\Models\CharacteristicTranslate;
use App\Models\CharacteristicValue;
use App\Models\CharacteristicValueTranslate;
use App\Models\Tag;
use App\Models\TagTranslate;
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


    private function cleanString($value)
    {
        // if (is_null($value)) return '';
        // $value = (string)$value;
    
        // // убираем невидимые символы Excel
        // $value = str_replace(["\xc2\xa0", "\xa0", "&nbsp;", "\xEF\xBB\xBF"], ' ', $value);
        
        // $value = str_replace(["\r", "\n", "\t"], ' ', $value);
        
        return trim($value);
    }


    private function generateUniqueSlug($modelClass, $name)
    {
        $slug = Str::slug($name);
        if (empty($slug)) {
            $slug = 'n-a';
        }

        $originalSlug = $slug;
        $i = 1;

        while ($modelClass::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $i++;
        }

        return $slug;
    }

    public function add(Request $request) {
        $langs = ['ua', 'en'];
        $fields_to_ignore = ['Дизайн'];
        $tags_headers = ['Ключові слова', 'Key words'];
    
        // 1. Очистка старых данных (только тех, что не одобрены)
        Characteristic::where('need_approve', true)->delete();
        CharacteristicValue::where('need_approve', true)->delete();
        Book::where('need_approve', true)->delete();
    
        $request->validate(['exel_file' => 'required|mimes:xlsx']);
    
        $data = Excel::toArray(new StringValueBinder, $request->file('exel_file'))[0];
        if (empty($data)) return back()->with('error', 'Файл порожній');
    
        $headers = array_shift($data);
        array_shift($headers); // Убираем колонку ID/A
    
        // --- ОПТИМИЗАЦИЯ: ПРЕДЗАГРУЗКА ДАННЫХ В ПАМЯТЬ ---
        DB::disableQueryLog();
    
        // Загружаем слаги, чтобы не делать SELECT EXISTS в цикле
        $usedBookSlugs = DB::table('books_translates')->pluck('slug')->flip()->toArray();
        $usedCharSlugs = DB::table('characteristics_translates')->pluck('slug')->flip()->toArray();
        $usedValSlugs  = DB::table('char_vals_trans')->pluck('slug')->flip()->toArray();
        $usedTagSlugs  = DB::table('tags_translates')->pluck('slug')->flip()->toArray();
    
        // Справочники ID (ускоряют поиск характеристик и тегов в 1000 раз)
        $existingChars = CharacteristicTranslate::where('lang', 'ua')->pluck('characteristic_id', 'name')->toArray();
        $existingTags  = TagTranslate::where('lang', 'ua')->pluck('tag_id', 'name')->toArray();
        
        // Сложный справочник для значений: $existingValues[char_id][val_name_ua] = val_id
        $existingValues = [];
        $allValues = DB::table('char_vals_trans')
            ->join('char_vals', 'char_vals.id', '=', 'char_vals_trans.char_val_id')
            ->where('char_vals_trans.lang', 'ua')
            ->select('char_vals.characteristic_id', 'char_vals.id', 'char_vals_trans.name')
            ->get();
        
        foreach ($allValues as $v) {
            $existingValues[$v->characteristic_id][$v->name] = $v->id;
        }
    
        $static_book_fields = ['Порядковий номер видання' => 'sort'];
        $static_book_translates_fields = [
            'Назва' => 'name',
            'Анотація' => 'anotation',
        ];
    
        $fields_chars_exception_with_one_lang = [
            'Рік(роки) / Year(s)' => ['ua' => 'Рік(роки)', 'en' => 'Year(s)'],
            'ISBN' => ['ua' => 'ISBN', 'en' => 'ISBN'],
            'ISSN' => ['ua' => 'ISSN', 'en' => 'ISBN'],
            'Number of volumes' => ['ua' => 'Кількість томів', 'en' => 'Number of volumes'],
            'Volume / Issue' => ['ua' => 'Том / Випуск', 'en' => 'Volume / Issue'],
            'DOI' => ['ua' => 'DOI', 'en' => 'DOI'],
            'Website' => ['ua' => 'Website', 'en' => 'Website'],
            'URL' => ['ua' => 'URL', 'en' => 'URL'],
        ];
    
        $error_messages = [];
    
        // --- НАЧАЛО ТРАНЗАКЦИИ ---
        DB::beginTransaction();
        try {
            foreach ($data as $index => $bookRow) {
                array_shift($bookRow); 
                if (empty($bookRow[0])) continue;
            
                $checkName = $this->cleanString($bookRow[8] ?? ''); 
                if (empty($checkName)) {
                    $error_messages[] = "Рядок " . ($index + 2) . ": порожня назва";
                    continue;
                }
    
                $current_book_static_fields = [];
                $current_book_static_translates = [];
                $current_book_dynamic_translates = [];
                $current_book_tags_ids = [];
    
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
    
                    // Обработка ТЕГОВ (оптимизировано)
                    if (in_array($header_name, $tags_headers)){
                        $tag_name_ua = explode(',', $current_val);
                        $tag_name_en = explode(',', $this->cleanString($bookRow[$i + 1] ?? ''));
                        $max_t = max(count($tag_name_ua), count($tag_name_en));
    
                        for ($e = 0; $e < $max_t; $e++) {
                            $n_ua = $this->cleanString($tag_name_ua[$e] ?? '');
                            $n_en = $this->cleanString($tag_name_en[$e] ?? '');
                            $search_n = !empty($n_ua) ? $n_ua : $n_en;
                            if (empty($search_n)) continue;
    
                            if (!isset($existingTags[$search_n])) {
                                $tag = Tag::create();
                                foreach (['ua' => $n_ua, 'en' => $n_en] as $l => $v) {
                                    $finalV = !empty($v) ? $v : $search_n;
                                    TagTranslate::create([
                                        'tag_id' => $tag->id, 'lang' => $l, 'name' => $finalV,
                                        'slug' => $this->fastSlug($finalV, $usedTagSlugs)
                                    ]);
                                }
                                $existingTags[$search_n] = $tag->id;
                            }
                            $current_book_tags_ids[] = $existingTags[$search_n];
                        }
                        $i++; continue;
                    }
    
                    if (isset($fields_chars_exception_with_one_lang[$header_name])) {
                        $current_book_dynamic_translates[] = [
                            'header_ua' => $fields_chars_exception_with_one_lang[$header_name]['ua'],
                            'header_en' => $fields_chars_exception_with_one_lang[$header_name]['en'],
                            'val_ua' => $current_val, 'val_en' => $current_val
                        ];
                        continue;
                    }
    
                    $val_ua = $current_val;
                    $val_en = $this->cleanString($bookRow[$i + 1] ?? '');
                    $header_ua = $header_name;
                    $header_en = $this->cleanString($headers[$i + 1] ?? '');
    
                    if ($header_ua && $header_en) {
                        $current_book_dynamic_translates[] = [
                            'header_ua' => $header_ua, 'header_en' => $header_en,
                            'val_ua' => $val_ua ?: $val_en, 'val_en' => $val_en ?: $val_ua
                        ];
                        $i++;
                    }
                }
    
                // --- СОЗДАНИЕ КНИГИ ---
                $book = Book::create([
                    'sort' => (int)($current_book_static_fields['sort'] ?? 1),
                    'need_approve' => true
                ]);
    
                foreach ($langs as $lang) {
                    $raw_name = $current_book_static_translates['Назва'][$lang] ?? 'no-name';
                    
                    // Создаем массив данных для перевода
                    $translateData = [
                        'book_id' => $book->id, // ЯВНО передаем ID книги
                        'lang'    => $lang,
                        'slug'    => $this->fastSlug($raw_name, $usedBookSlugs),
                    ];
    
                    // Динамически добавляем name, anotation и другие поля из $current_book_static_translates
                    foreach ($current_book_static_translates as $trans_data) {
                        $translateData[$trans_data['field_model_name']] = $trans_data[$lang];
                    }
    
                    // Используем DB::table или Model::create с полным массивом данных
                    BookTranslate::create($translateData);
                }
    
                // --- ХАРАКТЕРИСТИКИ (ОПТИМИЗИРОВАНО) ---
                $assigned_val_ids = [];
                foreach ($current_book_dynamic_translates as $charArr) {
                    $c_ua = $this->cleanString($charArr['header_ua']);
                    $v_ua = $this->cleanString($charArr['val_ua']);
                    if (empty($v_ua) || $v_ua === $c_ua) continue;
    
                    // 1. Характеристика
                    if (!isset($existingChars[$c_ua])) {
                        $char = Characteristic::create(['active' => 1, 'need_approve' => true, 'sort' => $book->sort]);
                        foreach (['ua' => 'header_ua', 'en' => 'header_en'] as $lang => $key) {
                            $n = $this->cleanString($charArr[$key]);
                            CharacteristicTranslate::create([
                                'characteristic_id' => $char->id, 'lang' => $lang, 'name' => $n,
                                'slug' => $this->fastSlug($n, $usedCharSlugs), 'description' => ''
                            ]);
                        }
                        $existingChars[$c_ua] = $char->id;
                    }
                    $charId = $existingChars[$c_ua];
    
                    // 2. Значение
                    if (!isset($existingValues[$charId][$v_ua])) {
                        $cv = CharacteristicValue::create(['characteristic_id' => $charId, 'active' => 1, 'need_approve' => true]);
                        foreach (['ua' => 'val_ua', 'en' => 'val_en'] as $lang => $key) {
                            $n = $this->cleanString($charArr[$key]);
                            CharacteristicValueTranslate::create([
                                'char_val_id' => $cv->id, 'lang' => $lang, 'name' => $n,
                                'slug' => $this->fastSlug($n, $usedValSlugs), 'description' => ''
                            ]);
                        }
                        $existingValues[$charId][$v_ua] = $cv->id;
                    }
                    $valId = $existingValues[$charId][$v_ua];
                    $assigned_val_ids[] = [
                        'book_id' => $book->id, 'char_val_id' => $valId,
                        'created_at' => now(), 'updated_at' => now()
                    ];
                }
    
                // Вставка связей
                if (!empty($current_book_tags_ids)) {
                    $tag_sync = array_map(fn($id) => ['book_id' => $book->id, 'tag_id' => $id], $current_book_tags_ids);
                    DB::table('books_tags')->insertOrIgnore($tag_sync);
                }
                if (!empty($assigned_val_ids)) {
                    DB::table('books_char_val')->insert($assigned_val_ids);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ошибка импорта: ' . $e->getMessage() . " (Строка " . ($index ?? 'неизвестно') . ")");
        }
    
        return redirect()->route('admin.import')->with(['success' => 'Імпорт завершено', 'error_messages' => $error_messages]);
    }
    
    // Вспомогательная функция для генерации слага БЕЗ запросов к БД
    private function fastSlug($name, &$usedSlugs) {
        $slug = Str::slug($name) ?: 'n-a';
        $original = $slug;
        $i = 1;
        while (isset($usedSlugs[$slug])) {
            $slug = $original . '-' . $i++;
        }
        $usedSlugs[$slug] = true;
        return $slug;
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