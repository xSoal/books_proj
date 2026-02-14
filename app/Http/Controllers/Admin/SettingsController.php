<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Request;

class SettingsController extends Controller
{
    public function index(){
        
        $setting = DB::table('settings')
                     ->where('type', 'email')
                     ->first();

        $email = $setting->value; 
        
        $about_us = DB::table('settings')
            ->where('type', 'about_us')
            ->first();

        $about_us_full = DB::table('settings')
            ->where('type', 'about_us_full')
            ->first();                

        $data = [
            'title' => 'Налаштування',
            'email' => $email,
            'about_us' => $about_us,
            'about_us_full' => $about_us_full
        ];
        return view('admin.settings.list', $data);
    }

    public function updateEmail(Request $request){
        $newEmail = $request['email'];

        $updated = DB::table('settings')
                     ->where('type', 'email')
                     ->update([
                         'value' => $newEmail,
                         'updated_at' => now() 
                     ]);

        $updated = DB::table('settings')
            ->where('type', 'about_us')
            ->update([
                'ua' => $request['ua'],
                'en' => $request['en'],
                'updated_at' => now() 
            ]);

        $updated = DB::table('settings')
            ->where('type', 'about_us_full')
            ->update([
                'ua' => $request['about_us_full_ua'],
                'en' => $request['about_us_full_en'],
                'updated_at' => now() 
            ]);       

        
        if ($updated) {
            return redirect()->back()->with('success', 'Email оновлено');
        } else {
            return redirect()->back()->with('error', 'Помилка.');
        }
    }
}
