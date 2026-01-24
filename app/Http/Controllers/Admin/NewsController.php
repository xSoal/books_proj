<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\News;
use Illuminate\Support\Str;


// use Carbon\Carbon;

class NewsController extends Controller
{

    public function list(Request $request){
        $search = trim($request->input('search', ''));

        $input = $request->except('_token');

        $paginate = 25;

        $getItems = function($search) use ($paginate, $request) {
            if(!$search){
                return News::where('type', $this->getNewsType($request->path()))->orderBy('public_date', 'desc')->paginate($paginate);
            }
            return News::where('type', $this->getNewsType($request->path()))
                ->where('title', 'LIKE', '%' . $search . '%' )
                ->orderBy('public_date', 'desc')
                ->paginate($paginate);
        };
        $items = $getItems($search);

        
        if( $request['page']==null ){
            $request['page'] = 1;
        }

        $page = $paginate * ($request['page']-1);
                
        $data = [
                'title' => 'Новини',
                'search' => $search,
                'items' => $items,
                'page' => $page,
            ];

        $view_name = 'admin.'.$this->getNewsType($request->path()).'.list'; 
        return 	view($view_name, $data);
		
	}

    public function post(News $news, Request $request){
        $type = $request->path();
        $type = str_replace('admin/', '', $type);

        $input = $request->except('_token');
        
        $e = [
            'meta_title' => $request['meta_title'],
            'meta_description' => $request['meta_description'],
            'meta_keywords' => $request['meta_keywords'],
            'og_title' => $request['og_title'],
            'og_description' => $request['og_description'],
            'og_img' => $request['og_img'],
        ];

        $newSeo = json_encode($e);


        //-----------------------------------------------------------------
        if( isset($input['save']) || isset($input['save_and_exit']) ){
            $news->fill($input);
            $news->slug = Str::slug($input['title']);
            $news->seo = $newSeo;

            $type = $this->getNewsType($request->path());
            if($type === 'rules' || $type === 'support'){
                $news->slug = $type . '-' . Str::slug($input['title']);
            }
            
            if( $news->save() ){
                
                if( isset($input['save_and_exit']) ){
                    $view_name = 'admin.'.$this->getNewsType($request->path()); 
                    return redirect()->route($view_name)->with('status','Додано');
                }else{
                    $view_name = 'admin.add_'.$this->getNewsType($request->path()); 
                    return redirect()->route($view_name)->with('status','Додано');
                }
            }
        }
        //-----------------------------------------------------------------

        //-----------------------------------------------------------------
        if( isset($input['update']) || isset($input['update_and_exit']) ){

            $project = News::find($input['id']); 
            $input['slug'] = trim($input['slug']) == '' ? Str::slug($input['title']) : $input['slug'];
            $project->fill($input);
            $project->seo = $newSeo;

            if( $project->update() ){
                if( isset($input['update_and_exit']) ){
                    $view_name = 'admin.'.$this->getNewsType($request->path()); 
                    return redirect()->route($view_name)->with('status','Оновлено');
                }else{
                    $view_name = 'admin.view_'.$this->getNewsType($request->path()); 
                    return redirect()->route($view_name, ['id'=> $input['id'] ] )->with('status','Оновлено');
                }
            }
        }
        //-----------------------------------------------------------------


        //-----------------------------------------------------------------
        if( isset($input['dell']) ){
            $tmp = News::where('id',$input['id'])->first();
            $tmp->delete();
            $view_name = 'admin.'.$this->getNewsType($request->path()); 
            return redirect()->route($view_name)->with('status','Вилучено');
        }
        //-----------------------------------------------------------------


        $view_name = 'admin.'.$this->getNewsType($request->path()); 
        return redirect()->route($view_name);
        
    }

    public function add(Request $request){
        $data = [
            'title' => 'Новини',
            'search' => '',
            'seo' => ''
        ];
        $view_name = 'admin.'.$this->getNewsType($request->path()).'.edit'; 
        return 	view($view_name,$data);
    }

    public function view(Request $request, $id){

        $item = News::where('id', '=', $id)->first();


        $seo = json_decode($item->seo);

        
        $data = [
                'title' => 'Редагувати',
                'item' => $item,
                'seo' => $seo
        ];

        $view_name = 'admin.'.$this->getNewsType($request->path()).'.edit'; 
        return 	view($view_name, $data);
    
    }


    private function getNewsType(string $input_string) {
        // Регулярное выражение для поиска всего, КРОМЕ:
        // 1. Пробельных символов (\s)
        // 2. Слов 'news', 'support', 'rules'
        
        // Шаблон: [^...] - означает "не символ из списка".
        // (news|support|rules) - это позитивный список, который мы ИСКЛЮЧАЕМ из удаления.
        // \s - исключаем пробелы.
        
        $pattern = '/[^\s]*(news|support|rules)[^\s]*(*SKIP)(*FAIL)|[^\s]+/i';
        
        // *SKIP)(*FAIL) - это более продвинутый метод, который позволяет пропустить 
        // найденные ключевые слова и обработать только остальной текст.
        // Однако для простоты и эффективности, часто используют подход с заменой.
    
        // --- Более простой и часто используемый подход ---
        
        // 1. Сначала удаляем все, что НЕ является ключевым словом, пробелом или границей слова.
        // 2. Затем удаляем лишние пробелы.
    
        // Шаблон для поиска всего, что не является буквой, пробелом или одним из ключевых слов.
        // В данном случае, проще всего инвертировать.
    
        // Ищем все, что НЕ является (news|support|rules|\s) и заменяем на пробел.
        // Затем удаляем повторяющиеся пробелы и пробелы по краям.
        
        // Шаг 1: Оставляем только ключевые слова и пробелы, заменяя все остальное на один пробел.
        // /[^a-z\s]/i - ищет все, что не является буквой A-Z или пробелом.
        $temp_string = preg_replace('/[^a-z\s]/i', ' ', $input_string);
        
        // Шаг 2: Удаляем лишние пробелы (оставляем только одинарные).
        $temp_string = preg_replace('/\s+/', ' ', $temp_string);
        
        // Шаг 3: Очищаем строку от всех слов, которые НЕ являются целевыми (news, support, rules).
        // Мы заменяем ЛЮБОЕ слово (\w+) на пустую строку, ЕСЛИ оно не совпадает с (news|support|rules).
        // (\b(news|support|rules)\b) - это слова, которые мы хотим СОХРАНИТЬ.
        // *SKIP|(*FAIL) заставляет движок пропустить совпадения, которые мы хотим сохранить.
        // \w+ - это любое слово, которое мы хотим удалить.
        $final_string = preg_replace('/\b(news|support|rules)\b(*SKIP)(*FAIL)|\w+/i', '', $temp_string);
        
        // Шаг 4: Удаляем пробелы по краям и лишние пробелы (снова, если появились).
        $final_string = trim(preg_replace('/\s+/', ' ', $final_string));
        
        return $final_string;
    }
}
