<?php

namespace App\Http\Controllers\MainPage;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Characteristic;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(Request $request, $slug)
    {
        $book = Book::whereHas('translates', function ($query) use ($slug) {
            $query->where('slug', $slug);
        })->with([
            'translates', 
            'tags.translates', 
            'char_vals.characteristic.translates', // Нужно для определения автора
            'char_vals.translates'
        ])->firstOrFail();

        $locale = app()->getLocale();

        $book->setRelation('translates', $book->translates->keyBy('lang'));
        $book->tags->each(function ($tag) use ($locale) {
            $tag->setRelation('translates', $tag->translates->keyBy('lang'));
        });

        $chars = $book->char_vals->groupBy('characteristic_id')->map(function ($vals) use ($locale) {
            $characteristic = $vals->first()->characteristic;
            $characteristic->setRelation('translates', $characteristic->translates->keyBy('lang'));
            
            $vals->each(function ($v) use ($locale) {
                $v->setRelation('translates', $v->translates->keyBy('lang'));
            });
            
            $characteristic->setRelation('char_vals', $vals);
            return $characteristic;
        });

        $authorValue = $book->char_vals->first(function ($val) {
            return $val->characteristic && $val->characteristic->is_author == 1;
        });


        $otherBooks = collect();

        if ($authorValue) {
            $otherBooks = Book::where('books.id', '!=', $book->id)
                ->where('books.active', 1)
                ->whereHas('char_vals', function ($q) use ($authorValue) {
                    $q->where('char_vals.id', $authorValue->id);
                })
                ->with('translates')
                ->limit(25)
                ->get();

            $otherBooks->each(fn($b) => $b->setRelation('translates', $b->translates->keyBy('lang')));
        }


        $translates = DB::table('translates')->pluck($locale, 'slug');


        // Логи
        UserActivity::create([
            'type' => 'view',
            'book_id' => $book->id,
            'locale' => app()->getLocale(),
            'user_ip' => $request->ip()
        ]);

        return view('main_page.book', [
            'title' => $book->translates[$locale]->name ?? 'Book',
            'book' => $book,
            'chars' => $chars, // Передаем сгруппированные характеристики
            'translates' => $translates,
            'otherBooks' => $otherBooks
        ]);
    }
}