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
        $tags = ['Ключові слова', 'Key words'];
        // Очистка старых данных (только тех, что не одобрены)
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

        $error_messages = [];

        foreach ($data as $index => $bookRow) {
            array_shift($bookRow); 
            if (empty($bookRow[0])) {
                // $error_messages[] = "Строка {$index}: пустая первая колонка";
                continue;
            }
        
            $checkName = $this->cleanString($bookRow[8] ?? ''); 
            if (empty($checkName)) {
                $row = $index + 2;
                $error_messages[] = "Рядок $row : порожня назва, або помилка читання $bookRow[8]";
                continue;
            }

            
            // if (empty(trim($bookRow[3] ?? '')) && empty(trim($bookRow[4] ?? ''))) {
            //      // Раскомментируй строку ниже, чтобы увидеть номера строк, которые не прошли
            //      $error_messages[] = "Строка {$index + 2}: пропущена по условию колонок 3 и 4. Название: " . $checkName;
            //      continue;
            // }



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

                if (in_array($header_name, $tags)){
                    $tag_name_ua = explode(',', $current_val);
                    $tag_name_en = explode(',', $this->cleanString($bookRow[$i + 1] ?? ''));
                    $max_length = max(count($tag_name_ua), count($tag_name_en));
    
                    for ($e = 0; $e < $max_length; $e++) {
                        $name_ua = $this->cleanString($tag_name_ua[$e] ?? '');
                        $name_en = $this->cleanString($tag_name_en[$e] ?? '');
                        $search_name = !empty($name_ua) ? $name_ua : $name_en;
    
                        if (empty($search_name)) continue;
    
                        // Ищем тег по любому из языков, чтобы не плодить дубликаты Tag
                        $tagTrans = TagTranslate::where('name', $search_name)->first();
    
                        if (!$tagTrans) {
                            $tag = new Tag();
                            $tag->save();
    
                            // Создаем сразу ОБА перевода
                            foreach (['ua' => $name_ua, 'en' => $name_en] as $l => $val) {
                                $finalName = !empty($val) ? $val : $search_name;
                                TagTranslate::create([
                                    'tag_id' => $tag->id,
                                    'lang'   => $l,
                                    'name'   => $finalName,
                                    'slug'   => $this->generateUniqueSlug(TagTranslate::class, $finalName)
                                ]);
                            }
                        } else {
                            $tag = Tag::find($tagTrans->tag_id);
                        }
                        
                        $current_book_tags_ids[$tag->id] = $tag->id;
                    }
                    $i++; continue;
                };

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

            // --- СОЗДАНИЕ КНИГИ ---
            $book = new Book();
            $book->sort = (!empty($current_book_static_fields['sort'])) ? (int)$current_book_static_fields['sort'] : 1;
            $book->need_approve = true;
            $book->save();

            foreach ($langs as $lang) {
                $book_translate = new BookTranslate();
                $book_translate->book_id = $book->id;
                $book_translate->lang = $lang;
                
                $raw_name = $current_book_static_translates['Назва'][$lang] ?? 'no-name';
                $book_translate->slug = $this->generateUniqueSlug(BookTranslate::class, $raw_name);

                foreach ($current_book_static_translates as $trans_data) {
                    $book_translate[$trans_data['field_model_name']] = $trans_data[$lang];
                }
                $book_translate->save();
            }

            // --- ХАРАКТЕРИСТИКИ ---
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
                            'slug' => $this->generateUniqueSlug(CharacteristicTranslate::class, $name),
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
                            'slug' => $this->generateUniqueSlug(CharacteristicValueTranslate::class, $name),
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

            // теги
            if (!empty($current_book_tags_ids)) {
                $tag_data = [];
                foreach ($current_book_tags_ids as $tagId) {
                    $tag_data[] = [
                        'book_id' => $book->id,
                        'tag_id'  => $tagId
                    ];
                }
                DB::table('books_tags')->insertOrIgnore($tag_data);
            }


            if (!empty($assigned_val_ids)) {
                DB::table('books_char_val')->insert(array_values($assigned_val_ids));
            }
        }

        // dd($error_messages);

        return redirect()
            ->route('admin.import')
            ->with([
                'success' => 'Імпорт завершено', 
                'error_messages' => $error_messages
            ]);

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