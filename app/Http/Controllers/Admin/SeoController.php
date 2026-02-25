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

    public function edit(Request $request)
    {
        $input = $request->except('_token');
        $page = $request->input('page');
    
        $setting = DB::table('settings')->where('type', 'seo')->first();
        $seo = $setting ? json_decode($setting->value, true) : [];
    
        $pageData = $request->only([
            'meta_title', 'meta_description', 'meta_keywords', 'og_title', 'og_description'
        ]);
    
        // Если в запросе есть 'img' и он не пустой — сохраняем его.
        if ($request->filled('img')) {
            $pageData['img'] = $request->input('img');
        } 
        // Если в запросе НЕТ 'img' (блок удален), но есть маркер img_container_exists,
        // значит картинку специально удалили. В этом случае в $pageData ключа 'img' не будет.
    
        $seo[$page] = $pageData;
    
        DB::table('settings')->updateOrInsert(
            ['type' => 'seo'],
            [
                'value' => json_encode($seo, JSON_UNESCAPED_UNICODE),
                'updated_at' => now()
            ]
        );
    
        return redirect()->route('admin.seo');
    }


}
