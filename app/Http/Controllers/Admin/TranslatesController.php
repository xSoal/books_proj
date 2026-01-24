<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Translate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TranslatesController extends Controller
{
    public function post(Translate $item, Request $request){

        $input = $request->except('_token');

        //-----------------------------------------------------------------
        if( isset($input['save']) ||  isset($input['save_and_exit']) ){

            $item->fill($input);

            if( $item->save() ){
                if( isset($input['save_and_exit']) ){
				    return redirect()->route('admin.translates')->with('status','Додано');
                } else{
                    return redirect()->route('admin.addTranslate')->with('status','Додано');
                }
			}
			
		}

        if( isset($input['update']) || isset($input['update_and_exit']) ){

            $item = Translate::find($input['id']);
            
            $item->fill($input);

			if( $item->update() ){
                if( isset($input['update_and_exit']) ){
				    return redirect()->route('admin.translates')->with('status','Оновлено');
                }else{
                    return redirect()->route('admin.viewTranslate',['id' => $input['id'] ])->with('status','Оновлено');
                }
			}
        }
        //-----------------------------------------------------------------



        //-----------------------------------------------------------------
        if( isset($input['dell']) ){
            $tmp = Translate::where('id',$input['id'])->first();
            $tmp->delete();
            return redirect()->route('admin.translates')->with('status','Видалено');
        }
        //-----------------------------------------------------------------


        //-----------------------------------------------------------------
        if( isset($input['search']) && $input['search']!=null ){
            if(view()->exists('admin.translates.list')){
				$search = $input['search'];
				$paginate = 25;

                $items = Translate::where(function($query) use ($search) {
                                        $query->orWhere('ua', 'LIKE', '%'.$search.'%')
                                            ->orWhere('en', 'LIKE', '%'.$search.'%')
                                            ->orWhere('slug', 'LIKE', '%'.$search.'%')
                                            ;
                                        })
                                        ->paginate($paginate);
                
				
                if( $request['page']==null ){
					$request['page'] = 1;
				}
				$page = $paginate * ($request['page']-1);
                
                $data = [
                        'title' => 'Переклади',
                        'items' => $items,
                        'search' => $search,
						'page' => $page
                    ];
				return 	view('admin.translates.list',$data);
			}
			abort(404);
        }
        //-----------------------------------------------------------------
        
        return redirect()->route('admin.translates');
    }


    public function view($id){
		if(view()->exists('admin.translates.edit')){
            $item = Translate::where('id', '=', $id)->first();

            $data = [
					'title' => 'Редагувати переклад',
					'item' => $item,
				];
			return 	view('admin.translates.edit',$data);
		}
        abort(404);
    }

    public function add(){
		if(view()->exists('admin.translates.edit') ){

            $data = [
                'title' => 'Додати переклад',
                ];
			return 	view('admin.translates.edit',$data);
		}
		abort(404);
	}

    public function list(Request $request){
		if(view()->exists('admin.translates.list')){

			$paginate = 25;

            // $items = DB::table('translates')->paginate($paginate);
            $items = Translate::paginate();

			if( $request['page']==null ){
				$request['page'] = 1;
			}

			$page = $paginate * ($request['page']-1);

			$data = [
					'title' => 'Переклади',
					'items' => $items,
					'search' => '',
					'page' => $page
				];
			return 	view('admin.translates.list',$data);
		}
		abort(404);
	}
}
