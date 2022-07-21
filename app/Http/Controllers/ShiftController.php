<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slot;
use App\Models\Client;
use App\Models\Positions;
use DataTables;
use App\Models\Requirements;
use App\Models\User;
use App\Helpers\SendMail;
use App\Helpers\Fcm;
use App\Models\Shift;
class ShiftController extends Controller
{
    public function filter_slot(Request $request)
    {
        if($request->ajax()){
          $data = Slot::select('slot.*','client.full_name','positions.position_title')
          ->leftJoin('positions','positions.id','slot.position_id')
          ->leftjoin('client','client.user_id','slot.client_id')
          ->where('slot.is_completed','no');
          if($request->has('slot_no') && !empty($request->slot_no))
          {
            $data=$data->where('slot_number','like','%'.$request->slot_no.'%');
          }
          if($request->has('active_status') && $request->active_status!='all')
          {
            $data=$data->where('is_active',$request->active_status);
          }
          if($request->has('booking_status') && $request->booking_status!='all')
          {
            $data=$data->where('is_booked',$request->booking_status);
          }
          if($request->has('date_from') && $request->date_from !='')
          {
            $data=$data->whereDate('slot.start_date','>=',$request->date_from);
          }
          if($request->has('date_to') && $request->date_to !='')
          {
            $data=$data->whereDate('slot.end_date','<=',$request->date_to);
          }
          $data=$data->whereDate('slot.start_date','>',date('Y-m-d'));
		  $data=$data->where('slot.is_completed','no');	
		  $data=$data->where('slot.is_absent','no');	
          
          //$data=$data->get();  
          return Datatables::of($data)

                ->addIndexColumn()
				->addColumn('full_name', function($data) { return $data->full_name; })
                ->addColumn('position', function($data) { return $data->position_title; })
                ->addColumn('slot_number', function($data) { return $data->slot_number; })
                ->addColumn('start_date', function($data) { return date('d-m-Y',strtotime($data->start_date)); })
                ->addColumn('end_date', function($data) { return date('d-m-Y',strtotime($data->end_date)); })
				->addColumn('start_time', function($data) { return date('h:i A',strtotime($data->start_time)); })
                ->addColumn('end_time', function($data) { return date('h:i A',strtotime($data->end_time)); })
                ->addColumn('is_active', function($data) {  
                if($data->is_active=='yes')
                {
                    return '<span class="badge bg-primary ms-auto">YES</span>';
                }else{
                    return '<span class="badge bg-warning ms-auto">NO</span>';

                }
                
                })
                ->addColumn('is_approved',function($data){
                    if($data->is_approve =='yes')
                    {
                        return '<span class="badge bg-primary ms-auto">Approved</span>';
                    }else{
                        return '<span class="badge bg-warning ms-auto" title="Needs admin approval">Disapproved</span>';
    
                    }
                    
                })
               
                ->addColumn('action',function($data){
                    $view=url('slot-request/'.$data->id);
                   
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>    
                        ';
                         
                })
                 ->rawColumns(['slot_number','start_date','end_date','is_active','action','is_approved'])
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
    }
public function filter_slot_completed(Request $request)
    {
        if($request->ajax()){
          $data = Slot::select('slot.*','client.full_name','positions.position_title','users.name as customer_name')
          ->leftJoin('positions','positions.id','slot.position_id')
          ->leftjoin('client','client.user_id','slot.client_id')
		  ->leftjoin('users','users.id','slot.booked_id')	
          ->where('slot.is_completed','yes');
          if($request->has('slot_no') && !empty($request->slot_no))
          {
            $data=$data->where('slot_number','like','%'.$request->slot_no.'%');
          }
          if($request->has('active_status') && $request->active_status!='all')
          {
            $data=$data->where('is_active',$request->active_status);
          }
          if($request->has('booking_status') && $request->booking_status!='all')
          {
            $data=$data->where('is_booked',$request->booking_status);
          }
          if($request->has('date_from') && $request->date_from !='')
          {
            $data=$data->whereDate('slot.start_date','>=',$request->date_from);
          }
          if($request->has('date_to') && $request->date_to !='')
          {
            $data=$data->whereDate('slot.end_date','<=',$request->date_to);
          }
         
          
          //$data=$data->get();  
          return Datatables::of($data)

                ->addIndexColumn()
				->addColumn('full_name', function($data) { return $data->full_name; })
                ->addColumn('position', function($data) { return $data->position_title; })
                ->addColumn('slot_number', function($data) { return $data->slot_number; })
                ->addColumn('start_date', function($data) { return date('d-m-Y',strtotime($data->start_date)); })
                ->addColumn('end_date', function($data) { return date('d-m-Y',strtotime($data->end_date)); })
				->addColumn('start_time', function($data) { return date('h:i A',strtotime($data->start_time)); })
                ->addColumn('end_time', function($data) { return date('h:i A',strtotime($data->end_time)); })
				->addColumn('completed_by', function($data) { return $data->customer_name; })
                ->addColumn('is_active', function($data) {  
                if($data->is_active=='yes')
                {
                    return '<span class="badge bg-primary ms-auto">YES</span>';
                }else{
                    return '<span class="badge bg-warning ms-auto">NO</span>';

                }  
                })
                ->addColumn('is_approved',function($data){
                    if($data->is_approve =='yes')
                    {
                        return '<span class="badge bg-primary ms-auto">Approved</span>';
                    }else{
                        return '<span class="badge bg-warning ms-auto" title="Needs admin approval">Disapproved</span>';
    
                    }
                    
                })
               
                ->addColumn('action',function($data){
                    $view=url('slot-request/'.$data->id);
                   
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>    
                        ';
                         
                })
                 ->rawColumns(['slot_number','start_date','end_date','is_active','action'])
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
    }
	public function filter_slot_today(Request $request)
	{
		if($request->ajax()){
          $data = Slot::select('slot.*','client.full_name','positions.position_title')
          ->leftJoin('positions','positions.id','slot.position_id')
          ->leftjoin('client','client.user_id','slot.client_id')
          ->where('slot.is_completed','no');
          if($request->has('slot_no') && !empty($request->slot_no))
          {
            $data=$data->where('slot_number','like','%'.$request->slot_no.'%');
          }
          if($request->has('active_status') && $request->active_status!='all')
          {
            $data=$data->where('is_active',$request->active_status);
          }
          if($request->has('booking_status') && $request->booking_status!='all')
          {
            $data=$data->where('is_booked',$request->booking_status);
          }

          $data=$data->where(function($q){
			$q->whereDate('slot.start_date',date('Y-m-d'))
			->whereDate('slot.end_date',date('Y-m-d'))
			->orWhereDate('slot.end_date',date('Y-m-d'));
		  });	
		  $data=$data->where('slot.is_absent','no')
				->where('slot.is_completed','no');
          
          //$data=$data->get();  
          return Datatables::of($data)

                ->addIndexColumn()
				->addColumn('full_name', function($data) { return $data->full_name; })
                ->addColumn('position', function($data) { return $data->position_title; })
                ->addColumn('slot_number', function($data) { return $data->slot_number; })
                ->addColumn('start_date', function($data) { return date('d-m-Y',strtotime($data->start_date)); })
                ->addColumn('end_date', function($data) { return date('d-m-Y',strtotime($data->end_date)); })
				->addColumn('start_time', function($data) { return date('h:i A',strtotime($data->start_time)); })
                ->addColumn('end_time', function($data) { return date('h:i A',strtotime($data->end_time)); })
                ->addColumn('is_active', function($data) {  
                if($data->is_active=='yes')
                {
                    return '<span class="badge bg-primary ms-auto">YES</span>';
                }else{
                    return '<span class="badge bg-warning ms-auto">NO</span>';

                }
                
                })
				 
                ->addColumn('is_approved',function($data){
                    if($data->is_approve =='yes')
                    {
                        return '<span class="badge bg-primary ms-auto">Approved</span>';
                    }else{
                        return '<span class="badge bg-warning ms-auto" title="Needs admin approval">Disapproved</span>';
    
                    } 
                })
               
                ->addColumn('action',function($data){
                     $view=url('slot-request/'.$data->id);
                   
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>    
                        ';
                         
                })
                 ->rawColumns(['slot_number','start_date','end_date','is_active','action','is_approved'])
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
	}
	public function filter_slot_absent(Request $request)
	{
		if($request->ajax()){
          $data = Slot::select('slot.*','client.full_name','positions.position_title')
          ->leftJoin('positions','positions.id','slot.position_id')
          ->leftjoin('client','client.user_id','slot.client_id')
          ->where('slot.is_absent','yes');
          if($request->has('slot_no') && !empty($request->slot_no))
          {
            $data=$data->where('slot_number','like','%'.$request->slot_no.'%');
          }
          if($request->has('active_status') && $request->active_status!='all')
          {
            $data=$data->where('is_active',$request->active_status);
          }
          if($request->has('booking_status') && $request->booking_status!='all')
          {
            $data=$data->where('is_booked',$request->booking_status);
          }
          if($request->has('date_from') && $request->date_from !='')
          {
            $data=$data->whereDate('slot.start_date','>=',$request->date_from);
          }
          if($request->has('date_to') && $request->date_to !='')
          {
            $data=$data->whereDate('slot.end_date','<=',$request->date_to);
          }
         
          
          //$data=$data->get();  
          return Datatables::of($data)

                ->addIndexColumn()
				->addColumn('full_name', function($data) { return $data->full_name; })
                ->addColumn('position', function($data) { return $data->position_title; })
                ->addColumn('slot_number', function($data) { return $data->slot_number; })
                ->addColumn('start_date', function($data) { return date('d-m-Y',strtotime($data->start_date)); })
                ->addColumn('end_date', function($data) { return date('d-m-Y',strtotime($data->end_date)); })
				->addColumn('start_time', function($data) { return date('h:i A',strtotime($data->start_time)); })
                ->addColumn('end_time', function($data) { return date('h:i A',strtotime($data->end_time)); })
                ->addColumn('remarks_for_absent', function($data) {  
                   return $data->remarks_for_absent;
                })
                ->addColumn('action',function($data){
                    $view=url('slot-request/'.$data->id);
                   
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>    
                        ';
                         
                })
				->addColumn('is_approved',function($data){
                    if($data->is_approve =='yes')
                    {
                        return '<span class="badge bg-primary ms-auto">Approved</span>';
                    }else{
                        return '<span class="badge bg-warning ms-auto" title="Needs admin approval">Disapproved</span>';
    
                    }
                    
                })
                 ->rawColumns(['slot_number','start_date','end_date','is_active','action'])
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
	}
	public function slot_request_filter(Request $request)
    {
      if($request->ajax()){
          $data = Slot::select('slot.*','client.full_name','positions.position_title')
          ->leftJoin('positions','positions.id','slot.position_id')
          ->leftjoin('client','client.user_id','slot.client_id');
          //->where('slot.is_approve','no');
           
          if($request->has('approve_status') && $request->approve_status !='all')
          {
            $data=$data->where('slot.is_approve',$request->approve_status);
          } 
          if($request->has('date_from') && $request->date_from !='')
          {
            $data=$data->whereDate('slot.start_date','>=',$request->date_from);
          }
          if($request->has('date_to') && $request->date_to !='')
          {
            $data=$data->whereDate('slot.end_date','<=',$request->date_to);
          }
          $data=$data->where(function($q){
			$q->where('slot.is_approve','no')
			->orWhereDate('slot.start_date','>',date('Y-m-d'));
		  });
          $data=$data->orderBy('slot.id','desc');  
          return Datatables::of($data)

                ->addIndexColumn()
                ->addColumn('full_name', function($data) { return $data->full_name; })
                ->addColumn('position', function($data) { return $data->position_title; })
                ->addColumn('slot_number', function($data) { return $data->slot_number; })
                ->addColumn('start_date', function($data) { return date('d-m-Y',strtotime($data->start_date)); })
                ->addColumn('end_date', function($data) { return date('d-m-Y',strtotime($data->end_date)); })
				->addColumn('start_time', function($data) { return date('h:i A',strtotime($data->start_time)); })
                ->addColumn('end_time', function($data) { return date('h:i A',strtotime($data->end_time)); })
                ->addColumn('is_approved', function($data) { 
                            $html = '<div class="btn-group mt-3">
                                <input type="radio" class="btn-check"  value="yes" name="approve_'.$data->id.'" id="approve_'.$data->id.'" ';
                                if($data->is_approve == 'yes'){
                                $html .= 'checked=""';
                                }
                                $html .= ' >
                                <label class="btn btn-outline-primary" for="approve_'.$data->id.'">Approved</label>
                                
                                <input type="radio" class="btn-check"  value="no" name="approve_'.$data->id.'" id="disapprove_'.$data->id.'" ';
                                if($data->is_approve == 'no'){
                                $html .= 'checked=""';
                                }
                                
                            $html .= ' >
                                <label class="btn btn-outline-primary" for="disapprove_'.$data->id.'">Disapproved</label>
                            </div>';
                    return $html; 
                    
                })  
                ->addColumn('action',function($data){
                    
                    $view=url('slot-request/'.$data->id);
                   
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>    
                        ';
                         
                         
                })
                 ->rawColumns(['slot_number','start_date','end_date','is_approved','action'])
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }  
    }
	public function checkBookedCount(Request $request)
	{
		$data=Shift::where('slot_id',$request->id)->where('is_booked','yes')->count();
		return $data;
	}
	public function getApproveForm(Request $request)
	{
		$data=Slot::find($request->id);
		return view('admin.slot-request.approve-form',compact('data'));
	}
}
