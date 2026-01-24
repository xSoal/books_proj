<?php

namespace App\Http\Controllers\MainPage;

use App\Http\Controllers\Controller;
use App\Models\News;


use Carbon\Carbon;
use Illuminate\Http\Request;


use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request){
        $search = trim($request->input('search'));
        $perPage = 9;

        $titles = DB::table('settings')
            ->where('type', 'titles')
            ->first()->value;
        $title = json_decode($titles)->search;
        


        if(!$search){
            // Создание пустого пагинатора, без запроса к БД
            $resultSearch = new LengthAwarePaginator(
                new Collection(), 
                0,
                $perPage,
                LengthAwarePaginator::resolveCurrentPage(),
                ['path' => $request->url(), 'query' => $request->query()]
            );

        } else {

            $searchPattern = '%' . $search . '%';
            $today = Carbon::today();

            $query = News::whereDate('public_date', '<=', $today)
                ->where('active', 1)
                ->where(function ($q) use ($searchPattern) {
                    $q->where('title', 'LIKE', $searchPattern) 
                    ->orWhere('content', 'LIKE', $searchPattern);
                });

            if (!Auth::user()) {
                $query->where('type', 'news');
            }
            
            $resultSearch = $query->orderBy('public_date', 'desc')
                ->paginate($perPage)
                ->appends(['search' => $search]);

        }


        $data = [
            'title' => $title,
            'search' => $search,
            'resultSearch' => $resultSearch
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
