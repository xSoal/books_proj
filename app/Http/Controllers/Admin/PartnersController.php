<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Partner;
use App\Models\PartnerTranslate;

use Illuminate\Http\Request;
use Validator;

class PartnersController extends Controller
{
    
    public function post(Partner $user, Request $request){

        $input = $request->except('_token');

        $languages = json_decode($input['languages']);

        $input['img'] = isset($input['img']) ? $input['img'] : '';
        
        //-----------------------------------------------------------------
        if( isset($input['save']) ||  isset($input['save_and_exit']) ){


            $user->fill($input);
            $user_save = $user->save();

            foreach ($languages as $lang) {
                $partner_translate = new PartnerTranslate;
                $partner_translate->partner_id = $user->id;
                $partner_translate->name = $input['name'][$lang];
                $partner_translate->lang = $lang;
                $partner_translate->save();
            }

            if( $user_save ){
                if( isset($input['save_and_exit']) ){
				    return redirect()->route('admin.partners')->with('status','Партнер доданий');
                }else{
                    return redirect()->route('admin.addPartner')->with('status','Партнер доданий');
                }
			}
			
		}

        if( isset($input['update']) || isset($input['update_and_exit']) ){

            $partner = Partner::find($input['id']);
            
            $partner->fill($input);
            $partner_update = $partner->update();

            $languages = json_decode($input['languages']);

            foreach ($languages as $lang) {
                $data = [
                    'name' => $input['name'][$lang]
                ];

                PartnerTranslate::updateOrCreate(
                    [
                        'partner_id' => $input['id'],
                        'lang' => $lang
                    ],
                    $data
                );
            }

			if( $partner_update ){
                if( isset($input['update_and_exit']) ){
				    return redirect()->route('admin.partners')->with('status','Оновлено');
                }else{
                    return redirect()->route('admin.viewPartner',['id' => $input['id'] ])->with('status','Оновлено');
                }
			}
        }
        //-----------------------------------------------------------------



        //-----------------------------------------------------------------
        if( isset($input['dell']) ){
            $tmp = Partner::where('id',$input['id'])->first();
            $tmp->delete();
            return redirect()->route('admin.partners')->with('status','Видалено');
        }
        //-----------------------------------------------------------------


        //-----------------------------------------------------------------
        if( isset($input['search']) && $input['search']!=null ){
            if(view()->exists('admin.partners.list')){
				$search = $input['search'];
				$paginate = 25;
				

                $items = Partner::where(function($query) use ($search) {
                                        $query->orWhere('name', 'LIKE', '%'.$search.'%')
                                            ->orWhere('email', 'LIKE', '%'.$search.'%');
                                        })
                                        ->paginate($paginate);
                
				
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
				return 	view('admin.partners.list',$data);
			}
			abort(404);
        }
        //-----------------------------------------------------------------
        
        return redirect()->route('admin.partners');
    }


    public function view($id){
		if(view()->exists('admin.partners.edit')){
            $item = Partner::where('id', '=', $id)->first();
            $item->setRelation('translates', $item->translates->keyBy('lang'));

            $data = [
					'title' => 'Редактировать пользователя',
					'item' => $item,
				];
			return 	view('admin.partners.edit',$data);
		}
        abort(404);
    }

    public function add(){
		if(view()->exists('admin.partners.edit') ){

            $data = [
                'title' => 'Добавить пользователя',
                ];
			return 	view('admin.partners.edit',$data);
		}
		abort(404);
	}

    public function list(Request $request){
		if(view()->exists('admin.partners.list')){

			$paginate = 25;

            $items = Partner::with('translates')->paginate($paginate);
            $items->getCollection()->transform(function ($partner) {
                $partner->setRelation('translates', $partner->translates->keyBy('lang'));
                return $partner;
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
			return 	view('admin.partners.list',$data);
		}
		abort(404);
	}

}
