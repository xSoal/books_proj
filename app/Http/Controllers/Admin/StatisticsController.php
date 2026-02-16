<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index(){
// 1. Генерируем последние 7 дней в формате базы данных (для ключей)
$days = collect(range(6, 0))->map(function($i) {
    return now()->subDays($i)->format('Y-m-d');
});

// В методе StatisticsController@index
$activities = UserActivity::where('created_at', '>=', now()->subDays(7))
    ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'), 'type')
    ->groupBy('date', 'type')
    ->get();

$chartData = [
    'labels' => $days->map(fn($d) => \Carbon\Carbon::parse($d)->format('d.m'))->toArray(),
    'views' => $days->map(function($d) use ($activities) {
        // Явно ищем тип 'view'
        return (int) ($activities->where('date', $d)->where('type', 'view')->first()->count ?? 0);
    })->toArray(),
    'searches' => $days->map(function($d) use ($activities) {
        // Явно ищем тип 'search'
        return (int) ($activities->where('date', $d)->where('type', 'search')->first()->count ?? 0);
    })->toArray(),
];

        // 2. Топ-10 поисковых запросов (для круговой диаграммы или списка)
        $topQueries = UserActivity::where('type', 'search')
            ->whereNotNull('search_query')
            ->select('search_query', DB::raw('count(*) as total'))
            ->groupBy('search_query')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

            // Топ-5 популярных типов контента (книга, статья и т.д.)
        $topTypes = UserActivity::where('type', 'view')
            ->whereNotNull('book_id')
            ->with('book.char_vals.characteristic') // Предполагая, что тип это характеристика
            ->get()
            ->groupBy('book_id')
            ->take(5);

        $recentActivities = UserActivity::with(['book.translates']) 
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->paginate(25);

        $statsCounts = [
            'books' => \App\Models\Book::count(),
            'characteristics' => \App\Models\Characteristic::count(),
            'values' => \App\Models\CharacteristicValue::count(),
        ];


        $title = 'Адмін панель';    
    
        return view('admin.index', compact('chartData', 'topQueries', 'recentActivities', 'statsCounts', 'title'));
    }

}
