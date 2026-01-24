<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeoController extends Controller
{
    public function index(){
        $setting = DB::table('settings')
                ->where('type', 'seo')
                ->first();

        $seo = json_decode($setting->value, true);
        // dd($seo);

        $data = [
            'title' => 'Seo',
            'seo' => $seo,
        ];

        return view('admin.seo.edit', $data);
    }

    public function edit(Request $request){
        $input = $request->except('_token');
        
        $page = $input['page'];
        unset($input['page']);
        
        $seo_json = DB::table('settings')
            ->where('type', 'seo')
            ->first();

        if($seo_json){
            $seo = json_decode($seo_json->value, true);
            $seo[$page] = $input;
        } else {
            $seo = [
                $page => $input
            ];
        }

        $updated = DB::table('settings')->updateOrInsert(
            [
                'type' => 'seo',
            ],
            [
                'value' => json_encode($seo),
                'updated_at' => now() 
            ]
        );

        return redirect()->route('admin.seo');
    }


}
