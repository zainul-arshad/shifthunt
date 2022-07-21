<?php


namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;
 

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
    public function sendOtp($id)
    {
       if($id != '')
       {
           $data=\App\Models\User::find($id);
              \Mail::send('front.emails.email-verification', ['data'=>$data], function($message)use($data){
                 $message->to($data->email, ' ')
                    ->subject
                    ('OTP For Email Verification');
                  
              });
       }
    }
    public function sendPasswordOtp($id)
    {
       if($id != '')
       {
           $data=\App\Models\User::find($id);
              \Mail::send('front.emails.forgot-password', ['data'=>$data], function($message)use($data){
                 $message->to($data->email, ' ')
                    ->subject
                    ('OTP For Reset Password');
                  
              });
       }
    }
}