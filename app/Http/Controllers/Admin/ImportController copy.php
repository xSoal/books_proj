<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookTranslate;
// Импортируем необходимые классы для биндера
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
    public function index(){

        $booksToApprove = Book::where('need_approve', true)
            ->with('translates')
            ->get()
            ->transform(function ($book) {
                $book->setRelation('translates', $book->translates->keyBy('lang'));
                return $book;
            });

        $data = [
            'title' => 'Імпорт',
            'booksToApprove' => $booksToApprove
        ];

        return view('admin.import.index', $data);
    }

    public function approve(){
        DB::table('books')->where('need_approve', true)->update(['need_approve' => false]);
        DB::table('characteristics')->where('need_approve', true)->update(['need_approve' => false]);
        DB::table('char_vals')->where('need_approve', true)->update(['need_approve' => false]);

        $data = [
            'title' =>  'Імпорт'
        ];

        return redirect()->route('admin.import', $data);
    }

    public function add(Request $request){
        $langs = ['ua', 'en'];

        Characteristic::where('need_approve', true)->delete();
        CharacteristicValue::where('need_approve', true)->delete();
        Book::where('need_approve', true)->delete();


        $request->validate([
            'exel_file' => 'required|mimes:xlsx',
        ]);

        $data = Excel::toArray(new StringValueBinder, $request->file('exel_file'))[0];
        

        if (empty($data)) {
            return back()->with('error', 'Файл пуст');
        }

        $headers = array_shift($data);
        array_shift($headers);


        $static_book_fileds = [
            'Порядковий номер видання' => 'sort',
        ];

        // TODO to lowercase all ===
        $static_book_translates_fields = [
            'Назва' => 'name',
            'Анотація' => 'anotation',
        ];

        $book_tags = [

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


        // После того, как определится что делать с полями убрать
        $fields_to_ignore = [
            'Дизайн',
            'Ключові слова',
            'Key words'
        ];

        // TODO проверка что заголовков и колонок одинаковое количество
        
        foreach ($data as $bookRow) {
            array_shift($bookRow);
            if(!$bookRow[0]) continue;
            if (empty(trim($bookRow[3] ?? '')) && empty(trim($bookRow[4] ?? ''))) {
                continue; 
            }
            // dd($bookRow);
            // $bookAttributes = [];
            // foreach ($headers as $index => $header) {
            //     if (!empty($header)) {
            //         $bookAttributes[$header] = $row[$index] ?? null;
            //     }
            // }

            $current_book_static_fields = [];
            $current_book_static_translates = [];
            $current_book_dinamic_translates = [];

            for($i = 0; $i < count($bookRow); $i++){


                $current_val = $bookRow[$i];

                $is_static_field = false;
                // сбор статических полей без перевода ( пока только sort )
                foreach ( $static_book_fileds as $key => $value) {
                    $is_static_field = trim($headers[$i]) === $key;
                    if($is_static_field){
                        $current_book_static_fields[$value] = $current_val;
                        continue;
                    }
                }
                if($is_static_field){
                    continue;
                }

                // для полей, которые статические поля книги с переводами
                $is_current_val_static_trans = isset($static_book_translates_fields[$headers[$i]]);
                if($is_current_val_static_trans){
                    $field_model_name = $static_book_translates_fields[$headers[$i]];
                    $val_ua = $current_val;
                    $val_en = $bookRow[$i + 1];

                    $i++;

                    $current_book_static_translates[$headers[$i - 1]] = [
                        'ua' => $val_ua ? $val_ua : $val_en,
                        'en' => $val_en ? $val_en : $val_ua,
                        'field_model_name' => $field_model_name
                    ];

                    continue;
                }



                // После того, как определится что делать с полями
                if(in_array($headers[$i], $fields_to_ignore)){
                    continue;
                }

        
                // динамические поля c где не нужен перевод
                $is_current_val_dinamic_number = isset($fields_chars_exception_with_one_lang[$headers[$i]]);
                if($is_current_val_dinamic_number){
                    
                    // $val_ua = $fields_chars_exception_with_one_lang[$headers[$i]]['ua'];
                    // $val_en = $fields_chars_exception_with_one_lang[$headers[$i]]['en'];

                    // $current_book_static_translates[$val_ua] = [
                    //     'ua' => $current_val,
                    //     'en' => $current_val,
                    // ];

                    $current_book_dinamic_translates[] = [
                        'header_ua' => $fields_chars_exception_with_one_lang[$headers[$i]]['ua'],
                        'header_en' => $fields_chars_exception_with_one_lang[$headers[$i]]['en'],
                        'val_ua' => $current_val,
                        'val_en' => $current_val
                    ];
    

                    continue;
                }


                // остальное, єто динамические поля с переводом
                try {
                    //code...
                    // $val_ua = $current_val;
                    // $val_en = $bookRow[$i + 1];

                    // $header_ua = $headers[$i];
                    // $header_en = $headers[$i + 1];

                    // $i++;
    
                    // $current_book_dinamic_translates[] = [
                    //     'header_ua' => $header_ua,
                    //     'header_en' => $header_en,
                    //     'val_ua' => $val_ua ? $val_ua : $val_en,
                    //     'val_en' => $val_en ? $val_en : $val_ua
                    // ];

                } catch (\Throwable $th) {
                    throw $th;
                    dd($i);
                }

                $val_ua = $current_val;
                // if(!isset($bookRow[$i + 1])){
                //     dd($current_val);
                // }
                $val_en = $bookRow[$i + 1];
    
                $header_ua = $headers[$i];
                $header_en = $headers[$i + 1];

                if(!$header_ua || !$header_en) break;
    
                $i++;
    
                $current_book_dinamic_translates[] = [
                    'header_ua' => $header_ua,
                    'header_en' => $header_en,
                    'val_ua' => $val_ua ? $val_ua : $val_en,
                    'val_en' => $val_en ? $val_en : $val_ua
                ];


            }


            $book_data = [
                'current_book_static_fields' => $current_book_static_fields,
                'current_book_static_translates' => $current_book_static_translates,
                'current_book_dinamic_translates' => $current_book_dinamic_translates
            ];


            $chars_created = [];
            $chars_issets = [];
            $char_vals_created = [];
            $char_vals_issets = [];

            // dd($current_book_dinamic_translates);
            foreach ($current_book_dinamic_translates as $i => $charArr) {
                // if(!isset($charArr['header_ua'])){
                //     dd($charArr);
                // }
                $char_ua_name = trim($charArr['header_ua']);
                $char = null;
                
                $char_trans = CharacteristicTranslate::where('lang', 'ua')->where('name', $char_ua_name )->first();

                if(!$char_trans){
                    $char = new Characteristic();
                    $char->img = '';
                    $char->active = 1;
                    $char->sort = $current_book_static_fields['sort'] ?? 1;
                    $char->need_approve = true;
                    $save = $char->save();
                    
                    $chars_created[] = $char->id;

                    if($save && $charArr['header_ua'] && $charArr['header_en']){
                        $char_trans_ua = new CharacteristicTranslate();
                        $char_trans_ua->lang = 'ua';
                        $char_trans_ua->characteristic_id  = $char->id;
                        $char_trans_ua->name = $charArr['header_ua'];
                        $char_trans_ua->description = '';

                        $slug_ua = Str::slug($charArr['header_ua']);
                        $slug_i = 1;
                        while(CharacteristicTranslate::where('slug', $slug_ua)->exists()){
                            $slug_ua = Str::slug($charArr['header_ua'] . '-' . $slug_i++);
                        }
                        
                        $char_trans_ua->slug  = $slug_ua;
                        $char_trans_ua->save();
                        // for en TODO make in loop
                        $char_trans_en = new CharacteristicTranslate();
                        $char_trans_en->lang = 'en';
                        $char_trans_en->characteristic_id  = $char->id;
                        $char_trans_en->name = $charArr['header_en'];
                        $char_trans_en->description = '';

                        $slug_en = Str::slug($charArr['header_en']);
                        $slug_i = 1;
                        while(CharacteristicTranslate::where('slug', $slug_en)->exists()){
                            $slug_en = Str::slug($charArr['header_en'] . '-' . $slug_i++);
                        }
                        
                        $char_trans_en->slug  = $slug_en;
                        $char_trans_en->save();

                    } else {
                        $char->delete();
                    }
            
                } else {
                    $char = Characteristic::where('id', $char_trans->characteristic_id )->first();
                    $chars_issets[] = $char->id;
                }


                if($char) {
                    $char_val_ua_name = trim($charArr['val_ua']);
                    $char_val_trans = CharacteristicValueTranslate::where('lang', 'ua')->where('name', $char_val_ua_name )->first();
                    if(!$char_val_trans){


                        $char_val = new CharacteristicValue();
                        $char_val->active = 1;
                        $char_val->characteristic_id = $char->id;
                        $char_val->need_approve = true;
                        $save = $char_val->save();
                        
                        
    
                        if($save){
                            if (empty(trim($charArr['val_ua'] ?? '')) && empty(trim($charArr['val_en'] ?? ''))) {
                                $char_val->delete();
                                continue; 
                            }

                            $char_val_trans_ua = new CharacteristicValueTranslate();
                            $char_val_trans_ua->lang = 'ua';
                            $char_val_trans_ua->char_val_id = $char_val->id;
                            $char_val_trans_ua->name = $charArr['val_ua'];
                            $char_val_trans_ua->description = '';
    
                            $slug_ua = Str::slug($charArr['val_ua']);
                            $slug_i = 1;
                            while(CharacteristicValueTranslate::where('slug', $slug_ua)->exists()){
                                $slug_ua = Str::slug($charArr['val_ua'] . '-' . $slug_i++);
                            }
                            
                            $char_val_trans_ua->slug  = $slug_ua;
                            $char_val_trans_ua->save();
                            // for en TODO make in loop
                            $char_val_trans_en = new CharacteristicValueTranslate();
                            $char_val_trans_en->lang = 'en';
                            $char_val_trans_en->char_val_id  = $char_val->id;
                            $char_val_trans_en->name = $charArr['val_en'];
                            $char_val_trans_en->description = '';
    
                            $slug_en = Str::slug($charArr['val_en']);
                            $slug_i = 1;
                            while(CharacteristicValueTranslate::where('slug', $slug_en)->exists()){
                                $slug_en = Str::slug($charArr['val_en'] . '-' . $slug_i++);
                            }
                            
                            $char_val_trans_en->slug  = $slug_en;
                            $char_val_trans_en->save();

                            $char_vals_created[] = $char_val->id;
    
                        }
                
                    } else {
                        $char_val = CharacteristicValue::where('id', $char_val_trans->char_val_id  )->first();
                        $char_vals_issets[] = $char_val->id;
                    }
                    
                }
            }



            // dd([
            //     $chars_created,
            //     $chars_issets,
            //     $char_vals_created,
            //     $char_vals_issets
            // ]);



            // 'current_book_static_fields' => $current_book_static_fields,
            // 'current_book_static_translates' => $current_book_static_translates,
            // 'current_book_dinamic_translates' => $current_book_dinamic_translates

            $book = new Book();
            
            foreach ($current_book_static_fields as $key => $value) {
                if($key === 'sort'){
                    if(!$value || $value === '0' || $value === 0){
                        $value = 1;
                    }  
                }
                $book[$key] = $value;
            }

            $book->need_approve = 1;

            $book->save();

            foreach ($langs as $lang) {
                $book_translate = new BookTranslate();
                $book_translate->book_id = $book->id;
                $book_translate->lang = $lang;

                $slug = Str::slug($current_book_static_translates['Назва'][$lang]);
                $slug_i = 1;
                while(BookTranslate::where('slug', $slug)->exists()){
                    $slug = Str::slug($current_book_static_translates['Назва'][$lang] . '-' . $slug_i++);
                }

                $book_translate->slug = $slug;

                foreach ($current_book_static_translates as $key => $value) {
                    $book_translate[$value['field_model_name']] = $value[$lang];
                }

                // echo $book_translate->name . '!!!<br>';
                // var_dump($current_book_static_translates);
                // echo $book_translate->name . '!!!<br>';
                $book_translate->save();

            }


            $dataToInsert = [];
            
            foreach ($char_vals_created as $char_val_id) {
                $dataToInsert[] = [
                    'book_id'     => $book->id,
                    'char_val_id' => $char_val_id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
            foreach ($char_vals_issets as $char_val_id) {
                $dataToInsert[] = [
                    'book_id'     => $book->id,
                    'char_val_id' => $char_val_id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }


            // foreach (array_merge($char_vals_created, $char_vals_issets) as $check_id) {
            //     foreach (['ua', 'en'] as $l) {
            //         $exists = CharacteristicValueTranslate::where('char_val_id', $check_id)->where('lang', $l)->exists();
            //         if (!$exists) {
            //             dd("Ошибка! Для значения ID: $check_id отсутствует перевод на язык: $l. Проверьте строку Excel с данными:", $charArr);
            //         }
            //     }
            // }
            

            DB::table('books_char_val')->insert($dataToInsert);



            // dd($current_book_static_translates);





            // foreach ($bookAttributes as $index => $attr) {
            //     $attr = $bookAttributes[$header];

            // }


            // dd($bookAttributes);

        }



        // проще удалить все без переводов чем найти почему
        // --- ОЧИСТКА БАЗЫ ПОСЛЕ ИМПОРТА (названия таблиц: char_vals, books_char_val, char_vals_trans) ---

        // 1. Удаляем связи в books_char_val, которые ведут "в никуда"
        // (если вдруг была удалена книга или само значение характеристики)
        // DB::table('books_char_val')
        // ->whereNotExists(function ($query) {
        //     $query->select(DB::raw(1))
        //         ->from('books')
        //         ->whereRaw('books.id = books_char_val.book_id');
        // })
        // ->orWhereNotExists(function ($query) {
        //     $query->select(DB::raw(1))
        //         ->from('char_vals')
        //         ->whereRaw('char_vals.id = books_char_val.char_val_id');
        // })
        // ->delete();

        // // 2. Ищем и удаляем "битые" значения характеристик (у которых нет перевода хотя бы на один язык)
        // foreach ($langs as $lang) {
        // // Выбираем ID из таблицы char_vals, для которых нет пары в таблице переводов для текущего языка
        // $badIds = DB::table('char_vals')
        //     ->leftJoin('char_vals_trans', function($join) use ($lang) {
        //         $join->on('char_vals.id', '=', 'char_vals_trans.char_val_id')
        //             ->where('char_vals_trans.lang', '=', $lang);
        //     })
        //     ->whereNull('char_vals_trans.id') 
        //     ->pluck('char_vals.id')
        //     ->toArray();

        // if (!empty($badIds)) {
        //     // Сначала удаляем связи этих значений с книгами, чтобы не было "Property on null"
        //     DB::table('books_char_val')->whereIn('char_val_id', $badIds)->delete();
            
        //     // Удаляем существующие переводы для этих ID (на других языках)
        //     DB::table('char_vals_trans')->whereIn('char_val_id', $badIds)->delete();
            
        //     // Удаляем само значение
        //     DB::table('char_vals')->whereIn('id', $badIds)->delete();
        // }
        // }

        // // 3. Аналогичная проверка для книг (удаляем книги без переводов)
        // foreach ($langs as $lang) {
        //     $badBookIds = DB::table('books')
        //         ->leftJoin('books_translates', function($join) use ($lang) {
        //             $join->on('books.id', '=', 'books_translates.book_id')
        //                 ->where('books_translates.lang', '=', $lang);
        //         })
        //         ->whereNull('books_translates.id')
        //         ->pluck('books.id')
        //         ->toArray();

        //     if (!empty($badBookIds)) {
        //         DB::table('books_translates')->whereIn('book_id', $badBookIds)->delete();
        //         DB::table('books_char_val')->whereIn('book_id', $badBookIds)->delete();
        //         DB::table('books')->whereIn('id', $badBookIds)->delete();
        //     }
        // }





        $data = [
            'title' => 'Імпорт'
        ];

        return redirect()->route('admin.import', $data);
    }

}



/**
 * Класс-биндер для принудительного приведения всех значений Excel в строки
 * без дробной части (.0), если число целое.
 */
class StringValueBinder extends DefaultValueBinder implements IValueBinder
{
    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value)) {
            // Приводим к float, чтобы убрать .0, затем в string
            $value = (string)(float)$value;
        }

        // Если значение null или пустое, приводим к пустой строке
        $value = $value ?? '';

        $cell->setValueExplicit($value, DataType::TYPE_STRING);

        return true;
    }
}