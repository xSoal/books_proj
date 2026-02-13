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
    public function index(Request $request, $filters = null)
    {
        // --- 1. Подготовка справочников (Характеристики) ---
        $chars = Characteristic::where('need_approve', 0)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('books_char_val')
                    ->join('char_vals', 'books_char_val.char_val_id', '=', 'char_vals.id')
                    ->whereColumn('char_vals.characteristic_id', 'characteristics.id');
            })
            ->with(['translates', 'char_vals' => function($query) {
                $query->with('translates');
            }])
            ->get();
    
        $chars->each(function ($char) {
            $char->setRelation('translates', $char->translates->keyBy('lang'));
            $char->char_vals->each(function ($value) {
                $value->setRelation('translates', $value->translates->keyBy('lang'));
            });
        });
    
        // Мапа для разбора URL-фильтров
        $chars_map = [];
        foreach ($chars as $value) {
            $chars_map[$value->translates[app()->getLocale()]->slug] = $value->id;
        }
        uksort($chars_map, fn($a, $b) => strlen($b) <=> strlen($a));
    
        // Мапа для доступных сортировок
        $chars_for_sorted_by = Characteristic::where('can_sorted_by', 1)->with('translates')->get();
        $chars_for_sorted_map = [];
        foreach ($chars_for_sorted_by as $value) {
            $value->setRelation('translates', $value->translates->keyBy('lang'));
            $chars_for_sorted_map[$value->translates[app()->getLocale()]->slug] = [
                'name' => $value->translates[app()->getLocale()]->name,
                'id' => $value->id
            ];
        }
    
        // --- 2. Обработка фильтров из URL ---
        $selected_char_vals_id = [];
        if ($filters) {
            $filter_parts = explode('/', $filters);
            foreach ($filter_parts as $part) {
                $parent_char_id = null;
                $char_vals_slugs = null;
    
                foreach ($chars_map as $char_slug => $char_id) {
                    if (str_starts_with($part, $char_slug . '-')) {
                        $char_vals_slugs = substr($part, strlen($char_slug) + 1);
                        $parent_char_id = $char_id;
                        break;
                    }
                }
    
                if ($parent_char_id) {
                    $char_vals_from_parent_map = [];
                    $parent_char = $chars->find($parent_char_id);
                    foreach ($parent_char->char_vals as $char_val) {
                        $char_vals_from_parent_map[$char_val->translates[app()->getLocale()]->slug] = $char_val->id;
                    }
                    uksort($char_vals_from_parent_map, fn($a, $b) => strlen($b) <=> strlen($a));
    
                    $temp_slugs_string = $char_vals_slugs;
                    while (strlen($temp_slugs_string) > 0) {
                        $found = false;
                        foreach ($char_vals_from_parent_map as $val_slug => $val_id) {
                            $is_match = ($temp_slugs_string === (string)$val_slug) || 
                                       (strpos($temp_slugs_string, $val_slug . '-') === 0);
                            if ($is_match) {
                                $selected_char_vals_id[] = $val_id;
                                $cutLength = strlen($val_slug);
                                if (isset($temp_slugs_string[$cutLength]) && $temp_slugs_string[$cutLength] === '-') $cutLength++;
                                $temp_slugs_string = substr($temp_slugs_string, $cutLength);
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) abort(404, 'Значение характеристики не найдено');
                    }
                }
            }
        }
    
        // --- 3. Построение основного запроса ---
        $query = Book::where('books.active', 1);

        // Фильтрация: Книга подходит, если у нее есть ХОТЯ БЫ ОДНО из выбранных значений
        if (!empty($selected_char_vals_id)) {
            $query->whereHas('char_vals', function($q) use ($selected_char_vals_id) {
                $q->whereIn('char_vals.id', $selected_char_vals_id);
            });
        }

        // Поиск по названию (если передан параметр search)
        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function($q) use ($search) {
                $q->whereHas('translates', function($sub) use ($search) {
                    $sub->where('name', 'LIKE', $search);
                });
            });
        }
    
        // --- 4. Сортировка ---
        $order = $request->query('order');
        if ($order) {
            $parts = explode('-', $order);
            $field = $parts[0];
            $direction = $parts[1] ?? 'asc';
    
            switch ($field) {
                case 'name':
                    $query->join('books_translates', 'books.id', '=', 'books_translates.book_id')
                        ->where('books_translates.lang', app()->getLocale())
                        ->select('books.*')
                        ->orderBy('books_translates.name', $direction);
                    break;
    
                default:
                    if (isset($chars_for_sorted_map[$field])) {
                        $charId = $chars_for_sorted_map[$field]['id'];
                        $currentLang = app()->getLocale();
    
                        $sortQuery = DB::table('char_vals_trans as cvt')
                            ->join('char_vals as cv', 'cvt.char_val_id', '=', 'cv.id')
                            ->join('books_char_val as bcv', 'cv.id', '=', 'bcv.char_val_id')
                            ->whereColumn('bcv.book_id', 'books.id')
                            ->where('cv.characteristic_id', $charId)
                            ->where('cvt.lang', $currentLang)
                            ->select('cvt.name')
                            ->limit(1);
    
                        $query->select('books.*')->selectSub($sortQuery, 'sort_val');
    
                        $characteristic = $chars_for_sorted_by->where('id', $charId)->first();
                        if ($characteristic && $characteristic->is_numeric) {
                            $query->orderByRaw("ISNULL(sort_val) ASC, CAST(NULLIF(sort_val, '') AS SIGNED) $direction");
                        } else {
                            $query->orderByRaw("ISNULL(sort_val) ASC, LOWER(sort_val) COLLATE utf8mb4_unicode_ci $direction");
                        }
                    } else {
                        $query->orderBy('books.id', 'desc');
                    }
                    break;
            }
        } else {
            $query->orderBy('books.id', 'desc');
        }
    
        // --- 5. Выполнение и отдача данных ---
        $books = $query->with('translates')->get();
    
        $books->each(function ($book) {
            $book->setRelation('translates', $book->translates->keyBy('lang'));
        });
    
        return view('main_page.search', [
            'title' => 'Пошук',
            'chars' => $chars,
            'selected_char_vals_id' => $selected_char_vals_id,
            'books' => $books,
            'chars_for_sorted_map' => $chars_for_sorted_map
        ]);
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
