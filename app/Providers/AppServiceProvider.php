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

        

        View::composer('*', function ($view) {
            $locale = app()->getLocale();
            // $translates = cache()->rememberForever('translates_' . $locale, function() use ($locale) {
            //     return DB::table('translates')->pluck($locale, 'slug');
            // });
            $translates = DB::table('translates')->pluck($locale, 'slug');

            $view->with('translates', $translates);
        });

        View::composer('*', function ($view) {
            $routeName = request()->route()?->getName();

            $global_seo = DB::table('settings')->where('type', 'seo')->first();
            $global_seo = json_decode($global_seo->value, true)[$routeName] ?? null;

            $view->with('global_seo', $global_seo);
        });


    }




}
