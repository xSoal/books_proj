<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;
use Validator;

class SuperUsersController extends Controller
{
    
    public function post(User $user, Request $request){


        $input = $request->except('_token');

        $input['img'] = isset($input['img']) ? $input['img'] : '';

        if( isset($input['phone']) && trim($input['phone']) !='' ){

            $phone = preg_replace("/[^0-9]/", '',$input['phone']);
            if( substr($phone,0,3) == '380' ){
                $phone = substr($phone,3);
            }
            if( substr($phone,0,1) == 0 && mb_strlen($phone) == 10 ){
                $phone = substr($phone,1);
            }
            if( $phone !='' && mb_strlen($phone)== 9 ){
                $input['phone'] = '+380'.$phone;
            }
        }
        
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
				return redirect()->route('admin.addSuperUser')->withErrors($validator)->withInput();
			}

			$input['password'] = Hash::make($input['password']);
            $user->role = 3;

            $user->fill($input);

            if( $user->save() ){
                if( isset($input['save_and_exit']) ){
				    return redirect()->route('admin.super_users')->with('status','Пользователь добавлен');
                }else{
                    return redirect()->route('admin.addSuperUser')->with('status','Пользователь добавлен');
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
				return redirect()->route('admin.viewSuperUser', ['id' => $input['id'] ] )->withErrors($validator)->withInput();
			}

            $user = User::find($input['id']);
            
            $user->fill($input);
			
			if( $user->update() ){
                if( isset($input['update_and_exit']) ){
				    return redirect()->route('admin.super_users')->with('status','Пользователь обновлен');
                }else{
                    return redirect()->route('admin.viewSuperUser',['id' => $input['id'] ])->with('status','Пользователь обновлен');
                }
			}
        }
        //-----------------------------------------------------------------



        //-----------------------------------------------------------------
        if( isset($input['dell']) ){
            $tmp = User::where('id',$input['id'])->first();
            $tmp->delete();
            return redirect()->route('admin.super_users')->with('status','Пользователь удалён');
        }
        //-----------------------------------------------------------------


        //-----------------------------------------------------------------
        if( isset($input['search']) && $input['search']!=null ){
            if(view()->exists('admin.super_users.list')){
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
				return 	view('admin.super_users.list',$data);
			}
			abort(404);
        }
        //-----------------------------------------------------------------
        
        return redirect()->route('admin.super_users');
    }


    public function view($id){
        if(Auth::user()->role !== 3) abort(403);

		if(view()->exists('admin.super_users.edit')){
            
            $post = array();
            $postP = array();
            $tmp = Post::get();
            foreach($tmp as $item){
                $post[$item->id] = $item;
                if( $item->parrent_id == 0 ){
                    $postP[] = $item;
                }
            }


            $item = User::where('id', '=', $id)->first();

            $postList = array();
            if($item->post_id !=0){
                $tmp = Post::where('id',$item->post_id)->first();
                $postList[] = $tmp;
                while( isset($tmp) && $tmp->parrent_id !=0 ){
                    $tmp = Post::where('id',$tmp->parrent_id)->first();
                    array_unshift($postList, $tmp);
                }
            }
            
            $data = [
					'title' => 'Редактировать пользователя',
					'item' => $item,
                    'post' => $post,
                    'postP' => $postP,
                    'postList' => $postList
				];
			return 	view('admin.super_users.edit',$data);
		}
        abort(404);
    }

    public function add(){
		if(view()->exists('admin.super_users.edit') ){

            $postList = array();
            $post = array();
            $postP = array();
            $tmp = Post::get();
            foreach($tmp as $item){
                $post[$item->id] = $item;
                if( $item->parrent_id == 0 ){
                    $postP[] = $item;
                }
            }

           $data = [
                'title' => 'Добавить пользователя',
                'post' => $post,
                'postP' => $postP,
                'postList' => $postList
                ];
			return 	view('admin.super_users.edit',$data);
		}
		abort(404);
	}

    public function list(Request $request){

        if(Auth::user()->role !== 3) abort(403);

		if(view()->exists('admin.super_users.list')){

			$paginate = 25;

            $items = User::where('role', '3')->paginate($paginate);
            
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
			return 	view('admin.super_users.list',$data);
		}
		abort(404);
	}


}
