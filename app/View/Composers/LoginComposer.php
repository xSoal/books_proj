<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class LoginComposer
{
    public function compose(View $view)
    {

        // $titles = DB::table('settings')
        //     ->where('type', 'titles')
        //     ->first()->value;
        // $title = json_decode($titles)->login;
        
        // $view->with('LoginTitle', $title);
    }
}