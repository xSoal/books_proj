<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Post;

use Validator;

class UsersController extends Controller
{
    
    public function post(User $user, Request $request){
       
        $input = $request->except('_token');

        $input['img'] = isset($input['img']) ? $input['img'] : '';
        
        //-----------------------------------------------------------------
        if( isset($input['save']) ||  isset($input['save_and_exit']) ){

			$messages = [
				'required' => 'Поле required: обязательно к заполению',
				'email.unique' => 'Поле E-MAIL должно быть уникальным',
				'min' => 'Пароль должен иметь не меньше 8 символов'
			];
			
			$validator = Validator::make($input, [
					'name' => 'required|string',
					'email' => 'required|unique:users',
					'password' => 'required|min:8|confirmed'
				],$messages);
            
			if ($validator->fails()) {
				return redirect()->route('admin.addUser')->withErrors($validator)->withInput();
			}

			$input['password'] = Hash::make($input['password']);

            $user->fill($input);

            if( $user->save() ){
                if( isset($input['save_and_exit']) ){
				    return redirect()->route('admin.users')->with('status','Користувач доданий');
                }else{
                    return redirect()->route('admin.addUser')->with('status','Користувач доданий');
                }
			}
			
		}
        //-----------------------------------------------------------------



        //-----------------------------------------------------------------
        if( isset($input['update']) || isset($input['update_and_exit']) ){
            
            $messages = [
				'required' => 'Поле required: обязательно к заполению',
				'email.unique' => 'Поле E-MAIL должно быть уникальным',
				'min' => 'Пароль должен иметь не меньше 8 символов',
                'confirmed' => 'Пароли не совпадают'
			];

            if( isset($input['password']) ){

                $validator = Validator::make($input, [
					'name' => 'required|string',
					'email' => 'required|unique:users,email,'.$input['id'],
					'password' => 'required|min:8|confirmed'
				],$messages);

                $input['password'] = Hash::make($input['password']);

            }else{
                unset($input['password']);
                unset($input['password_confirmation']);
                $validator = Validator::make($input, [
					'name' => 'required|string',
					'email' => 'required|unique:users,email,'.$input['id']
				],$messages);
            }

            if ($validator->fails()) {
				return redirect()->route('admin.viewUser', ['id' => $input['id'] ] )->withErrors($validator)->withInput();
			}

            $user = User::find($input['id']);
            
            $user->fill($input);
			
			if( $user->update() ){
                if( isset($input['update_and_exit']) ){
				    return redirect()->route('admin.users')->with('status','Пользователь обновлен');
                }else{
                    return redirect()->route('admin.viewUser',['id' => $input['id'] ])->with('status','Пользователь обновлен');
                }
			}
        }
        //-----------------------------------------------------------------



        //-----------------------------------------------------------------
        if( isset($input['dell']) ){
            $tmp = User::where('id',$input['id'])->first();
            $tmp->delete();
            return redirect()->route('admin.users')->with('status','Пользователь удалён');
        }
        //-----------------------------------------------------------------


        //-----------------------------------------------------------------
        if( isset($input['search']) && $input['search']!=null ){
            if(view()->exists('admin.users.list')){
				$search = $input['search'];
				$paginate = 25;
				

                $items = User::where(function($query) use ($search) {
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
				return 	view('admin.users.list',$data);
			}
			abort(404);
        }
        //-----------------------------------------------------------------
        
        return redirect()->route('admin.users');
    }


    public function view($id){
		if(view()->exists('admin.users.edit')){
            $item = User::where('id', '=', $id)->first();
            
            $data = [
					'title' => 'Редактировать пользователя',
					'item' => $item,
				];
			return 	view('admin.users.edit',$data);
		}
        abort(404);
    }

    public function add(){
		if(view()->exists('admin.users.edit') ){

            $data = [
                'title' => 'Добавить пользователя',
                ];
			return 	view('admin.users.edit',$data);
		}
		abort(404);
	}

    public function list(Request $request){
		if(view()->exists('admin.users.list')){

			$paginate = 25;

            $items = User::where('role', '0')->paginate($paginate);
            
			if( $request['page']==null ){
				$request['page'] = 1;
			}
			$page = $paginate * ($request['page']-1);

			$data = [
					'title' => 'Пользователи',
					'items' => $items,
					'search' => '',
					'page' => $page
				];
			return 	view('admin.users.list',$data);
		}
		abort(404);
	}


}
