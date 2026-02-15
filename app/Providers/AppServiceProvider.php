<?php

namespace App\Providers;

use App\View\Composers\LoginComposer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(255);

        View::composer(
            ['auth.login'], // 💡 Вкажіть ваші базові шаблони
            LoginComposer::class
        );

        // Переменная будет доступна только в шаблоне auth.login
        View::composer('*', function ($view) {
            $locale = app()->getLocale();
            $translates = cache()->rememberForever('translates_' . $locale, function() use ($locale) {
                return DB::table('translates')->pluck($locale, 'slug');
            });

            $view->with('translates', $translates);
        });
        }


}
