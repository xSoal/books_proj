<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    
    protected $redirectTo = '/login';

    protected function registered(Request $request, $user)
    {
        $this->guard()->logout();

        return redirect($this->redirectPath())->with('status','Дякуємо за реєстрацію, як тільки менеджер перевірити ваші дані ваш обліковий запис буде активним');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {

        $phone = preg_replace("/[^0-9]/", '',$data['phone']);
        if( substr($phone,0,3) == '380' ){
            $phone = substr($phone,3);
        }
        if( substr($phone,0,1) == 0 && mb_strlen($phone) == 10 ){
            $phone = substr($phone,1);
        }
        if( $phone !='' && mb_strlen($phone)== 9 ){
            $data['phone'] = '+380'.$phone;
        }

        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255','unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {

        $phone = preg_replace("/[^0-9]/", '',$data['phone']);
        if( substr($phone,0,3) == '380' ){
            $phone = substr($phone,3);
        }
        if( substr($phone,0,1) == 0 && mb_strlen($phone) == 10 ){
            $phone = substr($phone,1);
        }
        if( $phone !='' && mb_strlen($phone)== 9 ){
            $data['phone'] = '+380'.$phone;
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
