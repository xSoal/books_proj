<?php

namespace App\Http\Controllers\MainPage;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Characteristic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    //

    public function index(Request $request, $slug)
    {
        $book = Book::whereHas('translates', function ($query) use ($slug) {
            $query->where('slug', $slug);
        })->with('translates')->first();

        $book->setRelation('translates', $book->translates->keyBy('lang'));

        $locale = app()->getLocale();

       
        // TODO сократить запросы

        $book_char_vals_id = DB::table('books_char_val')
            ->where('book_id', $book->id)
            ->get();

        
        $char_val_ids = DB::table('books_char_val')
            ->where('book_id', $book->id)
            ->pluck('char_val_id')
            ->toArray();           

        $chars_id = DB::table('char_vals')
            ->whereIn('id', $char_val_ids)
            ->pluck('characteristic_id') 
            ->toArray();    
            
        $chars = Characteristic::whereIn('id', $chars_id)
            ->with(['translates', 'char_vals' => function($query) use ($char_val_ids) {
                $query->whereIn('id', $char_val_ids) 
                    ->with('translates');
            }])
            ->get()
            ;

        $chars->each(function ($char) {
            $char->setRelation('translates', $char->translates->keyBy('lang'));

            $char->char_vals->each(function ($value) {
                $value->setRelation('translates', $value->translates->keyBy('lang'));
            });
        });    
    

    // $grouped = $chars->groupBy('char_name');

        $locale = app()->getLocale();
        $translates = DB::table('translates')->pluck($locale, 'slug');

        $data = [
            'title' => 'Book',
            'book' => $book,
            'chars' => $chars,
            'translates' => $translates
        ];

        return view('main_page.book', $data);
    }
}
