<?php

namespace App\Http\Controllers\MainPage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;



class ContactsController extends Controller
{
    public function index(Request $request){
        $setting = DB::table('settings')
                ->where('type', 'seo')
                ->first();

        $seo = json_decode($setting->value);
        
        $titles = DB::table('settings')
            ->where('type', 'titles')
            ->first()->value;
        $title = json_decode($titles)->contacts;
        
        $data = [
            'seo' => $seo,
            'title' => $title
        ];


        return view('main_page.contacts', $data);

    }

    public function submit(Request $request){
        $name = $request['name'];
        $phone = $request['phone'];
        $email = $request['email'];
        $message = $request['message'];


        $setting = DB::table('settings')
                ->where('type', 'email')
                ->first();

        $emailAdmin = $setting->value;

        $html = "
            <p><b>Им'я:</b> $name</p>
            <p><b>Телефон:</b> $phone</p>
            <p><b>Пошта:</b> $email</p>
            <p><b>Текст повідомлення:</b> $message</p>
        ";


        if ($emailAdmin) {
            $test = Mail::send([], [], function ($message) use ($emailAdmin, $html) {
                $message->to($emailAdmin)
                        ->subject("Нове повідомлення з форми зворотнього зв'язку")
                        ->html($html);
            });

        }

        $titles = DB::table('settings')
            ->where('type', 'titles')
            ->first()->value;
        $title = json_decode($titles)->contacts;

        $data = ['message' => 'Повідомлення відправлене', 'title' => $title];

        return view('main_page.contacts', $data);

    }
}


