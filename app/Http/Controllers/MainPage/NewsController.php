<?php

namespace App\Http\Controllers\MainPage;

use App\Http\Controllers\Controller;
use App\Models\News;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller
{
    public function single(Request $request, $slug){
        $type = $this->getNewsType($request->path());
        if(($type === 'rules' || $type === 'support') && !Auth::user()){
            return redirect()->route('login');
        }
        $today = Carbon::today();
        $newsItem = News::whereDate('public_date', '<=', $today)->where('type', $type)->where('active', 1)->where('slug', '=', $slug)->firstOrFail();
        
        $view_name = 'main_page.single_'.$this->getNewsType($request->path());

        $seo = json_decode($newsItem->seo);

        $title = $newsItem->title;



        $data = [
            'newsItem' => $newsItem,
            'seo' => $seo,
            'title' => $title
        ];
            

        return view($view_name, $data);
    }

    public function allNews(Request $request){
        $type = $this->getNewsType($request->path());
        if(($type === 'rules' || $type === 'support') && !Auth::user()){
            return redirect()->route('login');
        }
        $today = Carbon::today();
        $news = News::whereDate('public_date', '<=', $today)->where('type', $type)->where('active', 1)->orderBy('public_date', 'desc')->paginate(9);
        $setting = DB::table('settings')
            ->where('type', 'seo')
            ->first();

        $seo = json_decode($setting->value);

        $titles = DB::table('settings')
            ->where('type', 'titles')
            ->first()->value;
        switch ($type) {
            case 'news':
                $title = json_decode($titles)->news;
                break;
            case 'rules':
                $title = json_decode($titles)->rules;
                break;
            case 'support':
                $title = json_decode($titles)->support;
                break;
            default:
                # code...
                break;
        }    


        $data = [
            'news' => $news,
            'seo' => $seo,
            'title' => $title
        ];
            
        
        return view('main_page.'.$type, $data);
    }

    private function getNewsType(string $url) {
        $pattern = '~^/?([^/]+)~';
        preg_match($pattern, $url, $matches);
        $final_string = $matches[1] ?? 'news';
        return $final_string;
    }

    public function reference(){
        if(!Auth::user()){
            return redirect()->route('login');
        }
        $today = Carbon::today();
        $news = News::whereDate('public_date', '<=', $today)
            ->where('active', 1)
            ->where(function ($query) {
                $query->where('type', 'support')
                    ->orWhere('type', 'rules');
            })
            ->orderBy('public_date', 'desc')->paginate(9);

        $setting = DB::table('settings')
            ->where('type', 'seo')
            ->first();

        $seo = json_decode($setting->value);

        $titles = DB::table('settings')
            ->where('type', 'titles')
            ->first()->value;
        $title = json_decode($titles)->reference;

        $data = [
            'news' => $news,
            'seo' => $seo,
            'title' => $title
        ];
        
        return view('main_page.reference_information', $data);
    }
}
