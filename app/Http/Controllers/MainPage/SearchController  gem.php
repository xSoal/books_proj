<?php

namespace App\Http\Controllers\MainPage;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Characteristic;
use App\Models\CharacteristicValue;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function index(Request $request, $filters = null)
    {
        $locale = app()->getLocale();

        // --- 1. ЗАГРУЗКА ВСЕХ ХАРАКТЕРИСТИК ДЛЯ ФИЛЬТРА И КАРТЫ СЛАГОВ ---
        $chars = Characteristic::where('need_approve', false)
            ->where('in_filter', 1)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('books_char_val')
                    ->join('char_vals', 'books_char_val.char_val_id', '=', 'char_vals.id')
                    ->whereColumn('char_vals.characteristic_id', 'characteristics.id');
            })
            ->with(['translates', 'char_vals' => fn($q) => $q->with('translates')])
            ->get();

        $chars->each(function ($char) use ($locale) {
            $char->setRelation('translates', $char->translates->keyBy('lang'));
            $char->char_vals->each(fn($v) => $v->setRelation('translates', $v->translates->keyBy('lang')));

            if ($char->is_numeric) {
                $numericValues = $char->char_vals->map(function ($val) use ($locale) {
                    $name = $val->translates[$locale]->name ?? null;
                    return is_numeric($name) ? (float)$name : null;
                })->filter(fn($v) => !is_null($v));

                $char->total_min = $numericValues->isNotEmpty() ? $numericValues->min() : 0;
                $char->total_max = $numericValues->isNotEmpty() ? $numericValues->max() : 0;
            }
        });

        $chars_map = [];
        foreach ($chars as $value) {
            $chars_map[$value->translates[$locale]->slug] = [
                'id' => $value->id,
                'is_numeric' => (int)$value->is_numeric === 1
            ];
        }
        uksort($chars_map, fn($a, $b) => strlen($b) <=> strlen($a));

        // Карта для сортировки (can_sorted_by)
        $chars_for_sorted_by = Characteristic::where('can_sorted_by', 1)->with('translates')->get();
        $chars_for_sorted_map = [];
        foreach ($chars_for_sorted_by as $value) {
            $value->setRelation('translates', $value->translates->keyBy('lang'));
            $chars_for_sorted_map[$value->translates[$locale]->slug] = [
                'name' => $value->translates[$locale]->name,
                'id' => $value->id
            ];
        }

        // --- 2. РАЗБОР URL ---
        $selected_char_vals_id = [];
        $selected_input_range = [];
        $selected_chars_id = [];

        if ($filters) {
            $filter_parts = array_filter(explode('/', $filters));
            foreach ($filter_parts as $part) {
                $parent_char_id = null;
                $parent_char_is_numeric = false;
                $char_vals_slugs = null;

                foreach ($chars_map as $char_slug => $c_info) {
                    if (str_starts_with($part, $char_slug . '-')) {
                        $char_vals_slugs = substr($part, strlen($char_slug) + 1);
                        $parent_char_id = $c_info['id'];
                        $selected_chars_id[] = $c_info['id'];
                        $parent_char_is_numeric = $c_info['is_numeric'];
                        break;
                    }
                }

                if ($parent_char_id && !$parent_char_is_numeric) {
                    $char_vals_from_parent_map = [];
                    $char_model = $chars->find($parent_char_id);
                    if ($char_model) {
                        foreach ($char_model->char_vals as $cv) {
                            $char_vals_from_parent_map[$cv->translates[$locale]->slug] = $cv->id;
                        }
                        uksort($char_vals_from_parent_map, fn($a, $b) => strlen($b) <=> strlen($a));

                        $temp_slugs_string = $char_vals_slugs;
                        while (strlen($temp_slugs_string) > 0) {
                            $found = false;
                            foreach ($char_vals_from_parent_map as $val_slug => $val_id) {
                                if ($temp_slugs_string === (string)$val_slug || str_starts_with($temp_slugs_string, $val_slug . '-')) {
                                    $selected_char_vals_id[] = $val_id;
                                    $cutLength = strlen($val_slug);
                                    if (isset($temp_slugs_string[$cutLength]) && $temp_slugs_string[$cutLength] === '-') $cutLength++;
                                    $temp_slugs_string = substr($temp_slugs_string, $cutLength);
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) abort(404);
                        }
                    }
                } else if ($parent_char_id && $parent_char_is_numeric) {
                    $range_parts = explode('-', $char_vals_slugs);
                    $numbers = array_values(array_filter($range_parts, fn($v) => is_numeric($v)));
                    if (count($numbers) >= 2) {
                        $min = min($numbers[0], $numbers[1]);
                        $max = max($numbers[0], $numbers[1]);
                        $selected_input_range[$parent_char_id] = ['cur_min' => $min, 'cur_max' => $max];

                        $range_val_ids = DB::table('char_vals')
                            ->join('char_vals_trans', 'char_vals.id', '=', 'char_vals_trans.char_val_id')
                            ->where('char_vals.characteristic_id', $parent_char_id)
                            ->where('char_vals_trans.lang', $locale)
                            ->whereRaw("CAST(char_vals_trans.name AS UNSIGNED) BETWEEN ? AND ?", [$min, $max])
                            ->pluck('char_vals.id')->toArray();

                        if (!empty($range_val_ids)) {
                            $selected_char_vals_id = array_merge($selected_char_vals_id, $range_val_ids);
                        } else {
                            abort(404);
                        }
                    }
                }
            }
        }

        // --- 3. ФОРМИРОВАНИЕ ЗАПРОСА ---
        $query = Book::where('books.active', true)->where('books.need_approve', false);

        if (!empty($selected_input_range)) {
            foreach ($selected_input_range as $char_id => $range) {
                $query->whereHas('char_vals', function($q) use ($char_id, $range, $locale) {
                    $q->where('characteristic_id', $char_id)
                      ->join('char_vals_trans', 'char_vals.id', '=', 'char_vals_trans.char_val_id')
                      ->where('char_vals_trans.lang', $locale)
                      ->whereRaw("CAST(char_vals_trans.name AS UNSIGNED) BETWEEN ? AND ?", [$range['cur_min'], $range['cur_max']]);
                });
            }
        }

        if (!empty($selected_char_vals_id)) {
            $numeric_char_ids = array_keys($selected_input_range);
            $grouped_vals = CharacteristicValue::whereIn('id', $selected_char_vals_id)
                ->whereNotIn('characteristic_id', $numeric_char_ids)
                ->get()->groupBy('characteristic_id');

            foreach ($grouped_vals as $char_id => $val_ids) {
                $query->whereHas('char_vals', fn($q) => $q->whereIn('char_vals.id', $val_ids->pluck('id')));
            }
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function ($mq) use ($search, $locale) {
                $mq->whereHas('translates', function ($q) use ($search, $locale) {
                    $q->where('lang', $locale)->where(fn($s) => $s->where('name', 'LIKE', $search)->orWhere('anotation', 'LIKE', $search));
                })->orWhereHas('char_vals.translates', fn($q) => $q->where('lang', $locale)->where('name', 'LIKE', $search));
            });
        }

        if ($request->filled('tag')) {
            $tagIds = explode('-', $request->query('tag'));
            $query->whereHas('tags', fn($q) => $q->whereIn('tags.id', $tagIds));
        }

        // --- 4. УМНЫЕ СЧЕТЧИКИ (ФАСЕТЫ) ---
        $matching_book_ids = (clone $query)->distinct()->pluck('books.id');
        $counts = DB::table('books_char_val')
            ->whereIn('book_id', $matching_book_ids)
            ->select('char_val_id', DB::raw('count(*) as total'))
            ->groupBy('char_val_id')
            ->pluck('total', 'char_val_id')->toArray();

        // --- 5. СОРТИРОВКА ---
        $order = $request->query('order');
        if ($order) {
            $parts = explode('-', $order);
            $field = $parts[0];
            $direction = $parts[1] ?? 'asc';

            if ($field === 'name') {
                $query->join('books_translates', 'books.id', '=', 'books_translates.book_id')
                    ->where('books_translates.lang', $locale)->select('books.*')->orderBy('books_translates.name', $direction);
            } elseif (isset($chars_for_sorted_map[$field])) {
                $charId = $chars_for_sorted_map[$field]['id'];
                $sortSub = DB::table('char_vals_trans as cvt')
                    ->join('char_vals as cv', 'cvt.char_val_id', '=', 'cv.id')
                    ->join('books_char_val as bcv', 'cv.id', '=', 'bcv.char_val_id')
                    ->whereColumn('bcv.book_id', 'books.id')
                    ->where('cv.characteristic_id', $charId)->where('cvt.lang', $locale)
                    ->select('cvt.name')->limit(1);
                $query->select('books.*')->selectSub($sortSub, 'sort_val');
                $charObj = $chars_for_sorted_by->find($charId);
                if ($charObj && $charObj->is_numeric) {
                    $query->orderByRaw("ISNULL(sort_val) ASC, CAST(NULLIF(sort_val, '') AS SIGNED) $direction");
                } else {
                    $query->orderByRaw("ISNULL(sort_val) ASC, LOWER(sort_val) COLLATE utf8mb4_unicode_ci $direction");
                }
            } else { $query->orderBy('books.id', 'desc'); }
        } else { $query->orderBy('books.id', 'desc'); }

        $books = $query->with(['translates', 'tags.translates', 'char_vals.characteristic', 'char_vals.translates'])
                       ->distinct()->get();

        // Обработка коллекции книг (Авторы/Типы для шаблона)
        $books->each(function($b) use ($locale) {
            $b->setRelation('translates', $b->translates->keyBy('lang'));
            $b->tags->each(fn($tag) => $tag->setRelation('translates', $tag->translates->keyBy('lang')));
            $b->authors = $b->char_vals->filter(fn($val) => optional($val->characteristic)->is_author == 1);
            $b->edition_types = $b->char_vals->filter(fn($val) => optional($val->characteristic)->is_type == 1);
        });

        // --- 6. ОБНОВЛЕНИЕ СЧЕТЧИКОВ В $CHARS ---
        $chars->each(function($char) use ($counts) {
            foreach ($char->char_vals as $val) {
                $val->books_count = $counts[$val->id] ?? 0;
            }
            // Сортируем значения в фильтре: сначала те, где есть книги
            $char->setRelation('char_vals', $char->char_vals->sortByDesc('books_count'));
        });

        // --- 7. SEO И ЛОГИ ---
        $local_seo = null;
        if (!empty($filters)) {
            $numeric_ids = array_keys($selected_input_range);
            $seo_data = Characteristic::whereIn('id', $selected_chars_id)->whereNotIn('id', $numeric_ids)
                ->with(['translates' => fn($q) => $q->where('lang', $locale),
                        'char_vals' => fn($q) => $q->whereIn('id', $selected_char_vals_id)
                                                   ->with(['translates' => fn($t) => $t->where('lang', $locale)])])
                ->get();

            if ($seo_data->isNotEmpty()) {
                $title_s = []; $desc_s = [];
                foreach ($seo_data as $c) {
                    $c_n = $c->translates->first()->name ?? '';
                    $v_n = $c->char_vals->map(fn($v) => $v->translates->first()->name ?? '')->filter()->implode(', ');
                    if ($c_n && $v_n) { $title_s[] = "$c_n: $v_n"; $desc_s[] = "$c_n: $v_n"; }
                }
                $m_t = Str::limit(implode('; ', $title_s), 71);
                $m_d = Str::limit(implode('; ', $desc_s), 139);
                $q_s = DB::table('settings')->where('type', 'seoTemplates')->first();
                if($q_s) {
                    $tps = json_decode($q_s->value, true)[$locale];
                    $m_t = str_replace('REPLACE', $m_t, $tps['title']);
                    $m_d = str_replace('REPLACE', $m_d, $tps['description']);
                }
                $local_seo = ['meta_title' => $m_t, 'meta_description' => $m_d, 'og_title' => $m_t, 'og_description' => $m_d];
            }
        }

        if ($request->anyFilled(['search', 'tag', 'order']) || !empty($filters)) {
            UserActivity::create([
                'type' => 'search', 'search_query' => $request->query('search'),
                'filters' => ['url' => $filters, 'parsed' => $selected_char_vals_id, 'ranges' => $selected_input_range],
                'results_count' => $books->count(), 'locale' => $locale, 'user_ip' => $request->ip()
            ]);
        }

        return view('main_page.search', [
            'title' => 'Пошук',
            'chars' => $chars,
            'selected_char_vals_id' => $selected_char_vals_id,
            'books' => $books,
            'chars_for_sorted_map' => $chars_for_sorted_map,
            'selected_input_range' => $selected_input_range,
            'translates' => DB::table('translates')->pluck($locale, 'slug'),
            'local_seo' => $local_seo
        ]);
    }
}