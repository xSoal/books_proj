<?php

namespace App\Http\Controllers\MainPage;

use App\Http\Controllers\Controller;
use App\Models\News;

use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainPageController extends Controller
{
    public function index(){
        $partners = Partner::where('active', 1)->get();
        $partners->transform(function ($partner) {
            $partner->setRelation('translates', $partner->translates->keyBy('lang'));
            return $partner;
        });

        $about_us = DB::table('settings')->where('type', 'about_us')->first();
        
        $locale = app()->getLocale();
        $translates = DB::table('translates')->pluck($locale, 'slug');

        $data = [
            'title' => 'title',
            'partners' => $partners,
            'about_us' => $about_us->$locale,
            'translates' => $translates
            // 'seo' => $seo
        ];



        return view('main_page.index', $data); 
    }

    
    public function about(){

        $about_us = DB::table('settings')->where('type', 'about_us_full')->first();
        
        $locale = app()->getLocale();
        $translates = DB::table('translates')->pluck($locale, 'slug');

        $data = [
            'title' => 'title',
            'about_us' => $about_us->$locale,
            'translates' => $translates
            // 'seo' => $seo
        ];



        return view('main_page.about', $data); 
    }
}
