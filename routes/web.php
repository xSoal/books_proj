<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/





// API ROUTE
Route::group(['prefix' => 'api', 'middleware' => 'web'], function () {
    
    Route::post('/find-next-post', ['uses' => '\App\Http\Controllers\API\APIController@findNextPost']);

    // Admin API route
    Route::group(['prefix' => 'admin', 'middleware' => 'auth' ], function () {
        Route::post('/genslug', ['uses' => '\App\Http\Controllers\API\APIController@genSlug']);
        Route::post('/getUserInfo', ['uses' => '\App\Http\Controllers\API\APIController@getUserInfo']);
        Route::post('/change-active', ['uses' => '\App\Http\Controllers\API\APIController@changeActive']);
        Route::post('/create-row', ['uses' => '\App\Http\Controllers\API\APIController@createRow']);
        Route::post('/remove-row', ['uses' => '\App\Http\Controllers\API\APIController@removeRow']);
        Route::post('/update-row-name', ['uses' => '\App\Http\Controllers\API\APIController@updateRowName']);
        Route::post('/update-row-color', ['uses' => '\App\Http\Controllers\API\APIController@updateRowColor']);
    });
});
//--------------------------------





// main route
Route::group(['prefix' => '/admin', 'middleware' => 'auth'], function() {


    // Route::get('/',function(){
    //     $data = [
    //         'title' => 'Особистий кабінет',
    //     ];
    //     return view('admin.index', $data);
    // });

    Route::get('/', ['uses' => '\App\Http\Controllers\Admin\StatisticsController@index', 'as' => 'admin']);


    Route::group(['prefix' => 'users'], function () {
        Route::get('/', ['uses' => '\App\Http\Controllers\Admin\UsersController@list', 'as' => 'admin.users']);
        Route::get('/add', ['uses' => '\App\Http\Controllers\Admin\UsersController@add', 'as' => 'admin.addUser']);
        Route::get('/{id}', ['uses' => '\App\Http\Controllers\Admin\UsersController@view', 'as' => 'admin.viewUser']);
        Route::post('/', ['uses' => '\App\Http\Controllers\Admin\UsersController@post', 'as' => 'admin.postUsers']);
    });

    Route::group(['prefix' => 'partners'], function () {
        Route::get('/', ['uses' => '\App\Http\Controllers\Admin\PartnersController@list', 'as' => 'admin.partners']);
        Route::get('/add', ['uses' => '\App\Http\Controllers\Admin\PartnersController@add', 'as' => 'admin.addPartner']);
        Route::get('/{id}', ['uses' => '\App\Http\Controllers\Admin\PartnersController@view', 'as' => 'admin.viewPartner']);
        Route::post('/', ['uses' => '\App\Http\Controllers\Admin\PartnersController@post', 'as' => 'admin.postPartners']);
    });

    Route::group(['prefix' => 'books'], function () {
        Route::get('/', ['uses' => '\App\Http\Controllers\Admin\BooksController@list', 'as' => 'admin.books']);
        Route::get('/add', ['uses' => '\App\Http\Controllers\Admin\BooksController@add', 'as' => 'admin.addBook']);
        Route::get('/{id}', ['uses' => '\App\Http\Controllers\Admin\BooksController@view', 'as' => 'admin.viewBook']);
        Route::post('/', ['uses' => '\App\Http\Controllers\Admin\BooksController@post', 'as' => 'admin.postBooks']);
    });

    Route::group(['prefix' => 'characteristics'], function () {
        Route::get('/', ['uses' => '\App\Http\Controllers\Admin\CharacteristicsController@list', 'as' => 'admin.characteristics']);
        Route::get('/add', ['uses' => '\App\Http\Controllers\Admin\CharacteristicsController@add', 'as' => 'admin.addCharacteristic']);
        Route::get('/{id}', ['uses' => '\App\Http\Controllers\Admin\CharacteristicsController@view', 'as' => 'admin.viewCharacteristic']);
        Route::post('/', ['uses' => '\App\Http\Controllers\Admin\CharacteristicsController@post', 'as' => 'admin.postCharacteristics']);
    });

    Route::group(['prefix' => 'characteristics_values'], function () {
        Route::get('/', ['uses' => '\App\Http\Controllers\Admin\CharacteristicValuesController@list', 'as' => 'admin.characteristicValues']);
        Route::get('/add', ['uses' => '\App\Http\Controllers\Admin\CharacteristicValuesController@add', 'as' => 'admin.addCharacteristicValue']);
        Route::get('/{id}', ['uses' => '\App\Http\Controllers\Admin\CharacteristicValuesController@view', 'as' => 'admin.viewCharacteristicValue']);
        Route::post('/', ['uses' => '\App\Http\Controllers\Admin\CharacteristicValuesController@post', 'as' => 'admin.postCharacteristicValues']);
    });

    Route::group(['prefix' => 'tag'], function () {
        Route::get('/', ['uses' => '\App\Http\Controllers\Admin\TagsController@list', 'as' => 'admin.tags']);
        Route::get('/add', ['uses' => '\App\Http\Controllers\Admin\TagsController@add', 'as' => 'admin.addTag']);
        Route::get('/{id}', ['uses' => '\App\Http\Controllers\Admin\TagsController@view', 'as' => 'admin.viewTag']);
        Route::post('/', ['uses' => '\App\Http\Controllers\Admin\TagsController@post', 'as' => 'admin.postTags']);
    });

    Route::group(['prefix' => 'translates'], function () {
        Route::get('/', ['uses' => '\App\Http\Controllers\Admin\TranslatesController@list', 'as' => 'admin.translates']);
        Route::get('/add', ['uses' => '\App\Http\Controllers\Admin\TranslatesController@add', 'as' => 'admin.addTranslate']);
        Route::get('/{id}', ['uses' => '\App\Http\Controllers\Admin\TranslatesController@view', 'as' => 'admin.viewTranslate']);
        Route::post('/', ['uses' => '\App\Http\Controllers\Admin\TranslatesController@post', 'as' => 'admin.postTranslate']);
    });

    Route::group(['prefix' => 'import'], function () {
        Route::get('/', ['uses' => '\App\Http\Controllers\Admin\ImportController@index', 'as' => 'admin.import']);
        Route::post('/importAdd', ['uses' => '\App\Http\Controllers\Admin\ImportController@add', 'as' => 'admin.importAdd']);
    });


    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', ['uses' => '\App\Http\Controllers\Admin\SettingsController@index', 'as' => 'admin.settings']);
        Route::post('/updateEmail', ['uses' => '\App\Http\Controllers\Admin\SettingsController@updateEmail', 'as' => 'admin.settings_updateEmail']);
    
    });

    Route::group(['prefix' => 'seo'], function () {
        Route::get('/', ['uses' => '\App\Http\Controllers\Admin\SeoController@index', 'as' => 'admin.seo']);
        Route::post('/edit', ['uses' => '\App\Http\Controllers\Admin\SeoController@edit', 'as' => 'admin.seoEdit']);
    });

});

