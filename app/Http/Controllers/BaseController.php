<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;
use Mail;
class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 200)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }
    public function sendOtp($email,$mobile,$otpEmail,$otpMobile)
    {
        $user=\App\Models\User::where('email',$email)->first();
    	$data =['email'=>$email,'mobile'=>$mobile,'otpEmail'=>$otpEmail,'otpMobile'=>$otpMobile,'name'=>$user->name];
    	if($email != null)
    	{
            Mail::send('front.emails.email-verification', ['data'=>$data], function($message)use($data) {
                 $message->to($data['email'], '')
                 ->subject('Verify your Email - ShiftHunter');
            }); 
    	}
        return true;
    }
    public function sendForgotPasswordOtp($email,$mobile,$otp)
    {
        $user=\App\Models\User::where('email',$email)->first();
    	$data =['email'=>$email,'mobile'=>$mobile,'otp'=>$otp,'name'=>$user->name];
    	if($email != null)
    	{
            Mail::send('front.emails.forgot-password', ['data'=>$data], function($message)use($data) {
                 $message->to($data['email'], '')
                 ->subject('OTP for resetting Password - ShiftHunter');
            }); 
    	}
        return true;
    }
}