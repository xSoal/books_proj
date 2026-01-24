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

        $data = [
            'title' => 'title',
            'partners' => $partners,
            'about_us' => $about_us->$locale
            // 'seo' => $seo
        ];



        return view('main_page.index', $data); 
    }
}
