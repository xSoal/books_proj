<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Characteristic;
use App\Models\CharacteristicValue;
use App\Models\CharacteristicValueTranslate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CharacteristicValuesController extends Controller
{
    public function post(CharacteristicValue $characteristic_value, Request $request){

        $input = $request->except('_token');

        if(isset($input['languages'])){
            $languages = json_decode($input['languages']);
        }

        $input['photo'] = isset($input['photo']) ? $input['photo'] : '';
        
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
                $exists = CharacteristicValueTranslate::where('slug', $slug)->exists();
                
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
				return redirect()->route('admin.addCharacteristicValue')->withErrors($validator)->withInput();
			}

            // dd($input);

            $characteristic_value->fill($input);
            $characteristic_value_save = $characteristic_value->save();

            foreach ($languages as $lang) {
                $characteristic_value_translate = new CharacteristicValueTranslate;
                $characteristic_value_translate->lang = $lang;

                $characteristic_value_translate->char_val_id = $characteristic_value['id'];
                $characteristic_value_translate->name = $input['name'][$lang];
                $characteristic_value_translate->description = $input['description'][$lang];
                $characteristic_value_translate->slug = Str::slug($input['name'][$lang]);
                $characteristic_value_translate->save();
            }

            if( $characteristic_value_save ){
                if( isset($input['save_and_exit']) ){
				    return redirect()->route('admin.characteristicValues')->with('status','Додано');
                }else{
                    return redirect()->route('admin.addCharacteristicValue')->with('status','Додано');
                }
			}
			
		}

        if( isset($input['update']) || isset($input['update_and_exit']) ){

            $characteristic = CharacteristicValue::find($input['id']);
            
            $characteristic->fill($input);
            $characteristic_update = $characteristic->update();

            $languages = json_decode($input['languages']);

            foreach ($languages as $lang) {
                $data = [
                    'name' => $input['name'][$lang],
                    'description' => $input['description'][$lang]
                ];

                CharacteristicValueTranslate::updateOrCreate(
                    [
                        'char_val_id' => $input['id'],
                        'lang' => $lang
                    ],
                    $data
                );
            }

			if( $characteristic_update ){
                if( isset($input['update_and_exit']) ){
				    return redirect()->route('admin.characteristicValues')->with('status','Оновлено');
                }else{
                    return redirect()->route('admin.viewCharacteristicValue',['id' => $input['id'] ])->with('status','Оновлено');
                }
			}
        }
        //-----------------------------------------------------------------



        //-----------------------------------------------------------------
        if( isset($input['dell']) ){
            $tmp = CharacteristicValue::where('id',$input['id'])->first();
            $tmp->delete();
            return redirect()->route('admin.characteristicValues')->with('status','Видалено');
        }
        //-----------------------------------------------------------------


        //-----------------------------------------------------------------
        if( isset($input['search']) && $input['search']!=null ){
            if(view()->exists('admin.characteristics.list')){
				$search = $input['search'];
				$paginate = 25;
				

                // $items = CharacteristicValue::where(function($query) use ($search) {
                //                         $query->orWhere('name', 'LIKE', '%'.$search.'%')
                //                             ->orWhere('email', 'LIKE', '%'.$search.'%');
                //                         })
                //                         ->paginate($paginate);
                $items = CharacteristicValue::where(function($query) use ($search) {
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
				return 	view('admin.characteristics_values.list',$data);
			}
			abort(404);
        }
        //-----------------------------------------------------------------
        
        return redirect()->route('admin.characteristicValues');
    }


    public function view($id){
		if(view()->exists('admin.characteristics.edit')){
            $item = CharacteristicValue::where('id', '=', $id)->first();
            $item->setRelation('translates', $item->translates->keyBy('lang'));

            $parents = Characteristic::with('translates')->get();
            $parents->each(function ($parent) {
                $parent->setRelation('translates', $parent->translates->keyBy('lang'));
            });
            $data = [
					'title' => 'Редагувати',
					'item' => $item,
                    'parents' => $parents
				];
			return 	view('admin.characteristics_values.edit',$data);
		}
        abort(404);
    }

    public function add(){
		if(view()->exists('admin.characteristics_values.edit') ){
            $parents = Characteristic::with('translates')->get();
            $parents->each(function ($parent) {
                $parent->setRelation('translates', $parent->translates->keyBy('lang'));
            });
            
            $data = [
                'title' => 'Додати значення характеристики',
                'parents' => $parents
            ];
			return 	view('admin.characteristics_values.edit',$data);
		}
		abort(404);
	}

    public function list(Request $request){
		if(view()->exists('admin.characteristics_values.list')){
 
			$paginate = 25;

            $items = CharacteristicValue::with('translates')->paginate($paginate);
            // dd($items);
           
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
			return 	view('admin.characteristics_values.list',$data);
		}
		abort(404);
	}
}
