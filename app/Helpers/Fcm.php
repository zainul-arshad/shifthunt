<?php
namespace App\Helpers;
use App\Models\DeviceTokens;
use App\Models\Client;
use App\Models\Positions;
use App\Models\Slot;
class Fcm
{
	public function send($data)
	{
		$client=Client::select('client.*','users.profile_image')
		->leftJoin('users','users.id','client.user_id')
		->where('client.user_id',$data->client_id)->first();
		$radius=\App\Models\Settings::getSettings('max_location_radius');
		$radius=$radius != '' ? $radius : 1000;
		$center_lat=$client->lat;
		$center_lng=$client->lng;
		$DeviceTokens=DeviceTokens::select('device_tokens.*')
		->selectRaw("device_tokens.*,
             ( 6371 * acos( cos( radians(?) ) *
               cos( radians( device_tokens.lat ) )
               * cos( radians( device_tokens.lng ) - radians(?)
               ) + sin( radians(?) ) *
               sin( radians( lat ) ) )
             ) AS distance", [$center_lat, $center_lng, $center_lat])
			->having("distance", "<",$radius)
			->get()->pluck('fcm_token');
		$url = 'https://fcm.googleapis.com/fcm/send';
		$FcmToken =$DeviceTokens;
		$title='New shift found at '.$client->city.'🏙';
		$body=Positions::getName($data->position_id).' - posted by '.$client->full_name;
		$image=$client->profile_image != '' ? asset('public/uploads/client/'.$client->profile_image) : 'https://ui-avatars.com/api/?background=random&name='.strtolower(trim($client->full_name));
		$data = [
			"registration_ids" => $FcmToken,
			"notification" => [
				"title" => $title,
				"body" => $body,
				"image" => $image,
			],
			"data"=>[
					"route"=>'shift',
					"shift_id"=>$data->id
				]
		];
		$encodedData = json_encode($data);
		$fields = $encodedData;
		$headers = [
			'Authorization:key='.env('FCM_KEY'),
			'Content-Type: application/json',
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		$result = curl_exec($ch);
		var_dump($result);
		curl_close($ch);

	}
	public function sendBookingStatus($data)
	{
	    $slot=Slot::find($data->slot_id);
	    $client=Client::select('client.*','users.profile_image')
		->leftJoin('users','users.id','client.user_id')
		->where('client.user_id',$slot->client_id)->first();
	    
		$DeviceTokens=DeviceTokens::select('device_tokens.*')->where('user_id',$data->customer_id)->get()->pluck('fcm_token');
		$url = 'https://fcm.googleapis.com/fcm/send';
		$FcmToken =$DeviceTokens;
		$title='Booking status ';
		$body=' Booking# '.$data->booking_no.' ('.Positions::getName($slot->position_id).') is '.($data->is_approved == 'yes' ? ' approved'  : '').($data->is_cancelled == 'yes' ? ' rejected' : '');
		$image=$client->profile_image != '' ? asset('public/uploads/client/'.$client->profile_image) : 'https://ui-avatars.com/api/?background=random&name='.strtolower(trim($client->full_name));
		$data = [
			"registration_ids" => $FcmToken,
			"notification" => [
				"title" => $title,
				"body" => $body,
				"image" => $image,
			],
			"data"=>[
					"route"=>'booking',
					
				]
		];
		$encodedData = json_encode($data);
		$fields = $encodedData;
		$headers = [
			'Authorization:key='.env('FCM_KEY'),
			'Content-Type: application/json',
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		$result = curl_exec($ch);
		var_dump($result);
		curl_close($ch);
	}
}