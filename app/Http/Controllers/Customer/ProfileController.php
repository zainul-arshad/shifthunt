<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Customer;
use App\Models\User;
use Validator;
class ProfileController extends Controller
{
    public function index()
    {
        $data=User::select('users.*',
            'customer.full_name','customer.dob','customer.address','customer.city','customer.street','customer.state','customer.zip','customer.country','customer.alt_mobile')
        ->leftJoin('customer','customer.user_id','users.id')
        ->where('users.id',\Auth::user()->id)
        ->first();
         
        return view('customer.profile.index',[
            'data'=>$data
        ]);
    }
    public function editProfile()
    {
        $data=User::select('users.*',
            'customer.full_name','customer.dob','customer.address','customer.city','customer.street','customer.state','customer.zip','customer.country','customer.alt_mobile')
        ->leftJoin('customer','customer.user_id','users.id')
        ->where('users.id',\Auth::user()->id)
        ->first();
        return view('customer.profile.update',[
            'data'=>$data
        ]);
    }
    public function updateProfile(Request $request)
    {
        $validator =Validator::make($request->all(),[
            'email'=>'required|unique:users,email,'.\Auth::user()->id,
            'mobile'=>'required|unique:users,mobile,'.\Auth::user()->id
        ]);
        if($validator->fails())
        {
            return response()->json(['error'=>$validator->messages()],500);exit;
        }
        $user=User::find(\Auth::user()->id);
        $customer=Customer::where('user_id',\Auth::user()->id)->first();

        $user->name=$request->full_name;
        $user->email=$request->email;
        $user->mobile=$request->mobile;
        if($request->hasFile('profile_image'))
        {
            $file=$request->file('profile_image');
            $name=time().$file->getClientOriginalName();
            $path=public_path('uploads/customer');
            $file->move($path,$name);
            $user->profile_image=$name;
        }
        if($user->save())
        {
            $customer->full_name=$request->full_name;
            $customer->address=$request->address;
            $customer->dob=$request->dob;
            $customer->street=$request->street;
            $customer->city=$request->city;
            $customer->state=$request->state;
            $customer->zip=$request->zip;
            $customer->bio=$request->bio;
            $customer->year_of_experience=$request->year_of_experience;
            $customer->alt_mobile=$request->alt_mobile;
            //$customer->lat=$request->lat;
            //$customer->lng=$request->lng;
            $customer->save();
            return response()->json(['error'=>false]);exit; 
        } 

        
    }
    public function deletePhoto(Request $request)
    {
        if($request->has('id') && $request->id != '')
        {
          $user=User::find($request->id);
        }else{
          $user=User::find(\Auth::user()->id);  
        }
        $path=public_path('uploads/customer/'.$user->profile_image);
        $user->profile_image=null;
        if($user->save())
        {
            unlink($path);
        }
        echo 1;
    }
    public function slots()
    {
        return view('customer.slot.index');
    }
}
