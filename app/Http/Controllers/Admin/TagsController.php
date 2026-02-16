<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\TagTranslate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TagsController extends Controller {
    public function post(Tag $tag, Request $request){

        $input = $request->except('_token');
        
        if(isset($input['languages'])){
            $languages = json_decode($input['languages']);
        }

        $input['img'] = isset($input['img']) ? $input['img'] : '';
        
        //-----------------------------------------------------------------
        if( isset($input['save']) ||  isset($input['save_and_exit']) ){
            $generatedSlugs = [];
            foreach ($languages as $lang) {
                $name = $input['name'][$lang] ?? '';

                $slug = Str::slug($name);

                if (in_array($slug, $generatedSlugs)) {
                    return redirect()->back()->withErrors(['slug' => "Назви на різних мовах генерують однаковий slug: $slug"])->withInput();
                }
                
                $generatedSlugs[$lang] = $slug;
            }

            foreach ($generatedSlugs as $lang => $slug) {
                $exists = TagTranslate::where('slug', $slug)->exists();
                
                if ($exists) {
                    return redirect()->back()
                        ->withErrors(['slug' => "Slug '$slug' (мова $lang) вже зайнятий. Потрібно змінити назву"])
                        ->withInput();
                }
            }

            $messages = [
				'required' => 'Поле required: поле має бути заповнене',
			];
			
			$validator = Validator::make($input, [
					'name.*' => 'required|string',
				],$messages);
            
			if ($validator->fails()) {
				return redirect()->route('admin.addTag')->withErrors($validator)->withInput();
			}


            $tag->fill($input);
            $tag_save = $tag->save();

            foreach ($languages as $lang) {
                $tag_translate = new TagTranslate;
                $tag_translate->lang = $lang;
                $tag_translate->tag_id = $tag->id;
                $tag_translate->name = $input['name'][$lang];
                $tag_translate->slug = Str::slug($input['name'][$lang]);
                $tag_translate->save();
            }

            if( $tag_save ){
                if( isset($input['save_and_exit']) ){
				    return redirect()->route('admin.tags')->with('status','Додано');
                }else{
                    return redirect()->route('admin.addTag')->with('status','Додано');
                }
			}
			
		}

        if( isset($input['update']) || isset($input['update_and_exit']) ){

            $tag = Tag::find($input['id']);
            
            $tag->fill($input);
            $tag_update = $tag->update();

            $languages = json_decode($input['languages']);

            foreach ($languages as $lang) {
                $data = [
                    'name' => $input['name'][$lang]
                ];

                TagTranslate::updateOrCreate(
                    [
                        'tag_id' => $input['id'],
                        'lang' => $lang
                    ],
                    $data
                );
            }

			if( $tag_update ){
                if( isset($input['update_and_exit']) ){
				    return redirect()->route('admin.tags')->with('status','Оновлено');
                }else{
                    return redirect()->route('admin.viewTag',['id' => $input['id'] ])->with('status','Оновлено');
                }
			}
        }
        //-----------------------------------------------------------------



        //-----------------------------------------------------------------
        if( isset($input['dell']) ){
            $tmp = Tag::where('id',$input['id'])->first();
            $tmp->delete();
            return redirect()->route('admin.tags')->with('status','Видалено');
        }
        //-----------------------------------------------------------------


        //-----------------------------------------------------------------
        if( isset($input['search']) && $input['search']!=null ){
            if(view()->exists('admin.tags.list')){
				$search = $input['search'];
				$paginate = 25;
				

                // $items = Tag::where(function($query) use ($search) {
                //                         $query->orWhere('name', 'LIKE', '%'.$search.'%')
                //                             ->orWhere('email', 'LIKE', '%'.$search.'%');
                //                         })
                //                         ->paginate($paginate);
                $items = Tag::where(function($query) use ($search) {
                    $query->whereHas('translates', function($q) use ($search) {
                        $q->where(function($innerQ) use ($search) {
                            $innerQ->where('name', 'LIKE', '%'.$search.'%')
                                    ->orWhere('slug', 'LIKE', '%'.$search.'%');
                        });
                    });
                    
                    // $query->orWhere('email', 'LIKE', '%'.$search.'%'); 
                })
                ->with('translates') 
                ->paginate($paginate); 
            
                $items->getCollection()->transform(function ($book) {
                    $book->setRelation('translates', $book->translates->keyBy('lang'));
                    return $book;
                });
				
                if( $request['page']==null ){
					$request['page'] = 1;
				}
				$page = $paginate * ($request['page']-1);
                
                $data = [
                        'title' => 'Пользователи',
                        'items' => $items,
                        'search' => $search,
						'page' => $page
                    ];
				return 	view('admin.tags.list',$data);
			}
			abort(404);
        }
        //-----------------------------------------------------------------
        
        return redirect()->route('admin.tags');
    }


    public function view($id){
		if(view()->exists('admin.tags.edit')){
            $item = Tag::where('id', '=', $id)->first();
            $item->setRelation('translates', $item->translates->keyBy('lang'));

            $data = [
					'title' => 'Редактировать пользователя',
					'item' => $item,
				];
			return 	view('admin.tags.edit',$data);
		}
        abort(404);
    }

    public function add(){
		if(view()->exists('admin.tags.edit') ){

            $data = [
                'title' => 'Добавить пользователя',
                ];
			return 	view('admin.tags.edit',$data);
		}
		abort(404);
	}

    public function list(Request $request){
		if(view()->exists('admin.tags.list')){

			$paginate = 25;

            $items = Tag::with('translates')->paginate($paginate);
            $items->getCollection()->transform(function ($tag) {
                $tag->setRelation('translates', $tag->translates->keyBy('lang'));
                return $tag;
            });

            // dd($items->first()->translates);

			if( $request['page']==null ){
				$request['page'] = 1;
			}

			$page = $paginate * ($request['page']-1);

			$data = [
					'title' => 'Партнери',
					'items' => $items,
					'search' => '',
					'page' => $page
				];
			return 	view('admin.tags.list',$data);
		}
		abort(404);
	}
}
