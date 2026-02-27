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
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function index(Request $request, $filters = null)
{
    $chars = Characteristic::where('need_approve', false)
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


    // потом для сео все вібранніе id характеристик
    $selected_chars_id = [];

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
                    $selected_chars_id[] = $char['id'];

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
                
                // очищаем от пустых и берем только числовые значения
                $numbers = array_values(array_filter($range_parts, fn($v) => is_numeric($v)));
            
                if (count($numbers) >= 2) {
                    $min = min($numbers[0], $numbers[1]);
                    $max = max($numbers[0], $numbers[1]);

                    // ищем все ID значений этой характеристики, которые числово входят в диапазон
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

    $query = Book::where('books.active', 1)->where('books.need_approve', false);

    // ФИЛЬТР: "Любые из выбранных" (Логика OR)
    // if (!empty($selected_char_vals_id)) {
    //     $query->whereHas('char_vals', function($q) use ($selected_char_vals_id) {
    //         // Ищем книги, у которых id значения входит в список выбранных
    //         $q->whereIn('char_vals.id', $selected_char_vals_id);
    //     });
    // }


    // фльтр по числовым диапазонам (обязательное соответствие каждому диапазону)
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

    // фильтр по обычным чекбокса
    // Но если выбрано несколько разных характеристик (напр. Автор И Тип), AND между группами
    if (!empty($selected_char_vals_id)) {
        // Получаем ID только тех значений, которые НЕ относятся к числовым
        $numeric_char_ids = array_keys($selected_input_range);
        
        // uруппируем выбранные ID по их характеристикам, чтобы между разными группами был И
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
                          ->orWhere('anotation', 'LIKE', $search);
                  });
            })
            //ИЛИ поиск по значениям всех характеристик, привязанных к книге
            ->orWhereHas('char_vals.translates', function ($q) use ($search, $locale) {
                $q->where('lang', $locale)
                  ->where('name', 'LIKE', $search);
            });
        });
    }

    $order = $request->query('order');

    if ($order && str_contains($order, '-')) {
        $direction = str_ends_with($order, '-desc') ? 'desc' : 'asc';
        $field = str_replace(['-asc', '-desc'], '', $order);
    } else {
        $field = 'id';
        $direction = 'desc';
    }

    if ($field === 'name') {
        // Сортировка по названию (через переводы)
        $query->join('books_translates as bt', 'books.id', '=', 'bt.book_id')
            ->where('bt.lang', app()->getLocale())
            ->select('books.*') // Чтобы не перемешивать поля из разных таблиц
            ->orderBy('bt.name', $direction);
    
    } elseif (isset($chars_for_sorted_map[$field])) {
        // Сортировка по динамическим характеристикам (ISBN, Год и т.д.)
        $charId = $chars_for_sorted_map[$field]['id'];
        $locale = app()->getLocale();
    
        $query->leftJoin('books_char_val as bcv', 'books.id', '=', 'bcv.book_id')
            ->leftJoin('char_vals as cv', function ($join) use ($charId) {
                $join->on('bcv.char_val_id', '=', 'cv.id')
                     ->where('cv.characteristic_id', $charId);
            })
            ->leftJoin('char_vals_trans as cvt', function ($join) use ($locale) {
                $join->on('cv.id', '=', 'cvt.char_val_id')
                     ->where('cvt.lang', $locale);
            })
            // select, чтобы избежать дублирования колонок
            ->select('books.*')
            ->orderByRaw("CONVERT(cvt.name USING utf8mb4) COLLATE utf8mb4_unicode_ci $direction");
    
    } else {
        $query->orderBy('books.id', 'desc');
    }

    
    $books = $query
    ->with([
        'translates', 
        'tags.translates',
        'char_vals.characteristic', 
        'char_vals.translates'
    ])
    ->groupBy('books.id') 
    ->paginate(25);

    
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

    //  было ли хоть какое-то действие (поиск или фильтрация)
    if ($request->anyFilled(['search', 'tag', 'order']) || !empty($filters)) {
        UserActivity::create([
            'type'          => 'search',
            'search_query'  => $request->query('search'), 
            'filters'       => $fullActivityLog,
            'results_count' => $books->count(),
            'locale'        => app()->getLocale(),
            'user_ip'       => $request->ip()
        ]);
    }

    $locale = app()->getLocale();
    $translates = DB::table('translates')->pluck($locale, 'slug');



    // dinamic seo
    $local_seo = null;
    $filter_parts = $filters ? array_filter(explode('/', trim($filters, '/'))) : [];
    $filter_count = count($filter_parts);

    if ($filter_count > 0 && $filter_count <= 3) {
        $locale = app()->getLocale();
        $numeric_char_ids = array_keys($selected_input_range);

        $seo_data = Characteristic::whereIn('id', $selected_chars_id)
            ->whereNotIn('id', $numeric_char_ids)
            ->with([
                'translates' => fn($q) => $q->where('lang', $locale),
                // значения, которые выбраны 
                'char_vals' => fn($q) => $q->whereIn('id', $selected_char_vals_id)
                                        ->with(['translates' => fn($t) => $t->where('lang', $locale)])
            ])
            ->get();

        if ($seo_data->isNotEmpty()) {
            $title_segments = [];
            $desc_segments = [];

            foreach ($seo_data as $char) {
                $char_name = $char->translates->first()->name ?? '';
                $char_desc = $char->translates->first()->name ?? '';
                
                // Собираем все выбранные значения для этой характеристики
                $val_names = $char->char_vals->map(function($val) use ($locale) {
                    return $val->translates->first()->name ?? '';
                })->filter()->implode(', ');

                if (!empty($char_name) && !empty($val_names)) {
                    $title_segments[] = $char_name . ': ' . $val_names;
                }

                if (!empty($char_desc)) {
                    $desc_segments[] = $char_desc . ': ' . $val_names;
                }
            }

            $final_title = implode('; ', $title_segments);
            $final_desc = implode('; ', $desc_segments);

            $meta_title = Str::limit($final_title, 71, '...');
            $meta_description = Str::limit($final_desc, 139, '...');
    
            $q = DB::table('settings')->where('type', 'seoTemplates')->first();

            if($q){
                $temps = json_decode($q->value, true)[$locale];
                $meta_title = str_replace('REPLACE', $meta_title, $temps['title']);
                $meta_description = str_replace('REPLACE', $meta_description, $temps['description']);
            }

            $local_seo = [
                'meta_title'       => $meta_title,
                'meta_description' => $meta_description,
                'og_title'         => $meta_title,
                'og_description'   => $meta_description,
            ];
        }
    }

    return view('main_page.search', [
        'title' => 'Пошук',
        'chars' => $chars,
        'selected_char_vals_id' => $selected_char_vals_id,
        'books' => $books,
        'chars_for_sorted_map' => $chars_for_sorted_map,
        'selected_input_range' => $selected_input_range,
        'translates' => $translates,
        'local_seo' => $local_seo
    ]);
}
}