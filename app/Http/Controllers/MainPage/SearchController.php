<?php

namespace App\Http\Controllers\MainPage;

use App\Http\Controllers\Controller;
use App\Models\Book;


use App\Models\Characteristic;
use App\Models\CharacteristicValue;


use App\Models\News;
use App\Models\UserActivity;
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
    $chars = Characteristic::where('need_approve', 0)
        // ->where('is_numeric', 0)
        ->where('in_filter', 1)
        ->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('books_char_val')
                ->join('char_vals', 'books_char_val.char_val_id', '=', 'char_vals.id')
                ->whereColumn('char_vals.characteristic_id', 'characteristics.id');
        })
        ->with(['translates', 'char_vals' => fn($q) => $q->with('translates')])
        ->get();

    $chars->each(function ($char) {
        $char->setRelation('translates', $char->translates->keyBy('lang'));
        $char->char_vals->each(fn($v) => $v->setRelation('translates', $v->translates->keyBy('lang')));

        if ($char->is_numeric) {
            $currentLang = app()->getLocale();
            
            // только числа
            $numericValues = $char->char_vals->map(function ($val) use ($currentLang) {
                $name = $val->translates[$currentLang]->name ?? null;
                return is_numeric($name) ? (float)$name : null;
            })->filter(fn($v) => !is_null($v));

            // Добавляем атрибуты в объект модели
            if ($numericValues->isNotEmpty()) {
                $char->total_min = $numericValues->min();
                $char->total_max = $numericValues->max();
            } else {
                $char->total_min = 0;
                $char->total_max = 0;
            }
        }
    });

    $chars_map = [];
    foreach ($chars as $value) {
        $chars_map[$value->translates[app()->getLocale()]->slug] = [
            'id' => $value->id,
            'is_numeric' => $value->is_numeric
        ];
    }
    uksort($chars_map, fn($a, $b) => strlen($b) <=> strlen($a));

    $chars_for_sorted_by = Characteristic::where('can_sorted_by', 1)->with('translates')->get();
    $chars_for_sorted_map = [];
    foreach ($chars_for_sorted_by as $value) {
        $value->setRelation('translates', $value->translates->keyBy('lang'));
        $chars_for_sorted_map[$value->translates[app()->getLocale()]->slug] = [
            'name' => $value->translates[app()->getLocale()]->name,
            'id' => $value->id
        ];
    }

    // --- 2. Сбор всех ID выбранных характеристик из URL ---
    $selected_input_range = [];
    $selected_char_vals_id = [];
    if ($filters) {
        $filter_parts = explode('/', $filters);
        foreach ($filter_parts as $part) {
            $parent_char_id = null;
            $parent_char_is_numeric = false;
            $char_vals_slugs = null;
            
            foreach ($chars_map as $char_slug => $char) {
                if (str_starts_with($part, $char_slug . '-')) {
                    $char_vals_slugs = substr($part, strlen($char_slug) + 1);
                    $parent_char_id = $char['id'];
                    $parent_char_is_numeric = $char['is_numeric'] === 1;
                    break;
                }
            }
            
            if ($parent_char_id && !$parent_char_is_numeric) {

                $char_vals_from_parent_map = [];
                foreach ($chars->find($parent_char_id)->char_vals as $char_val) {
                    $char_vals_from_parent_map[$char_val->translates[app()->getLocale()]->slug] = $char_val->id;
                }
                uksort($char_vals_from_parent_map, fn($a, $b) => strlen($b) <=> strlen($a));

                $temp_slugs_string = $char_vals_slugs;
                while (strlen($temp_slugs_string) > 0) {
                    $found = false;
                    foreach ($char_vals_from_parent_map as $val_slug => $val_id) {
                        if ($temp_slugs_string === (string)$val_slug || strpos($temp_slugs_string, $val_slug . '-') === 0) {
                            $selected_char_vals_id[] = $val_id;
                            $cutLength = strlen($val_slug);
                            if (isset($temp_slugs_string[$cutLength]) && $temp_slugs_string[$cutLength] === '-') $cutLength++;
                            $temp_slugs_string = substr($temp_slugs_string, $cutLength);
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) abort(404, 'Значенння характеристика не знайдено');
                }
            } else if ($parent_char_id && $parent_char_is_numeric) {
                $range_parts = explode('-', $char_vals_slugs);
                
                // Очищаем от пустых и берем только числовые значения
                $numbers = array_values(array_filter($range_parts, fn($v) => is_numeric($v)));
            
                if (count($numbers) >= 2) {
                    $min = min($numbers[0], $numbers[1]);
                    $max = max($numbers[0], $numbers[1]);

                    // 2. Ищем все ID значений этой характеристики, которые числово входят в диапазон
                    // name из таблицы переводов и приводим его к числу в SQL
                    $range_val_ids = DB::table('char_vals')
                        ->join('char_vals_trans', 'char_vals.id', '=', 'char_vals_trans.char_val_id')
                        ->where('char_vals.characteristic_id', $parent_char_id)
                        ->where('char_vals_trans.lang', app()->getLocale())
                        // CAST name в UNSIGNED или DECIMAL для корректного сравнения
                        ->whereRaw("CAST(char_vals_trans.name AS UNSIGNED) BETWEEN ? AND ?", [$min, $max])
                        ->pluck('char_vals.id')
                        ->toArray();


                        $selected_input_range [$parent_char_id] = [
                            'cur_min' => $min,
                            'cur_max' => $max,
                        ];
            
                    // 3. Добавляем найденные ID в общий массив для фильтрации
                    if (!empty($range_val_ids)) {
                        $selected_char_vals_id = array_merge($selected_char_vals_id, $range_val_ids);
                    } else {
                        abort(404);
                    }
                } else {
                    abort(404);
                }
            }
        }
    }

    // --- 3. Построение запроса (Сортировка теперь работает всегда) ---
    // Используем books.active для избежания конфликтов имен при JOIN
    $query = Book::where('books.active', 1);

    // ФИЛЬТР: "Любые из выбранных" (Логика OR)
    // if (!empty($selected_char_vals_id)) {
    //     $query->whereHas('char_vals', function($q) use ($selected_char_vals_id) {
    //         // Ищем книги, у которых id значения входит в список выбранных
    //         $q->whereIn('char_vals.id', $selected_char_vals_id);
    //     });
    // }


    // 1. Фильтр по числовым диапазонам (обязательное соответствие каждому диапазону)
    if (!empty($selected_input_range)) {
        foreach ($selected_input_range as $char_id => $range) {
            $query->whereHas('char_vals', function($q) use ($char_id, $range) {
                $q->where('characteristic_id', $char_id)
                ->join('char_vals_trans', 'char_vals.id', '=', 'char_vals_trans.char_val_id')
                ->where('char_vals_trans.lang', app()->getLocale())
                ->whereRaw("CAST(char_vals_trans.name AS UNSIGNED) BETWEEN ? AND ?", [$range['cur_min'], $range['cur_max']]);
            });
        }
    }

    // 2. Фильтр по обычным чекбоксам (Логика: Книга должна иметь хотя бы одно из выбранных значений в группе)
    // Но если выбрано несколько разных характеристик (напр. Автор И Тип), используем AND между группами
    if (!empty($selected_char_vals_id)) {
        // Получаем ID только тех значений, которые НЕ относятся к числовым (они уже обработаны выше)
        $numeric_char_ids = array_keys($selected_input_range);
        
        // Группируем выбранные ID по их характеристикам, чтобы между разными группами был И
        $grouped_vals = CharacteristicValue::whereIn('id', $selected_char_vals_id)
            ->whereNotIn('characteristic_id', $numeric_char_ids)
            ->get()
            ->groupBy('characteristic_id');

        foreach ($grouped_vals as $char_id => $val_ids) {
            $query->whereHas('char_vals', function($q) use ($val_ids) {
                $q->whereIn('char_vals.id', $val_ids->pluck('id'));
            });
        }
    }

    // теги через get
    if ($request->filled('tag')) {
        $tagIds = explode('-', $request->query('tag'));
        
        $query->whereHas('tags', function($q) use ($tagIds) {
            $q->whereIn('tags.id', $tagIds);
        });
    }


    // поиска
    if ($request->filled('search')) {
        $search = '%' . trim($request->search) . '%';
        $locale = app()->getLocale();
    
        $query->where(function ($mainQuery) use ($search, $locale) {
            // cfvf сама книга
            $mainQuery->whereHas('translates', function ($q) use ($search, $locale) {
                $q->where('lang', $locale)
                  ->where(function ($sub) use ($search) {
                      $sub->where('name', 'LIKE', $search)
                          ->orWhere('anotation', 'LIKE', $search); // Убедитесь, что в БД именно 'anotation', а не 'annotation'
                  });
            })
            // 2. ИЛИ поиск по значениям всех характеристик, привязанных к книге
            ->orWhereHas('char_vals.translates', function ($q) use ($search, $locale) {
                $q->where('lang', $locale)
                  ->where('name', 'LIKE', $search);
            });
        });
    }

    // сорт:
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

    // Получаем результат (distinct нужен, чтобы избежать дублей из-за JOIN/whereHas)
    $books = $query->with([
        'translates', 
        'tags.translates',
        'char_vals.characteristic', 
        'char_vals.translates'
    ])->distinct()->get();

    $books->each(function($b) {
        $b->setRelation('translates', $b->translates->keyBy('lang'));
    
        $b->tags->each(function($tag) {
            $tag->setRelation('translates', $tag->translates->keyBy('lang'));
        });

        $b->authors = $b->char_vals->filter(function($val) {
            return optional($val->characteristic)->is_author == 1;
        });
    
        $b->edition_types = $b->char_vals->filter(function($val) {
            return optional($val->characteristic)->is_type == 1;
        });
    });

    $fullActivityLog = [
        'url_filters' => $filters,
        'get_params'  => $request->except(['page']),
        'parsed_ids'  => $selected_char_vals_id, 
        'ranges'      => $selected_input_range,  
    ];

    // Проверяем, было ли хоть какое-то действие (поиск или фильтрация)
    if ($request->anyFilled(['search', 'tag', 'order']) || !empty($filters)) {
        UserActivity::create([
            'type'          => 'search',
            'search_query'  => $request->query('search'), 
            'filters'       => $fullActivityLog,
            'results_count' => $books->count(), // Для коллекции count
            'locale'        => app()->getLocale(),
            'user_ip'       => $request->ip()
        ]);
    }

    $locale = app()->getLocale();
    $translates = DB::table('translates')->pluck($locale, 'slug');

    return view('main_page.search', [
        'title' => 'Пошук',
        'chars' => $chars,
        'selected_char_vals_id' => $selected_char_vals_id,
        'books' => $books,
        'chars_for_sorted_map' => $chars_for_sorted_map,
        'selected_input_range' => $selected_input_range,
        'translates' => $translates
    ]);
}
}