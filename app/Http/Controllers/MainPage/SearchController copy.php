<?php

namespace App\Http\Controllers\MainPage;

use App\Http\Controllers\Controller;
use App\Models\Book;


use App\Models\Characteristic;
use App\Models\CharacteristicValue;


use App\Models\News;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request, $filters = null){
        // $search = trim($request->input('search'));
        // $perPage = 9;

        // $titles = DB::table('settings')
        //     ->where('type', 'titles')
        //     ->first()->value;
        // $title = json_decode($titles)->search;
        


        // if(!$search){
        //     // Создание пустого пагинатора, без запроса к БД
        //     $resultSearch = new LengthAwarePaginator(
        //         new Collection(), 
        //         0,
        //         $perPage,
        //         LengthAwarePaginator::resolveCurrentPage(),
        //         ['path' => $request->url(), 'query' => $request->query()]
        //     );

        // } else {

        //     $searchPattern = '%' . $search . '%';
        //     $today = Carbon::today();

        //     $query = News::whereDate('public_date', '<=', $today)
        //         ->where('active', 1)
        //         ->where(function ($q) use ($searchPattern) {
        //             $q->where('title', 'LIKE', $searchPattern) 
        //             ->orWhere('content', 'LIKE', $searchPattern);
        //         });

        //     if (!Auth::user()) {
        //         $query->where('type', 'news');
        //     }
            
        //     $resultSearch = $query->orderBy('public_date', 'desc')
        //         ->paginate($perPage)
        //         ->appends(['search' => $search]);

        // }


        // $data = [
        //     'title' => $title,
        //     'search' => $search,
        //     'resultSearch' => $resultSearch
        // ];

        // $chars = Characteristic::where('need_approve', 0)->with('translates')->get();
        // $chars->transform(function ($chars) {
        //     $chars->setRelation('translates', $chars->translates->keyBy('lang'));
        //     return $chars;
        // });

        // $char_vals = CharacteristicValue::where('need_approve', 0)->with('translates')->get();
        // $char_vals->transform(function ($char_vals) {
        //     $char_vals->setRelation('translates', $char_vals->translates->keyBy('lang'));
        //     return $char_vals;
        // });


        // $search = trim($request->input('search'));
        
        $chars = Characteristic::where('need_approve', 0)
            // ->where('in_filter', 1)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('books_char_val')
                    ->join('char_vals', 'books_char_val.char_val_id', '=', 'char_vals.id')
                    ->whereColumn('char_vals.characteristic_id', 'characteristics.id');
            })
            ->with(['translates', 'char_vals' => function($query) {
                $query
                // ->where('need_approve', 0)
                ->with('translates');
            }])
        ->get();

        $chars->each(function ($char) {
            $char->setRelation('translates', $char->translates->keyBy('lang'));

            $char->char_vals->each(function ($value) {
                $value->setRelation('translates', $value->translates->keyBy('lang'));
            });
        });

        // $chars_map = $chars->map(function ($char){
        //     return [
        //         $char->translates[app()->getLocale()]->slug => $char->id
        //     ];
        // })->toArray();

        $chars_map = [];
       
        foreach ($chars as $key => $value) {
            $chars_map[ $value->translates[app()->getLocale()]->slug ] = $value->id;
        }
        // чтобы найти снчала самое длинное совпадение
        uksort($chars_map, function($a, $b) {
            return strlen($b) <=> strlen($a);
        });



        $chars_for_sorted_by = Characteristic::where('can_sorted_by', 1)
            ->with('translates')->get();
        $chars_for_sorted_by->each(function ($char) {
            $char->setRelation('translates', $char->translates->keyBy('lang'));
        });

        $chars_for_sorted_map = [];
        foreach ($chars_for_sorted_by as $key => $value) {
            $chars_for_sorted_map[ $value->translates[app()->getLocale()]->slug ] = 
            [
                'name' => $value->translates[app()->getLocale()]->name, 
                'id' => $value->id
            ];
        }





        $selected_char_vals_id = [];
        if($filters){

            $filter_parts =  explode('/', $filters);
            foreach ($filter_parts as $key => $part) {
                $parent_char_id = null;
                $char_vals_slugs = null;
                foreach ($chars_map as $char_slug => $char_id)  {
                    if (str_starts_with($part, $char_slug . '-')) {
                        $char_vals_slugs = substr($part, strlen($char_slug) + 1);
                        $parent_char_id = $char_id;
                    }
                }
                if(!$parent_char_id){
                    abort(404, 'Характеристику не знайдено');
                }
    
                // все значения текущей характеристики
                $char_vals_from_parent_map = [];
    
                foreach ($chars->find($parent_char_id)->char_vals as $c => $char) {
                    $char_vals_from_parent_map[ $char->translates[app()->getLocale()]->slug ] = $char->id;
                }
                uksort($char_vals_from_parent_map, function($a, $b) {
                    return strlen($b) <=> strlen($a);
                });
    
    
                // if($key === 1){
                //     dd($char_vals_slugs, $char_vals_from_parent_map, $filter_parts);
                // }
    
    
                $temp_slugs_string = $char_vals_slugs;
    
                while (strlen($temp_slugs_string) > 0) {
                    $found = false;
                
                    foreach ($char_vals_from_parent_map as $val_slug => $val_id) {
                        $val_slug = (string)$val_slug;
                        $is_match = ($temp_slugs_string === $val_slug) || 
                                    (strpos($temp_slugs_string, $val_slug . '-') === 0);
                
                        if ($is_match) {
                            $selected_char_vals_id[] = $val_id;
                            
                            $cutLength = strlen($val_slug);
                            // Если в строке после слага идет дефис, отрезаем и его
                            if (isset($temp_slugs_string[$cutLength]) && $temp_slugs_string[$cutLength] === '-') {
                                $cutLength += 1;
                            }
                
                            $temp_slugs_string = substr($temp_slugs_string, $cutLength);
                            $found = true;
                            break; 
                        }
                    }
                    // dd($selected_char_vals_id);
    
                    // Если прошли все возможные слаги из мапы и ничего не нашли, а строка не пуста
                    if (!$found) {
                        abort(404, 'Значение характеристики не найдено');
                    }
                }
    
            }

            

            $order = $request->query('order'); // Получаем например 'name-asc' или 'id-desc'


            

            // 1. Инициализируем запрос с ЯВНЫМ указанием таблицы для колонки active
            $books = Book::where('books.active', 1);

            // 2. Если есть список ID (фильтрация)
            if (isset($books_id) && !empty($books_id)) {
                // Здесь тоже лучше добавить префикс таблицы
                $books->whereIn('books.id', $books_id);
            }

            // 3. Обработка сортировки
            if ($order) {
                $parts = explode('-', $order);
                $field = $parts[0];
                $direction = $parts[1] ?? 'asc';
            
                switch ($field) {
                    case 'name':
                        $books->join('books_translates', 'books.id', '=', 'books_translates.book_id')
                            ->where('books_translates.lang', app()->getLocale())
                            ->select('books.*')
                            ->orderByRaw("LOWER(books_translates.name) $direction");
                        break;
            
                    default:
                        if (isset($chars_for_sorted_map[$field])) {
                            $charId = $chars_for_sorted_map[$field]['id'];
                            $currentLang = app()->getLocale();
            
                            // Используем подзапрос, чтобы вытащить ровно ОДНО значение для каждой книги
                            $sortQuery = DB::table('char_vals_trans as cvt')
                                ->join('char_vals as cv', 'cvt.char_val_id', '=', 'cv.id')
                                ->join('books_char_val as bcv', 'cv.id', '=', 'bcv.char_val_id')
                                ->whereColumn('bcv.book_id', 'books.id')
                                ->where('cv.characteristic_id', $charId)
                                ->where('cvt.lang', $currentLang)
                                ->select('cvt.name')
                                ->limit(1);
            
                            // Добавляем этот подзапрос в select как виртуальную колонку
                            $books->select('books.*')
                                ->selectSub($sortQuery, 'sort_val');
            
                            // Проверяем тип характеристики (числовая или нет)
                            $characteristic = $chars_for_sorted_by->where('id', $charId)->first();
                            $isNumeric = $characteristic && $characteristic->is_numeric;
            
                            if ($isNumeric) {
                                // Сортируем как числа. NULLIF убирает пустые строки, CAST делает число
                                $books->orderByRaw("ISNULL(sort_val), CAST(NULLIF(sort_val, '') AS UNSIGNED) $direction");
                            } else {
                                // Сортировка кириллицы (добавьте COLLATE если Д всё еще после Х)
                                $books->orderByRaw("ISNULL(sort_val), LOWER(sort_val) COLLATE utf8mb4_unicode_ci $direction");
                            }
                        } else {
                            $books->orderBy('books.id', 'desc');
                        }
                        break;
                }
            }

            // 4. Выполняем запрос
            $books = $books->with('translates')
            ;

        } else {
            $books = Book::where('active', 1)
                ->with('translates')
                ;

        }

        $books = $books->get();


        $books->each(function ($book) {
            $book->setRelation('translates', $book->translates->keyBy('lang'));
        });




        // dd($selected_char_vals_id);

        // dd($filters);



        // dd($chars_for_sorted_map);
        // dd($chars_for_sorted_by);

        $data = [
            'title' => 'Пошук',
            'chars' => $chars,
            'selected_char_vals_id' => $selected_char_vals_id,
            'books' => $books,
            'chars_for_sorted_map' => $chars_for_sorted_map
        ];

        return view('main_page.search', $data);

    }
}



// public function search(Request $request)
//     {
//         // Получаем и очищаем поисковую строку
//         $searchText = trim($request->input('search', '')); 
//         $perPage = 15;

//         // Если строка пуста, возвращаем все записи или делаем редирект
//         if (empty($searchText)) {
//             $items = News::paginate($perPage);
//         } else {
//             // Разбиваем строку на отдельные слова, убирая лишние пробелы
//             $searchWords = preg_split('/\s+/', $searchText, -1, PREG_SPLIT_NO_EMPTY);
            
//             // Запускаем запрос
//             $items = News::where(function ($query) use ($searchWords) {
                
//                 // Применяем условие для КАЖДОГО слова
//                 foreach ($searchWords as $word) {
//                     $query->where(function ($q) use ($word) {
//                         $searchTerm = '%' . $word . '%';
                        
//                         // Ищем это слово в поле title ИЛИ в поле content
//                         $q->where('title', 'LIKE', $searchTerm)
//                           ->orWhere('content', 'LIKE', $searchTerm);
//                     });
//                 }

//             })->paginate($perPage)
//               ->appends(['search' => $searchText]); // Сохраняем запрос в пагинации
//         }

//         return view('admin.news.list', compact('items', 'searchText'));
//     }
