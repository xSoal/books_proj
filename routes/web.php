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


    Route::get('/',function(){
        $data = [
            'title' => 'Особистий кабінет',
        ];
        return view('admin.index',$data);
    });


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
    Route::get('/search/{filters?}', ['uses' => '\App\Http\Controllers\MainPage\SearchController@index', 'as' => 'search'])
        ->where('filters', '.*');

    Route::get('/book/{slug}', ['uses' => '\App\Http\Controllers\MainPage\BookController@index', 'as' => 'book']);
});



//Переключение языков
Route::get('setlocale/{lang}', function ($lang) {
    $referer = Redirect::back()->getTargetUrl(); //URL предыдущей страницы
    $parse_url = parse_url($referer, PHP_URL_PATH); //URI предыдущей страницы
    //разбиваем на массив по разделителю
    $segments = explode('/', $parse_url);
    //Если URL (где нажали на переключение языка) содержал корректную метку языка
    if (in_array($segments[1], App\Http\Middleware\LocaleMiddleware::$languages)) {
    unset($segments[1]); //удаляем метку
    }
    //Добавляем метку языка в URL (если выбран не язык по-умолчанию)
    if ($lang != App\Http\Middleware\LocaleMiddleware::$mainLanguage){
    array_splice($segments, 1, 0, $lang);
    }
    //формируем полный URL
    $url = Request::root().implode("/", $segments);
    //если были еще GET-параметры - добавляем их
    if(parse_url($referer, PHP_URL_QUERY)){
    $url = $url.'?'. parse_url($referer, PHP_URL_QUERY);
    }
    return redirect($url); //Перенаправляем назад на ту же страницу
})->name('setlocale');
    

