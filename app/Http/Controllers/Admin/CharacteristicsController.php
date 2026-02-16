<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Characteristic;
use App\Models\CharacteristicTranslate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CharacteristicsController extends Controller
{
    public function post(Characteristic $characteristic, Request $request){

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
                $exists = CharacteristicTranslate::where('slug', $slug)->exists();
                
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
				return redirect()->route('admin.addCharacteristic')->withErrors($validator)->withInput();
			}


            $characteristic->fill($input);
            $characteristic_save = $characteristic->save();

            foreach ($languages as $lang) {
                $characteristic_translate = new CharacteristicTranslate;
                $characteristic_translate->lang = $lang;
                $characteristic_translate->characteristic_id = $characteristic->id;
                $characteristic_translate->name = $input['name'][$lang];
                $characteristic_translate->description = $input['description'][$lang];
                $characteristic_translate->slug = Str::slug($input['name'][$lang]);
                $characteristic_translate->save();
            }

            if( $characteristic_save ){
                if( isset($input['save_and_exit']) ){
				    return redirect()->route('admin.characteristics')->with('status','Додано');
                }else{
                    return redirect()->route('admin.addCharacteristic')->with('status','Додано');
                }
			}
			
		}

        if( isset($input['update']) || isset($input['update_and_exit']) ){

            $characteristic = Characteristic::find($input['id']);
            
            $characteristic->fill($input);
            $characteristic_update = $characteristic->update();

            $languages = json_decode($input['languages']);

            foreach ($languages as $lang) {
                $data = [
                    'name' => $input['name'][$lang]
                ];

                CharacteristicTranslate::updateOrCreate(
                    [
                        'characteristic_id' => $input['id'],
                        'lang' => $lang
                    ],
                    $data
                );
            }

			if( $characteristic_update ){
                if( isset($input['update_and_exit']) ){
				    return redirect()->route('admin.characteristics')->with('status','Оновлено');
                }else{
                    return redirect()->route('admin.viewCharacteristic',['id' => $input['id'] ])->with('status','Оновлено');
                }
			}
        }
        //-----------------------------------------------------------------



        //-----------------------------------------------------------------
        if( isset($input['dell']) ){
            $tmp = Characteristic::where('id',$input['id'])->first();
            $tmp->delete();
            return redirect()->route('admin.characteristics')->with('status','Видалено');
        }
        //-----------------------------------------------------------------


        //-----------------------------------------------------------------
        if( isset($input['search']) && $input['search']!=null ){
            if(view()->exists('admin.characteristics.list')){
				$search = $input['search'];
				$paginate = 25;
				

                // $items = Characteristic::where(function($query) use ($search) {
                //                         $query->orWhere('name', 'LIKE', '%'.$search.'%')
                //                             ->orWhere('email', 'LIKE', '%'.$search.'%');
                //                         })
                //                         ->paginate($paginate);

                $items = Characteristic::where(function($query) use ($search) {
                    $query->whereHas('translates', function($q) use ($search) {
                        $q->where(function($innerQ) use ($search) {
                            $innerQ->where('name', 'LIKE', '%'.$search.'%')
                                    ->orWhere('slug', 'LIKE', '%'.$search.'%');
                        });
                    });
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
				return 	view('admin.characteristics.list',$data);
			}
			abort(404);
        }
        //-----------------------------------------------------------------
        
        return redirect()->route('admin.characteristics');
    }


    public function view($id){
		if(view()->exists('admin.characteristics.edit')){
            $item = Characteristic::where('id', '=', $id)->first();
            $item->setRelation('translates', $item->translates->keyBy('lang'));

            $data = [
					'title' => 'Редактировать пользователя',
					'item' => $item,
				];
			return 	view('admin.characteristics.edit',$data);
		}
        abort(404);
    }

    public function add(){
		if(view()->exists('admin.characteristics.edit') ){

            $data = [
                'title' => 'Добавить пользователя',
                ];
			return 	view('admin.characteristics.edit',$data);
		}
		abort(404);
	}

    public function list(Request $request){
		if(view()->exists('admin.characteristics.list')){

			$paginate = 25;

            $items = Characteristic::with('translates')->paginate($paginate);
            $items->getCollection()->transform(function ($characteristic) {
                $characteristic->setRelation('translates', $characteristic->translates->keyBy('lang'));
                return $characteristic;
            });



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
			return 	view('admin.characteristics.list',$data);
		}
		abort(404);
	}
}