Route::group(['prefix' => App\Http\Middleware\LocaleMiddleware::getLocale(), 'middleware' => 'locale'], function() {
    // Авторизация
    Auth::routes();
    
    Route::get('/', ['uses' => '\App\Http\Controllers\MainPage\MainPageController@index', 'as' => 'main_page']);
    Route::get('/about', ['uses' => '\App\Http\Controllers\MainPage\MainPageController@about', 'as' => 'about']);
    Route::get('/search/{filters?}', ['uses' => '\App\Http\Controllers\MainPage\SearchController@index', 'as' => 'search'])
        ->where('filters', '.*');

    Route::get('/browse', ['uses' => '\App\Http\Controllers\MainPage\BrowseController@index', 'as' => 'browse']);

    Route::get('/book/{slug}', ['uses' => '\App\Http\Controllers\MainPage\BookController@index', 'as' => 'book']);
});



//Переключение языков
// Route::get('setlocale/{lang}', function ($lang) {
//     $referer = Redirect::back()->getTargetUrl(); //URL предыдущей страницы
//     $parse_url = parse_url($referer, PHP_URL_PATH); //URI предыдущей страницы
//     //разбиваем на массив по разделителю
//     $segments = explode('/', $parse_url);
//     //Если URL (где нажали на переключение языка) содержал корректную метку языка
//     if (in_array($segments[1], App\Http\Middleware\LocaleMiddleware::$languages)) {
//     unset($segments[1]); //удаляем метку
//     }

//     //Добавляем метку языка в URL (если выбран не язык по-умолчанию)
//     if ($lang != App\Http\Middleware\LocaleMiddleware::$mainLanguage){
//     array_splice($segments, 1, 0, $lang);
//     }
//     //формируем полный URL
//     $url = Request::root().implode("/", $segments);
//     // if(str_contains($url, '/search/')){
//     //     $url = '/search';
//     // }
//     //если были еще GET-параметры - добавляем их
//     if(parse_url($referer, PHP_URL_QUERY)){
//     $url = $url.'?'. parse_url($referer, PHP_URL_QUERY);
//     }

//     return redirect($url); //Перенаправляем назад на ту же страницу
// })->name('setlocale');
    

