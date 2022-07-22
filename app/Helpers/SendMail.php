<?php
namespace App\Helpers;
use Mail;
class SendMail
{
	public static function sendMail($to,$data)
	{
		if(!empty($to) && !empty($data) && $data['template'] == 'approve_status')
		{
			$template='front.emails.approve-status';
			$data['subject']='Job application  Status ';
			 
		}
		if(!empty($to) && !empty($data) && $data['template'] == 'shift_status')
		{
			$template='front.emails.shift-status';
			$data['subject']='Shift  Status ';
			 
		}
		 
		if( !empty($data) && $data['template'] == 'booked')
		{
			$template='front.emails.booked';
			$data['subject']='Booked';
			$user=\App\Models\User::find($data['booking']->customer_id);
			$data['user']=$user;
			$to=$user->email;
			$data['shift']=\App\Models\Slot::find($data['booking']->slot_id);
		}
		if( !empty($data) && $data['template'] == 'bookings')
		{
			$template='front.emails.bookings';
			$data['subject']='New shift booking';
			$data['shift']=\App\Models\Slot::find($data['booking']->slot_id);
			$user=\App\Models\User::find($data['shift']->client_id);
			$data['user']=$user;
			$to=$user->email;
			
		} 
		if( !empty($data) && $data['template'] == 'new_shift')
		{
			$template='front.emails.new_shift';
			$data['subject']='New shifts nearby you';
			$user=\App\Models\User::find($data['shift']->client_id);
			$client=\App\Models\Client::where('user_id',$data['shift']->client_id)->first();
			$data['user']=$user;
			if(!empty($client) && !empty($client->lat) && !empty($client->lng))
			{
			        $center_lat=$client->lat;
					$center_lng=$client->lng;
				    $customer=\App\Models\Customer::select('customer.*','users.email as user_email')
					->leftJoin('users','users.id','customer.user_id')
					->selectRaw("customer.*,( 6371 * acos( cos( radians(?) ) *
					cos( radians( customer.lat ) )
					* cos( radians( customer.lng ) - radians(?)
					) + sin( radians(?) ) *
					sin( radians( lat ) ) )
					) AS distance", [$center_lat, $center_lng, $center_lat])
					->having('distance', '<', '100000')
					->get();
					 
					foreach($customer as $c)
					{
						if(filter_var($c->user_email, FILTER_VALIDATE_EMAIL))
						{
							$to=$c->user_email;
							$data['customer']=$c;
							Mail::send($template, ['data'=>$data], function($message)use($to, $data) {
							$message->to($to, ' ')
							->subject($data['subject']);
							});
						}
						
					}

			}
			 
			 return true;
			
		} 
		Mail::send($template, ['data'=>$data], function($message)use($to, $data) {
         $message->to($to, ' ')
		->subject($data['subject']);
      	});
	}

} 


	 