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

            $books_id = DB::table('books_char_val')
                ->whereIn('char_val_id', $selected_char_vals_id)
                ->pluck('book_id') // толко  book_id
                ->unique()        
                ->toArray();
            // dd($selected_char_vals_id);
            $books = Book::with(['translates']) 
                ->whereIn('id', $books_id)
                ->get();
            $books->each(function ($book) {
                $book->setRelation('translates', $book->translates->keyBy('lang'));
            });
        } else {
            $books = Book::get();
            $books->each(function ($book) {
                $book->setRelation('translates', $book->translates->keyBy('lang'));
            });
        }




        // dd($selected_char_vals_id);

        // dd($filters);


        $data = [
            'title' => 'Пошук',
            'chars' => $chars,
            'selected_char_vals_id' => $selected_char_vals_id,
            'books' => $books
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