Route::get('setlocale/{lang}', function ($lang) {
    $referer = Redirect::back()->getTargetUrl();
    $parse_url = parse_url($referer, PHP_URL_PATH);
    $segments = explode('/', $parse_url);

    // 1. Убираем текущую метку языка из сегментов
    if (in_array($segments[1] ?? '', App\Http\Middleware\LocaleMiddleware::$languages)) {
        unset($segments[1]);
        $segments = array_values($segments); // сбрасываем индексы
    }

    // для книги
    if (count($segments) >= 3 && $segments[1] == 'book') {
        $oldSlug = $segments[2];
        $bookId = DB::table('books_translates')->where('slug', $oldSlug)->value('book_id');
        if ($bookId) {
            $newSlug = DB::table('books_translates')
                ->where('book_id', $bookId)
                ->where('lang', $lang)
                ->value('slug');
            
            if ($newSlug) $segments[2] = $newSlug;
        }
    }

    if (count($segments) >= 2 && $segments[1] == 'search' && !empty($segments[2])) {
        // Проходим по всем сегментам фильтров и пытаемся их "перевести"
        for ($i = 2; $i < count($segments); $i++) {
            $part = $segments[$i]; // например 'genre-fantasy'
            
            // Ищем характеристику или значение по slug
            // Здесь логика зависит от того, как вы храните slug фильтров. 
            // Нужно найти ID характеристики/значения по старому slug и заменить на новый для $lang
            $segments[$i] = translateFilterSegment($part, $lang); 
        }
    }

    // 3. Добавляем новую метку языка (если не основной)
    if ($lang != App\Http\Middleware\LocaleMiddleware::$mainLanguage) {
        array_splice($segments, 1, 0, $lang);
    }

    $url = Request::root() . implode("/", $segments);

    // Добавляем GET-параметры
    if ($query = parse_url($referer, PHP_URL_QUERY)) {
        $url .= '?' . $query;
    }

    return redirect($url);
})->name('setlocale');

function translateFilterSegment($part, $newLang) {
    // 1. Ищем длинную характеристику в начале строки
    // Нам нужно понять, где заканчивается слаг характеристики и начинаются значения
    // Сначала получим все слаги характеристик для текущего языка (или из кэша)
    $allCharSlugs = DB::table('characteristics_translates')->pluck('slug')->toArray();
    
    // Сортируем по длине (от длинных к коротким), чтобы сначала находить "long-slug", а не "long"
    usort($allCharSlugs, fn($a, $b) => strlen($b) <=> strlen($a));

    $foundCharSlug = null;
    foreach ($allCharSlugs as $slug) {
        if (strpos($part, $slug . '-') === 0) {
            $foundCharSlug = $slug;
            break;
        }
    }

    if (!$foundCharSlug) return $part; // Не опознали фильтр

    // 2. Определяем ID характеристики и её новый слаг
    $charId = DB::table('characteristics_translates')
        ->where('slug', $foundCharSlug)
        ->value('characteristic_id');
        
    $newCharSlug = DB::table('characteristics_translates')
        ->where('characteristic_id', $charId)
        ->where('lang', $newLang)
        ->value('slug') ?: $foundCharSlug;

    // 3. Выделяем часть со значениями (всё, что после "slug-")
    $valuesPart = substr($part, strlen($foundCharSlug) + 1);
    
    // Разбиваем значения. Они могут быть соединены дефисом
    // Но так как слаги значений сами могут содержать дефисы, это сложнее.
    // Самый надежный способ: найти все возможные ID значений для этой характеристики
    $valSlugs = explode('-', $valuesPart);
    $newValSlugs = [];

    // Находим все переводы значений для этой конкретной характеристики
    $allVals = DB::table('char_vals_trans')
        ->join('char_vals', 'char_vals.id', '=', 'char_vals_trans.char_val_id')
        ->where('char_vals.characteristic_id', $charId)
        ->select('char_vals_trans.slug', 'char_vals_trans.char_val_id')
        ->get();

    // Пытаемся сопоставить каждый сегмент
    $tempString = $valuesPart;
    while (strlen($tempString) > 0) {
        $matched = false;
        
        // Сортируем слаги значений текущей характеристики по длине
        $currentLangVals = $allVals->sortByDesc(fn($item) => strlen($item->slug));

        foreach ($currentLangVals as $val) {
            // Если текущая строка начинается со слага или равна ему
            if ($tempString === $val->slug || strpos($tempString, $val->slug . '-') === 0) {
                // Ищем этот же ID на новом языке
                $translatedVal = DB::table('char_vals_trans')
                    ->where('char_val_id', $val->char_val_id)
                    ->where('lang', $newLang)
                    ->value('slug');
                
                $newValSlugs[] = $translatedVal ?: $val->slug;
                
                // Отрезаем найденную часть
                $cutLen = strlen($val->slug);
                if (isset($tempString[$cutLen]) && $tempString[$cutLen] === '-') $cutLen++;
                $tempString = substr($tempString, $cutLen);
                
                $matched = true;
                break;
            }
        }

        if (!$matched) {
            // Если это число (диапазон), просто оставляем как есть
            $parts = explode('-', $tempString);
            $newValSlugs[] = $parts[0];
            $tempString = substr($tempString, strlen($parts[0]) + 1);
        }
    }

    return $newCharSlug . '-' . implode('-', $newValSlugs);
}