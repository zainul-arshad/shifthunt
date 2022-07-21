<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;
use App\Models\Client;
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
    //protected $redirectTo = RouteServiceProvider::HOME;
     protected function redirectTo()
    {
        if (auth()->user()->user_type == 'carer') {
            return '/dashboard';
        }
        if (auth()->user()->user_type == 'admin') {
            return '/dashboard';
        }
        if (auth()->user()->user_type == 'carer_home') {
            return '/dashboard';
        }
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
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'mobile' => ['required','max:15', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
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
       
        $userdata= User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'user_type' =>$data['user_type'],
            'password' => bcrypt($data['password']),
        ]);
        if($data['user_type'] == 'carer')
        {


        Customer::create([
                'full_name'=> $data['name'],
                 
                'user_id'=>$userdata->id,
                'created_by'=>$userdata->id,
                'updated_by'=>$userdata->id
            ]);
        }else{
        Client::create([
                'full_name'=> $data['name'],
                 
                'user_id'=>$userdata->id,
                'created_by'=>$userdata->id,
                'updated_by'=>$userdata->id
            ]);    
        }
         return $userdata;
         
        
    }
}
