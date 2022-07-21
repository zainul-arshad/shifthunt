<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;
use Validator;
use \Auth;
use \App\Models\User;
use \App\Models\Customer;
use \App\Models\Client;
use \App\Models\Slot;
use \App\Models\Booking;
use \App\Models\Requirements;
use \App\Models\Settings;
use \App\Models\Banner;
use \App\Models\DeviceTokens;
class ApiController extends BaseController
{
   public function getSlots(Request $request)
   {
     \Log::info($request->all());   
     try{
       $dataArray=[];
       $center_lat=$request->has('lat') ? $request->lat : '';
       $center_lng=$request->has('lng') ? $request->lng : '';
       $radius=$request->radius;
       
       $data=Slot::select('slot.id as slot_id','slot.slot_number','slot.start_date','slot.start_time','slot.end_date','slot.end_time','slot.client_id','slot.price','slot.price_type',
       'positions.position_title','positions.position_desc','positions.icon',
       'client.full_name as client_name','client.address','client.city','client.lat','client.lng','client.zip','client.street','client.dob','client.alt_mobile','client.state')
       ->selectRaw("client.*,
             ( 6371 * acos( cos( radians(?) ) *
               cos( radians( client.lat ) )
               * cos( radians( client.lng ) - radians(?)
               ) + sin( radians(?) ) *
               sin( radians( lat ) ) )
             ) AS distance", [$center_lat, $center_lng, $center_lat])
       ->leftJoin('positions','positions.id','slot.position_id')
       ->leftJoin('client','client.user_id','slot.client_id')
       ->where('slot.is_active','yes')
       ->where('slot.is_approve','yes')
       ->where('slot.is_booked','no');
       if($request->has('radius') && $request->radius != '')
       {
           $data=$data->having("distance", "<",$radius);
       }
       if($request->has('date') && $request->date != '')
       {
           $data=$data->whereDate('slot.start_date',$request->date);
       }else{
           $data=$data->whereDate('slot.start_date','>=',date('Y-m-d'));
       }
       if($request->has('client_id') && $request->client_id != '[]' )
       {
             $client_id=explode(',',trim($request->client_id,'[]'));
             if(count($client_id) != 0)
             {
                $data=$data->whereIn('slot.client_id',$client_id); 
             }
       }
       if($request->has('location') && $request->location != '')
       {
           $data=$data->where(function($q)use($request){
               $q->where('client.city',$request->location)
               ->orWhere('client.street',$request->location)
               ->orWhere('client.state',$request->location);
           });
       }
       if($request->has('lat') && $request->lat != '' && $request->has('lng') && $request->lng != '')
       {
           $data=$data->orderBy('distance','desc');
       }
       $data=$data->get();
        
        return $this->sendResponse($data,'');
    }catch(Excpection $e){
   		return $this->sendError('', ['error'=>$e]);
   	}  
   }
   public function getSlot($id)
   {
     try{
       $dataArray=[];
       $data=Slot::select('slot.id as slot_id','slot.slot_number','slot.start_date','slot.start_time','slot.end_date','slot.end_time','slot.client_id','slot.price','slot.price_type','slot.position_id',
       'positions.position_title','positions.position_desc','positions.icon',
       'client.full_name as client_name','client.address','client.city','client.lat','client.lng','client.zip','client.street','client.dob','client.alt_mobile','client.state','users.profile_image as client_image')
       ->leftJoin('positions','positions.id','slot.position_id')
       ->leftJoin('client','client.user_id','slot.client_id')
       ->leftJoin('users','users.id','slot.client_id')
       ->where('slot.id',$id)
       //->where('slot.is_active','yes')
       //->where('slot.is_booked','no')
       ->first();
       
        $data->start_time=date('h:i A',strtotime($data->start_time));
        $data->end_time=date('h:i A',strtotime($data->end_time));
        $requirements=Requirements::select('name')->where('position_id',$data->position_id)->get();
        $data->requirments=$requirements;
        $booked_ids=Booking::select('customer_id')->where('slot_id',$id)->get();
        $booked_ids_array=[];
        foreach($booked_ids as $ids)
        {
            $booked_ids_array[]=$ids->customer_id;
        }
        $data->booked_ids=$booked_ids_array;
        return $this->sendResponse($data,'');
    }catch(Excpection $e){
   		return $this->sendError('', ['error'=>$e]);
   	}  
   }
   public function book(Request $request)
   {
       
     try{
         $book=Booking::where('customer_id',\Auth::user()->id)->where('slot_id',$request->slot_id)->count();
         if($book == 0)
         {
             $data=new Booking();
             $data->customer_id=\Auth::user()->id;
             $data->slot_id=$request->slot_id;
             $data->created_at=date('Y-m-d H:i:s');
             $data->updated_at=date('Y-m-d H:i:s');
             $data->created_by=\Auth::user()->id;
             $data->updated_by=\Auth::user()->id;
             $data->is_approved='no';
             if($data->save())
             {
                 return $this->sendResponse(['booking_no'=>$data->booking_no],'Booked');
             }
             
         }
         return $this->sendResponse('','Allready Booked');
     }catch(Excpection $e){
        return $this->sendError('', ['error'=>$e]); 
     }
   }
   public function getClients(Request $request)
   {
      try{
         $data=Client::select('client.*','users.name','users.email','users.id as client_id','users.profile_image')
         ->leftJoin('users','users.id','client.user_id')
         ->get();
         return $this->sendResponse($data,'');
     }catch(Excpection $e){
        return $this->sendError('', ['error'=>$e]); 
     } 
   }
   public function getClient(Request $request)
   {
      try{
         $id=$request->id;
		 $location=Client::whereNotNull('lat')->whereNotNull('lng')->where('city',$request->location)->orWhere('street',$request->location)->orWhere('state',$request->location)->first();
		 
		 $center_lat=isset($location) ? $location->lat:null;
		 $center_lng=isset($location) ? $location->lng:null;
         $data=Client::select('client.full_name as client_name','client.address','client.city','client.lat','client.lng','client.zip','client.street','client.dob','client.alt_mobile','client.state','users.profile_image as client_image',
         'users.name','users.email','users.id as client_id','users.profile_image');
         //if(isset($location) && $location->lat != '' && $location->lng != '')
         //{
             $data=$data->selectRaw("client.*,
             ( 6371 * acos( cos( radians(?) ) *
               cos( radians( client.lat ) )
               * cos( radians( client.lng ) - radians(?)
               ) + sin( radians(?) ) *
               sin( radians( lat ) ) )
             ) AS distance", [$center_lat, $center_lng, $center_lat]);
         //}
             
         $data=$data->leftJoin('users','users.id','client.user_id')
         ->where('users.id',$id)
         ->first();
          $slots=Slot::select('slot.id as slot_id','slot.slot_number','slot.start_date','slot.start_time','slot.end_date','slot.end_time','slot.client_id','slot.price','slot.price_type',
           'positions.position_title','positions.position_desc','positions.icon',
           'client.full_name as client_name','client.address','client.city','client.lat','client.lng','client.zip','client.street','client.dob','client.alt_mobile','client.state')
           ->leftJoin('positions','positions.id','slot.position_id')
           ->leftJoin('client','client.user_id','slot.client_id')
           ->where('slot.is_active','yes')
           ->where('slot.client_id',$id)
           ->where('slot.is_booked','no')
           ->whereDate('slot.start_date','>=',date('Y-m-d'));
            $slots=$slots->get();
           return $this->sendResponse(['client'=>$data,'slots'=>$slots],'');
     }catch(Excpection $e){
        return $this->sendError('', ['error'=>$e]); 
     } 
   }
   public function getBookedSlots(Request $request)
   {
       \Log::info($request->all());
      try{
         
        $data=Booking::select('booking.*',
        'slot.id as slot_id','slot.slot_number','slot.start_date','slot.start_time','slot.end_date','slot.end_time','slot.client_id','slot.price','slot.price_type',
        'positions.position_title','positions.position_desc','positions.icon',
        'client.full_name as client_name','client.address','client.city','client.lat','client.lng','client.zip','client.street','client.dob','client.alt_mobile','client.state')
        ->leftJoin('slot','slot.id','booking.slot_id')
        ->leftJoin('positions','positions.id','slot.position_id')
        ->leftJoin('client','client.user_id','slot.client_id')
        ->where('booking.customer_id',\Auth::user()->id);
        if($request->has('type') && $request->type == 'approved')
        {
           $data=$data->where('booking.is_approved','yes'); 
        }
        if($request->has('type') && $request->type == 'pending')
        {
           $data=$data->where('booking.is_approved','no')->where('booking.is_cancelled','no'); 
        }
        if($request->has('from_date') && $request->from_date != '')
        {
           $data=$data->whereDate('slot.start_date','>=',$request->from_date);
        }
        if($request->has('to_date') && $request->to_date != '')
        {
           $data=$data->whereDate('slot.start_date','<=',$request->to_date);
        }
        if($request->has('client_id') && $request->client_id != '[]')
        {
           $client_id=explode(',',trim($request->client_id,'[]'));
             if(count($client_id) != 0)
             {
                $data=$data->whereIn('slot.client_id',$client_id); 
             }
        }
        $data=$data->orderBy('booking.created_at','desc')
        ->get();
            return $this->sendResponse($data,'');
     }catch(Excpection $e){
        return $this->sendError('', ['error'=>$e]); 
     } 
   }
   public function getLocations(Request $request)
   {
      try{
          $data=Client::select('city')->distinct('city')->get();
          $array1=new \stdClass();
          $array=[];
          foreach($data as $item)
          {
               if($item->city != null || $item->city != ''){
                 $array[]=$item->city;
                }
             // $array[]=$item->street;
             // $array[]=$item->state;
          }
          //$array=array_unique($array);
          $array1->locations=$array;
          return $this->sendResponse($array1,'');  
        }catch(Excpection $e){
            return $this->sendError('', ['error'=>$e]); 
        } 
   }
   public function getSettings()
   {
       $data=Settings::get();
       $array=[];
       foreach($data as $item)
       {
          $array[$item->label]=$item->value; 
       }
       return $this->sendResponse($array,'');
   }
   public function getBanners()
   {
       $data=Banner::where('status','yes')->get();
       $array=[];
       foreach($data as $item)
       {
          $array[]=asset('public/images/banner').'/'.$item->banner; 
       }
       return $this->sendResponse($array,'');
   }
   public function getCurrentLocation(Request $request)
   {
       //echo json_encode($request->all());
        try{

           $data=\Http::get('https://nominatim.openstreetmap.org/reverse?format=json&lat='.$request->lat.'&lon='.$request->lng); 
           $data=$data->json();
           $location='Unknown Location';
           if(isset($data['address']) && isset($data['address']['city']))
           {
               $location=$data['address']['city'];
           }
           if(isset($data['address']) && isset($data['address']['town']))
           {
               $location=$data['address']['town'];
           }
           
           return $this->sendResponse(['location'=>$location],''); 
        }catch(Expection $e){
            
            return $this->sendError('', ['error'=>$e]); 
        }
        
   }
   public function sendDeviceInfo(Request $request)
   {
      try{
            $data=DeviceTokens::where('device_id',$request->device_id)->first();
            if(!isset($data))
            {
              $data=new DeviceTokens();  
            }
            $data->device_id=$request->device_id;
            $data->user_id=$request->user_id;
            $data->fcm_token=$request->fcm_token;
            $data->lat=$request->lat;
            $data->lng=$request->lng;
            if($request->location != '')
            {
               $client=Client::whereNotNull('lat')->whereNotNull('lng')->where('city',$request->location)->orWhere('street',$request->location)->orWhere('state',$request->location)->first();
               $data->lat=$client->lat;
               $data->lng=$client->lng; 
            }
            $data->save();
            
           return $this->sendResponse('','Success'); 
        }catch(Expection $e){
            return $this->sendError('', ['error'=>$e]); 
        } 
   }
    
}