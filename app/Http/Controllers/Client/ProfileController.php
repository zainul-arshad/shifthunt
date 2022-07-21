<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use Validator;
class ProfileController extends Controller
{
    public function index()
    {
        $data=User::select('users.*',
            'client.full_name','client.dob','client.address','client.city','client.street','client.state','client.zip','client.country','client.alt_mobile')
        ->leftJoin('client','client.user_id','users.id')
        ->where('users.id',\Auth::user()->id)
        ->first();
         
        return view('client.profile.index',[
            'data'=>$data
        ]);
    }
    public function editProfile()
    {
        $data=User::select('users.*',
            'client.full_name','client.dob','client.address','client.city','client.street','client.state','client.zip','client.country','client.alt_mobile','client.lat','client.lng')
        ->leftJoin('client','client.user_id','users.id')
        ->where('users.id',\Auth::user()->id)
        ->first();
        return view('client.profile.update',[
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
        $client=Client::where('user_id',\Auth::user()->id)->first();

        $user->name=$request->full_name;
        $user->email=$request->email;
        $user->mobile=$request->mobile;
        if($request->hasFile('profile_image'))
        {
            $file=$request->file('profile_image');
            $name=time().$file->getClientOriginalName();
            $path=public_path('uploads/client');
            $file->move($path,$name);
            $user->profile_image=$name;
        }
        if($user->save())
        {
            $client->full_name=$request->full_name;
            $client->address=$request->address;
            $client->dob=$request->dob;
            $client->street=$request->street;
            $client->city=$request->city;
            $client->state=$request->state;
            $client->zip=$request->zip;
            $client->alt_mobile=$request->alt_mobile;
            $client->lat=$request->lat;
            $client->lng=$request->lng;
            $client->save();
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
        
        $path=public_path('uploads/client/'.$user->profile_image);
        $user->profile_image=null;
        if($user->save())
        {
            unlink($path);
        }
        echo 1;
    }
}
