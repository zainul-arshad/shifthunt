<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;
use Validator;
use \Auth;
use Hash;
use \App\Models\User;
use \App\Models\Customer;
class LoginController extends BaseController
{
   public function signup(Request $request)
   {
     try{
         $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users',
            'mobile' => 'required|unique:users',
            'password' => 'required',
        ]);
        
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());       
        }
        $request->merge(['user_type'=>'customer']);
        $request->merge(['password'=>bcrypt($request->password)]);
        $otp_mobile='112233';//rand(10000,99999);
        $otp_email='112233';//rand(10000,99999);
        $request->merge(['otp_mobile'=>$otp_mobile,'otp_email'=>$otp_email,'mobile_otp_created_at'=>date('Y-m-d H:i:s'),'email_otp_created_at'=>date('Y-m-d H:i:s')]);
        $data=User::create($request->all());
        if($data)
        {
          //create customer
          $customer=new Customer();
          $customer->full_name=$data->name;
          
          $customer->user_id=$data->id;
          
          $customer->created_by=$data->id;
          $customer->updated_by=$data->id;
          $customer->save();
        }
        if($data)
        {
          $this->sendOtp($data->id);  
          $success['user']=$data;    
          return $this->sendResponse($success, 'Registered Successfully.');  
        }
    }catch(Excpection $e)
    {
        return $this->sendError('', ['error'=>$e]);
    }  
   }
   public function login(Request $request)
   {
    try{
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $params=[];
        if(filter_var($request->email,FILTER_VALIDATE_EMAIL) != '')
        {
            $params=['email' => $request->email, 'password' => $request->password];
        }else{
            $params=['mobile' => $request->email, 'password' => $request->password];
        }
        if(Auth::attempt($params)){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('ShiftHunt')->accessToken; 
            $success['user'] =  $user;
   
            return $this->sendResponse($success, 'User login successfully.');
        }else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 

    }catch(Excpection $e)
    {
        return $this->sendError('', ['error'=>$e]);
    }
   }


   public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        $data=Customer::select('customer.*','users.email','users.mobile','users.profile_image','users.name','users.last_step','users.approve_status','users.approve_remarks','users.is_resubmit')
            ->leftJoin('users','users.id','customer.user_id')
            ->where('customer.user_id',$request->user()->id)
            ->first();
        $data->percentage=$data->last_step != '' ? ($data->last_step/11): 0;    
        $success['user']=$data;
        return $this->sendResponse($success, ' ');  
    }
    public function editUser(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email,'.$request->user()->id,
            'mobile' => 'required|unique:users,mobile,'.$request->user()->id,
        ]);
        
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());       
        }
        $user=User::find(\auth::user()->id);
        $user->name=$request->name;
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
        if($user->save()){
            $customer=Customer::where('user_id',$user->id)->first();
            $customer->full_name=$user->name;
            $customer->save();
        }
        return $this->sendResponse('Updated', ' ');  
    }
    public function resendOtp(Request $request)
    {
        if($request->type == 'email')
        {
           $user=User::where('email',$request->email)->first();
           if(strtotime($user->email_otp_created_at)+900 > time())
           {
              $this->sendOtp($user->id); 
           }else{
               $otp='112244';
               $user->otp_email=$otp;
               $user->otp_mobile=$otp;
               $user->email_otp_created_at=date('Y-m-d H:i:s');
               $user->save();
               $this->sendOtp($user->id);
           }
        }
        if($request->type == 'mobile')
        {
           $user=User::where('mobile',$request->mobile)->first();
           if(strtotime($user->mobile_otp_created_at)+900 > time())
           {
              $this->sendOtp($user->id); 
           }else{
               $otp='112244';
               $user->otp_mobile=$otp;
               $user->mobile_otp_created_at=date('Y-m-d H:i:s');
               $user->save();
               $this->sendOtp($user->id);
           }
        }
    }
    public function otpVerify(Request $request)
    {
          
        if($request->type == 'email')
        {
           $user=User::where('email',$request->email)->first();
           if(strtotime($user->email_otp_created_at)+900 > time())
           {
               if($user->otp_email == $request->otp){
                   $user->email_verified='yes';
                   $user->save();
                   return $this->sendResponse(['email_verified'=>true],'');
               }else{
                   return $this->sendError('Incorrect OTP');
               }
              
           }else{
               $otp='112244';
               $user->otp_email=$otp;
               $user->otp_mobile=$otp;
               $user->email_otp_created_at=date('Y-m-d H:i:s');
               $user->save();
               $this->sendOtp($user->id);
               return $this->sendError('OTP Expired');
           }
        }
        if($request->type == 'mobile')
        {
           $user=User::where('mobile',$request->mobile)->first();
           if(strtotime($user->mobile_otp_created_at)+900 > time())
           {
               if($user->otp_mobile == $request->otp){
                   $user->mobile_verified='yes';
                   $user->save();
                   return $this->sendResponse(['mobile_verified'=>true],'');
               }else{
                   return $this->sendError('Incorrect OTP');
               }
              
           }else{
               $otp='112244';
               $user->otp_mobile=$otp;
               $user->mobile_otp_created_at=date('Y-m-d H:i:s');
               $user->save();
               $this->sendOtp($user->id);
               return $this->sendError('OTP Expired');
           }
        }
    }
    public function forgotPassword(Request $request)
    {
        if(filter_var($request->email,FILTER_VALIDATE_EMAIL) != '')
        {
           $user=User::where('email',$request->email)->first(); 
        }else{
           $user=User::where('mobile',$request->email)->first(); 
        }
        
        if(!isset($user))
        {
         return $this->sendResponse('','Invalid email address');   
        }
       if(strtotime($user->otp_created_at)+900 > time())
       {
          $this->sendPasswordOtp($user->id);
          return $this->sendResponse('','');
       }else{
           $otp='112200';
           $user->otp=$otp;
           $user->otp_created_at=date('Y-m-d H:i:s');
           $user->save();
           $this->sendPasswordOtp($user->id);
           return $this->sendResponse('','');
       }
        
    }
    public function forgotPasswordOtpVerify(Request $request)
    {
      if(filter_var($request->email,FILTER_VALIDATE_EMAIL) != '')
        {
           $user=User::where('email',$request->email)->first(); 
        }else{
           $user=User::where('mobile',$request->email)->first(); 
        }
        if(strtotime($user->otp_created_at)+900 > time())
        {
            if($user->otp == $request->otp)
            {
               return $this->sendResponse('',''); 
            }else{
                return $this->sendError('');
            }
        }else{
            
           $otp='112200';
           $user->otp=$otp;
           $user->otp_created_at=date('Y-m-d H:i:s');
           $user->save();
           $this->sendPasswordOtp($user->id);
           return $this->sendError(''); 
        }
    }
    public function createNewPassword(Request $request)
    {
        if(filter_var($request->email,FILTER_VALIDATE_EMAIL) != '')
        {
           $user=User::where('email',$request->email)->first(); 
        }else{
           $user=User::where('mobile',$request->email)->first(); 
        }
        $user->password=bcrypt($request->password);
        $user->save();
        return $this->sendResponse('','');
    }
    public function changePassword(Request $request)
    {
        
        $user=User::find(\Auth::user()->id);
        if(Hash::check($request->old_password,$user->password))
        {
            $user->password=bcrypt($request->password);//password_hash($request->password, PASSWORD_BCRYPT);
            $user->save();
            return $this->sendResponse('','Upated');
        }else{
          return $this->sendError('Current Password is Incorrect');   
        }
    }
    
}
