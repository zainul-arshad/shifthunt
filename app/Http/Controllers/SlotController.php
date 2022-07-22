<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slot;
use App\Models\Shift;
use App\Models\Client;
use App\Models\Positions;
use DataTables;
use App\Models\Requirements;
use App\Models\User;
use App\Models\Booking;
use App\Helpers\SendMail;
use App\Helpers\Fcm;
class SlotController extends Controller
{
	public function index()
	{
		return view('admin.slot.index');
	}

	public function filter_slot(Request $request)
    {
        if($request->ajax()){
          $data = Slot::select('slot.*','client.full_name','positions.position_title')
          ->leftJoin('positions','positions.id','slot.position_id')
          ->leftjoin('client','client.id','slot.client_id')
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
          $data=$data->where('slot.client_id',\Auth::user()->id);
          //$data=$data->get();  
          return Datatables::of($data)

                ->addIndexColumn()
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
                    $edit=url('slot/'.$data->id.'/edit');
                    $view=url('slot/'.$data->id);
                    // $complete='<button class="btn bg-success btn-sm text-white rounded-circle markAsCompleteBtn" type="button" value="'.$data->id.'" title="Mark As Complete" ><i class="fa fa-check"></i></button>';
                    // if($data->is_approve == 'no')
                    // {
                    //     $complete='';
                    // }
					$editBtn='<a href="'.$edit.'" class="btn bg-primary btn-sm text-white rounded-circle"><i class="fa fa-pencil"></i></a>';
					$delete='<button class="btn bg-danger btn-sm text-white rounded-circle deleteBtn" type="button" value="'.$data->id.'"><i class="fa fa-trash"></i></button>';
					if( $data->is_booked == 'no')
					{
						$complete='';
					}else{
						$delete='';
						$editBtn='';
					}
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>    
                        '.$editBtn.$delete;
                         
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
          ->leftjoin('client','client.id','slot.client_id')
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
         
          $data=$data->where('slot.client_id',\Auth::user()->id);
          //$data=$data->get();  
          return Datatables::of($data)

                ->addIndexColumn()
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
               
               
                ->addColumn('action',function($data){
                    $edit=url('slot/'.$data->id.'/edit');
                    $view=url('slot/'.$data->id);
					$update='<button class="btn bg-primary btn-sm text-white rounded-circle updateBtn" type="button" value="'.$data->id.'" title="Update"><i class="fa fa-refresh"></i></button>';
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a> '.$update;
						//<a href="'.$edit.'" class="btn bg-primary btn-sm text-white rounded-circle"><i class="fa fa-pencil"></i></a>
						//<button class="btn bg-danger btn-sm text-white rounded-circle deleteBtn" type="button" value="'.$data->id.'"><i class="fa fa-trash"></i></button>
                         
                })
                 ->rawColumns(['slot_number','start_date','end_date','is_active','action'])
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
    }
    public function create()
    {
        $client=Client::get();
        $positions=Positions::where('client_id',\Auth::user()->id)->where('is_active','yes')->get();
    	return view('admin.slot.create',[
            'clients'=>$client,
            'positions'=>$positions
        ]);
    }

    public function store(Request $request)
    {
        if(\Auth::user()->user_type == 'admin')
        {
            $request->validate([
            'client_name'=>'required',  
            'start_date'=>'required',
            'start_time'=>'required',
            'end_date'=>'required',
            'end_time'=>'required',
            'is_active'=>'required',
			'price'=>'required',
			'price_type'=>'required',
            //'is_booked'=>'required',
            'position'=>'required',
			'no_of_shifts'=>'required',
            ]);
              
        }else{
            $request->validate([
            'start_date'=>'required',
            'start_time'=>'required',
            'end_date'=>'required',
            'end_time'=>'required',
            'is_active'=>'required',
            'price'=>'required',
			'price_type'=>'required',
            'position'=>'required',
			'no_of_shifts'=>'required',
			'duration'=>'required'
            ]);
        }
    	
    	$model = new Slot();
        if(\Auth::user()->user_type == 'admin')
        {
    	 $model->client_id=$request->client_name;
        }else{
         $model->client_id=\Auth::user()->id;  
        }
        $model->position_id=$request->position;
    	$model->start_date=$request->start_date;
    	$model->start_time=$request->start_time;
    	$model->end_date=$request->end_date;
        $model->end_time=$request->end_time;
    	$model->is_active=$request->is_active;
    	//$model->is_booked=$request->is_booked;
    	$model->created_by=\Auth::user()->id;
		$model->updated_by=\Auth::user()->id;
		$model->created_at=date('Y-m-d H:i:s');
		$model->updated_at=date('Y-m-d H:i:s');
		$model->template=$request->template;
		$model->price=$request->price;
		$model->price_type=$request->price_type;
		$model->duration=$request->duration;
        $model->no_of_shifts=$request->no_of_shifts;
		$model->no_of_shifts=$request->no_of_shifts;
		$model->commission_percentage=Client::getCommission($model->client_id);
		$model->commission_amount=($model->price*$model->commission_percentage*$model->duration*$model->no_of_shifts)/100;
		if($model->save())
		{
			if($request->no_of_shifts > 0)
			{
				for($i=1;$i<=$request->no_of_shifts;$i++)
				{
					$shift=new Shift();
					$shift->slot_id=$model->id;
					$shift->client_id=$model->client_id;
					$shift->position_id=$model->position_id;
					$shift->start_date=$model->start_date;
					$shift->start_time=$model->start_time;
					$shift->end_date=$model->end_date;
					$shift->end_time=$model->end_time;
					$shift->save();
				}
			}
		}

		echo "Created Successfully";
    }

     public function show($id)
    {
       $data = Slot::select('slot.*','client.full_name','positions.position_title')
              ->leftjoin('users','users.id','slot.client_id')
              ->leftjoin('client','client.user_id','users.id')
              ->leftjoin('positions','positions.id','slot.position_id')
              ->find($id);
        return view('admin.slot.view',[
            'data' => $data
        ]);
    }
    public function edit($id)
    {
        $data=Slot::find($id);
        $client=Client::get();
        $positions =Positions::where('client_id',\Auth::user()->id)->where('is_active','yes')->get();
        return view('admin.slot.update',[
            'data' => $data,
            'clients'=>$client,
            'positions'=>$positions
        ]);
    }

    public function update(Request $request)
    {
        if(\Auth::user()->user_type == 'admin')
        {
            $request->validate([
            'client_name'=>'required',  
            'start_date'=>'required',
            'start_time'=>'required',
            'end_date'=>'required',
            'end_time'=>'required',
            'is_active'=>'required',
            //'is_booked'=>'required',
            'position'=>'required',
			'no_of_shifts'=>'required',
			'duration'=>'required'

            ]);
              
        }else{
            $request->validate([
            'start_date'=>'required',
            'start_time'=>'required',
            'end_date'=>'required',
            'end_time'=>'required',
            'is_active'=>'required',
            //'is_booked'=>'required',
            'position'=>'required',
			'no_of_shifts'=>'required',
			'duration'=>'required'

            ]);
        }
        $model = Slot::find($request->row_id);
        if(\Auth::user()->user_type == 'admin')
        {
         $model->client_id=$request->client_name;
        }else{
         $model->client_id=\Auth::user()->id;  
        }
        $model->position_id=$request->position;
        $model->start_date=$request->start_date;
        $model->start_time=$request->start_time;
        $model->end_date=$request->end_date;
        $model->end_time=$request->end_time;
        $model->is_active=$request->is_active;
        $model->is_booked=$request->is_booked;
        $model->updated_by=\Auth::user()->id;
        $model->updated_at=date('Y-m-d H:i:s');
		$model->template=$request->template;
		$model->price=$request->price;
		$model->price_type=$request->price_type;
		$model->duration=$request->duration;
		if(Shift::where('slot_id',$model->id)->where('client_id',$model->client_id)->where('is_booked','yes')->count() < $request->no_of_shifts)
		{
			 $model->no_of_shifts=$request->no_of_shifts;
		}
		$model->commission_percentage=Client::getCommission($model->client_id);
		$model->commission_amount=($model->price*$model->commission_percentage*$model->duration*$model->no_of_shifts)/100;
        if($model->save())
		{
			$shift_count=Shift::where('slot_id',$model->id)->where('client_id',$model->client_id)->count();

			if($request->no_of_shifts > 0 && $shift_count != $request->no_of_shifts)
			{
				 
				if($shift_count < $request->no_of_shifts)
				{
					$sc=$request->no_of_shifts - $shift_count;
					for($i=1;$i<=$sc;$i++)
					{
						$shift=new Shift();
						$shift->slot_id=$model->id;
						$shift->client_id=$model->client_id;
						$shift->position_id=$model->position_id;
						$shift->start_date=$model->start_date;
						$shift->start_time=$model->start_time;
						$shift->end_date=$model->end_date;
						$shift->end_time=$model->end_time;
						$shift->save();
					}

				}else{
					if($shift_count > $request->no_of_shifts)
				    {
						$sc=$shift_count - $request->no_of_shifts ;
						Shift::where('slot_id',$model->id)->where('client_id',$model->client_id)->where('is_booked','no')/*->orderBy('id','desc')*/->limit($sc)->delete();
				    }
					if($shift_count == 0)
					{
						for($i=1;$i<=$request->no_of_shifts;$i++)
						{
							$shift=new Shift();
							$shift->slot_id=$model->id;
							$shift->client_id=$model->client_id;
							$shift->position_id=$model->position_id;
							$shift->start_date=$model->start_date;
							$shift->start_time=$model->start_time;
							$shift->end_date=$model->end_date;
							$shift->end_time=$model->end_time;
							$shift->save();
						} 
					}
				}
				
				
			}
		}
        echo "Updated Successfully";
    }

    public function destroy(Request $request)
    {
        $data=Slot::find($request->id)->delete();
        echo 1;
    }
    public function markAsComplete(Request $request)
    {
        $slot=Slot::find($request->id);
        if($slot && $slot->client_id == \Auth::user()->id)
        {
          $slot->is_completed='yes';
          $slot->save();
        }
        echo 1;
    }
    
    
    public function slot_requests()
    {
       return view('admin.slot-request.index'); 
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
    public function approveSlot(Request $request)
    {
		$data;
		if($request->status == 'yes')
		{
			$data=Slot::find($request->slot_id);
			$data->is_approve='yes';
			$data->commission_percentage=$request->commission_percentage;
			$data->commission_amount=($request->commission_percentage*$data->price*$data->no_of_shifts*$data->duration)/100;
			$data->save();
		}else{
			$data=Slot::find($request->id);
			$data->is_approve=$request->status;
			if($data->save())
			{
				echo 1;
			}else{
				echo 0;
			}
		}
        
	    $user=User::find($data->client_id);
		SendMail::sendMail($user->email,[
		'template'=>'shift_status',
		'user'=>$user,
		'shift'=>$data,
		]);
		if($request->status == 'yes')
		{
			 SendMail::sendMail('',[
			'template'=>'new_shift',
			'shift'=>$data,
			]);
            Fcm::send($data);
		}
    }
    public function slotRequestView($id)
    {
       $data = Slot::select('slot.*','client.full_name','positions.position_title')
              ->leftjoin('users','users.id','slot.client_id')
              ->leftjoin('client','client.user_id','users.id')
              ->leftjoin('positions','positions.id','slot.position_id')
              ->find($id);
        $position=Positions::find($data->position_id); 
        $requirements=Requirements::where('position_id',$data->position_id)->get();
        return view('admin.slot-request.view',[
            'data' => $data,
            'position'=>$position,
            'requirements'=>$requirements
        ]); 
    }
	public function filter_slot_today(Request $request)
	{
		if($request->ajax()){
          $data = Slot::select('slot.*','client.full_name','positions.position_title')
          ->leftJoin('positions','positions.id','slot.position_id')
          ->leftjoin('client','client.id','slot.client_id')
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
          $data=$data->where('slot.client_id',\Auth::user()->id);
          //$data=$data->get();  
          return Datatables::of($data)

                ->addIndexColumn()
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
                    $edit=url('slot/'.$data->id.'/edit');
                    $view=url('slot/'.$data->id);
					$editBtn='<a href="'.$edit.'" class="btn bg-primary btn-sm text-white rounded-circle"><i class="fa fa-pencil"></i></a>';
					$delete='<button class="btn bg-danger btn-sm text-white rounded-circle deleteBtn" type="button" value="'.$data->id.'"><i class="fa fa-trash"></i></button>';
                    $complete='&nbsp;<button class="btn bg-success btn-sm text-white rounded-circle markAsCompleteBtn" type="button" value="'.$data->id.'" title="Mark As Complete" ><i class="fa fa-check"></i></button>';
                    $absent='&nbsp;<button class="btn bg-info btn-sm text-white rounded-circle markAsAbsentBtn" type="button" value="'.$data->id.'" title="Mark As Absent" ><i class="fa fa-times"></i></button>';
					if($data->is_approve == 'no' || $data->is_absent == 'yes' || $data->is_completed == 'yes' )
                    {
                        $complete='';
						$absent='';
                    }
					if( $data->is_booked == 'no')
					{
						$complete='';
					}else{
						$delete='';
						$editBtn='';
					}
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>    
                        '.$editBtn.$complete.$absent.$delete
                        ;
                         
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
          ->leftjoin('client','client.id','slot.client_id')
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
         
          $data=$data->where('slot.client_id',\Auth::user()->id);
          //$data=$data->get();  
          return Datatables::of($data)

                ->addIndexColumn()
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
                    $edit=url('slot/'.$data->id.'/edit');
                    $view=url('slot/'.$data->id);
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>    
                        
                        ';
					//<a href="'.$edit.'" class="btn bg-primary btn-sm text-white rounded-circle"><i class="fa fa-pencil"></i></a>
					//<button class="btn bg-danger btn-sm text-white rounded-circle deleteBtn" type="button" value="'.$data->id.'"><i class="fa fa-trash"></i></button>
                         
                })
                 ->rawColumns(['slot_number','start_date','end_date','is_active','action'])
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
	}
	public function markAsAbsent(Request $request)
	{
		$data=Slot::find($request->id);
		$data->is_absent='yes';
		$data->remarks_for_absent=$request->remarks_for_absent;
		if($data->save())
		{
			return 1;
		}else{
			return 0;
		}
	}
	public function slot_completion_update(Request $request)
	{
		$data=Slot::find($request->id);
		$booking=Booking::where('slot_id',$data->id)
				->where('customer_id',$data->booked_id)
				->first();
		$view=view('admin.slot.slot_completion_update',['data'=>$data,'booking'=>$booking])->render();
		return $view;
	}
	public function slot_completion_store(Request $request)
	{
		 
		$data=Slot::find($request->id);
		if($request->is_absent == 'yes')
		{
			$data->is_absent='yes';
			$data->remarks_for_absent=$request->remarks_for_absent;
		    $data->is_completed='no';
			$data->remarks_for_completion='';
			if($data->save())
			{
				echo 1;
			}
		}
		else{
			$data->is_absent='no';
			$data->remarks_for_absent='';
			$data->is_completed='yes';
			$data->remarks_for_completion=$request->remarks_for_completion;
			if($data->save())
			{
				$booking=Booking::where('slot_id',$data->id)
				->where('customer_id',$data->booked_id)
				->first();
				if($booking)
				{
					$booking->start_date=$request->start_date;
					$booking->end_date=$request->end_date;
					$booking->start_time=$request->start_time;
					$booking->end_time=$request->end_time;
					$booking->rating=$request->rating;
					$booking->duration=$request->duration;
					$booking->save();
					echo 1;
				}
			}
		}
	}
     
}


?>