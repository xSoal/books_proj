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

class BrowseController extends Controller
{
    public function index(Request $request){

        $locale = app()->getLocale();
        $translates = DB::table('translates')->pluck($locale, 'slug');


        $chars_for_sorted_by = Characteristic::where('can_sorted_by', 1)->with('translates')->get();
        $chars_for_sorted_map = [];
        foreach ($chars_for_sorted_by as $value) {
            $value->setRelation('translates', $value->translates->keyBy('lang'));
            $chars_for_sorted_map[$value->translates[app()->getLocale()]->slug] = [
                'name' => $value->translates[app()->getLocale()]->name,
                'id' => $value->id
            ];
        }


        $order = $request->query('order');

        $query = Book::select('books.*')->where('books.active', 1);

        if ($order) {
            $parts = explode('-', $order);
            $field = $parts[0];
            $direction = $parts[1] ?? 'asc';
        
            switch ($field) {
                case 'name':
                    // Используем LEFT join, чтобы не терять книги без перевода
                    $query->leftJoin('books_translates', function($join) {
                        $join->on('books.id', '=', 'books_translates.book_id')
                             ->where('books_translates.lang', '=', app()->getLocale());
                    })
                    ->orderByRaw("ISNULL(books_translates.name) ASC, books_translates.name $direction");
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
        
                        $query->selectSub($sortQuery, 'sort_val');
        
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
            // Стандартная сортировка (без JOIN-ов)
            $query->orderBy('books.id', 'desc');
        }
        
        // 2. Убираем distinct(), если нет фильтрации через whereHas, вызывающей дубли
        $books = $query->with('translates')->paginate(5)->withQueryString();
        
        $books->each(fn($b) => $b->setRelation('translates', $b->translates->keyBy('lang')));


        $data = [
            'translates' => $translates,
            'books' => $books,
            'chars_for_sorted_map' => $chars_for_sorted_map
        ];

        return view('main_page.browse', $data);
    }
}
