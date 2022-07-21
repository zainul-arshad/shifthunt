<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\User;
use App\Models\Slot;
use DataTables;
use Validator;
use App\Models\Positions;
use App\Models\Requirements;
use App\Models\GPositions;
use App\Models\GRequirements;
class ClientController extends Controller
{
	public function index()
	{
		return view('admin.client.index');
	}

	public function filter_client(Request $request)
    {
        if($request->ajax()){
          $data = Client::select('client.*','users.name as client_name','users.email','users.mobile','users.profile_image')
          ->leftJoin('users','users.id','client.user_id');
          if($request->has('name') && !empty($request->name))
          {
            $data=$data->where('client_name','like','%'.$request->name.'%');
          }
          if($request->has('mobile') && !empty($request->mobile))
          {
            $data=$data->where('mobile','like','%'.$request->mobile.'%')->orwhere('alt_mobile','like','%'.$request->mobile.'%');
          }
          if($request->has('email') && !empty($request->email))
          {
            $data=$data->where('email','like','%'.$request->email.'%')->orwhere('email','like','%'.$request->email.'%');
          }
        
         
         
          $data=$data->get();  
          return Datatables::of($data)

                ->addIndexColumn()
                ->addColumn('client_name', function($data) { return ucfirst($data->client_name); })
                ->addColumn('email', function($data) { return $data->email; })
                ->addColumn('mobile', function($data) { return $data->mobile; })
                ->addColumn('action',function($data){
                    $edit=url('client/'.$data->id.'/edit');
                    $view=url('client/'.$data->id);
                    $slotlist=url('slotlist/'.$data->id);
                    $slot=' <a href="'.$slotlist.'" class="btn bg-warning btn-sm text-white rounded-circle" title="Slots"><i class="fa  fa-clock-o"></i></a>  ';
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>    
                        <a href="'.$edit.'" class="btn bg-primary btn-sm text-white rounded-circle"><i class="fa fa-pencil"></i></a>'.$slot.'

                            <button class="btn bg-danger btn-sm text-white rounded-circle deleteBtn" type="button" value="'.$data->id.'"><i class="fa fa-trash"></i></button>';
                         
                })
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
    }
    public function create()
    {
    	$positions=GPositions::where('is_active','yes')->get();
    	return view('admin.client.create',[
			'positions'=>$positions
		]);
    }

    public function store(Request $request)
    {
    	$validator =Validator::make($request->all(),[
            'full_name'=>'required',
            'email'=>'required|unique:users',
            'mobile'=>'required|unique:users',
            'address'=>'required',
            'state'=>'required',
            'country'=>'required',
            'city'=>'required',
            'zip'=>'required',
            'commission_percentage'=>'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['error'=>$validator->messages()],500);exit;
        }
        $user=new User(); 
        if($request->hasFile('profile_image'))
        {
            $path=public_path().'/uploads/client';
            $files=$request->file('profile_image');
            $name=time().$files->getClientOriginalName();
            $files->move($path,$name);
            $user->profile_image=$name;
        }
        $user->password=bcrypt('12345678');
        $user->name=$request->full_name;
        $user->user_type='carer_home';
        $user->email=$request->email;
        $user->mobile=$request->mobile;
        if($user->save())
        {
        	$model = new Client();
        	$model->full_name=$request->full_name;
        	$model->alt_mobile=$request->alt_mobile;
        	$model->address=$request->address;
        	$model->street=$request->street;
        	$model->city=$request->city;
        	$model->state=$request->state;
        	$model->country=$request->country;
        	$model->zip=$request->zip;
            $model->user_id=$user->id;
            $model->lat=$request->lat;
            $model->lng=$request->lng;
        	$model->created_by=\Auth::user()->id;
    		$model->updated_by=\Auth::user()->id;
    		$model->created_at=date('Y-m-d H:i:s');
    		$model->updated_at=date('Y-m-d H:i:s');
    		$model->commission_percentage=$request->commission_percentage;
    		$path=public_path().'/uploads/client'; 
    		if($model->save()){
				if($request->has('positions') && count($request->positions) != 0)
				{
					foreach($request->positions as $position)
					{
						$gposition=GPositions::find($position);
						if($gposition)
						{
							$newPosition=new Positions();
							$newPosition->client_id=$model->user_id;
							$newPosition->position_title=$gposition->position_title;
							$newPosition->is_active=$gposition->is_active;
							$newPosition->position_desc=$gposition->position_desc;
							$newPosition->icon=$gposition->icon;
							$newPosition->template=$gposition->template;
							$newPosition->created_by=\Auth::user()->id;
							$newPosition->updated_by=\Auth::user()->id;
							$newPosition->created_at=date('Y-m-d H:i:s');
							$newPosition->updated_at=date('Y-m-d H:i:s');
							if($newPosition->save())
							{
								$grequirements=GRequirements::where('position_id',$gposition->id)->get();
								foreach($grequirements as $grequirement)
								{
									$newRequirement=new Requirements();
									$newRequirement->name=$grequirement->name;
									$newRequirement->client_id=$model->user_id;
									$newRequirement->position_id=$newPosition->id;
									$newRequirement->save(); 
								}
							}
						}
					}
				}
			}
         
        }
		echo "Created Successfully";
    }

     public function show($id)
    {
        $data=Client::select('client.*','users.name as client_name','users.email','users.mobile','users.profile_image')
        ->leftJoin('users','users.id','client.user_id')
        ->where('client.id',$id)
        ->first();

        return view('admin.client.view',[
            'data' => $data
        ]);
    }
    public function edit($id)
    {
        $data=Client::select('client.*','users.name as client_name','users.email','users.mobile','users.profile_image')
        ->leftJoin('users','users.id','client.user_id')
        ->where('client.id',$id)
        ->first();
        return view('admin.client.update',[
            'data' => $data
        ]);
    }

    public function update(Request $request)
    {
         
        $validator =Validator::make($request->all(),[
            'full_name'=>'required',
            'email'=>'required|unique:users,email,'.$request->client_id,
            'mobile'=>'required|unique:users,mobile,'.$request->client_id,
            'address'=>'required',
            'state'=>'required',
            'country'=>'required',
            'city'=>'required',
            'zip'=>'required',
            'commission_percentage'=>'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['error'=>$validator->messages()],500);exit;
        }
        $user=User::find($request->client_id); 
        if($request->hasFile('profile_image'))
        {
            $path=public_path().'/uploads/client';
            $files=$request->file('profile_image');
            $name=time().$files->getClientOriginalName();
            $files->move($path,$name);
            $user->profile_image=$name;
        }
        $user->name=$request->full_name;
        $user->email=$request->email;
        $user->mobile=$request->mobile;
        if($user->save())
        {
            $model =Client::where('user_id',$request->client_id)->first();
            $model->full_name=$request->full_name;
            $model->alt_mobile=$request->alt_mobile;
            $model->address=$request->address;
            $model->street=$request->street;
            $model->city=$request->city;
            $model->state=$request->state;
            $model->country=$request->country;
            $model->zip=$request->zip;
            $model->lat=$request->lat;
            $model->lng=$request->lng;
            $model->updated_by=\Auth::user()->id;
            $model->updated_at=date('Y-m-d H:i:s');
            $model->commission_percentage=$request->commission_percentage;
            $model->save();
        }

        
         
        echo "Updated Successfully";
    }

    public function destroy(Request $request)
    {
        $data=Client::find($request->id);
        $user=User::find($data->user_id);
        $data->delete();
        $user->delete();
        echo 1;
    }
    public function slotlist($client_id)
    {
         $client=Client::find($client_id);
        // $data=Slot::where('client_id',$client->user_id)->get();
        return view('admin.slotlist.index',['client_id'=>$client_id,'user_id'=>$client->user_id]);
    }
    public function slotlist_filter(Request $request)
    {
        $data=Slot::select('slot.*','positions.position_title')
        ->leftJoin('positions','positions.id','slot.position_id')
        ->where('slot.client_id',$request->user_id);
        
        if($request->has('slot_no') && !empty($request->slot_no))
          {
            $data=$data->where('slot.slot_number','like','%'.$request->slot_no.'%');
          }
          if($request->has('active_status') && $request->active_status!='all')
          {
            $data=$data->where('slot.is_active',$request->active_status);
          }
          if($request->has('booking_status') && $request->booking_status!='all')
          {
            $data=$data->where('slot.is_booked',$request->booking_status);
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
        return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('slot_number', function($data) { return $data->slot_number; })
                ->addColumn('position', function($data) { return $data->position_title; })
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
                ->addColumn('action',function($data){
                    $edit=url('slot/'.$data->id.'/edit');
                    $view=url('slotlistview/'.$data->id);
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>    
                        
                        ';
					//<a href="'.$edit.'" class="btn bg-primary btn-sm text-white rounded-circle"><i class="fa fa-pencil"></i></a>
					//<button class="btn bg-danger btn-sm text-white rounded-circle deleteBtn" type="button" value="'.$data->id.'"><i class="fa fa-trash"></i></button>
                         
                }) 
                ->rawColumns(['is_active','start_date','end_date','is_active','position','action'])
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
            ->make(true);
    }
    public function slotlist_filter_completed(Request $request)
    {
        $data=Slot::select('slot.*','positions.position_title')
        ->leftJoin('positions','positions.id','slot.position_id')
		->leftjoin('users','users.id','slot.booked_id')
        ->where('slot.client_id',$request->user_id);
        
        if($request->has('slot_no') && !empty($request->slot_no))
          {
            $data=$data->where('slot.slot_number','like','%'.$request->slot_no.'%');
          }
          if($request->has('active_status') && $request->active_status!='all')
          {
            $data=$data->where('slot.is_active',$request->active_status);
          }
          if($request->has('booking_status') && $request->booking_status!='all')
          {
            $data=$data->where('slot.is_booked',$request->booking_status);
          }
          if($request->has('date_from') && $request->date_from !='')
          {
            $data=$data->whereDate('slot.start_date','>=',$request->date_from);
          }
          if($request->has('date_to') && $request->date_to !='')
          {
            $data=$data->whereDate('slot.end_date','<=',$request->date_to);
          }
		   
		  $data=$data->where('slot.is_completed','yes');	
		   
        return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('slot_number', function($data) { return $data->slot_number; })
				->addColumn('completed_by', function($data) { return $data->customer_name; })
                ->addColumn('position', function($data) { return $data->position_title; })
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
                ->addColumn('action',function($data){
                    $edit=url('slot/'.$data->id.'/edit');
                    $view=url('slotlistview/'.$data->id);
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>    
                        
                        ';
					//<a href="'.$edit.'" class="btn bg-primary btn-sm text-white rounded-circle"><i class="fa fa-pencil"></i></a>
					//<button class="btn bg-danger btn-sm text-white rounded-circle deleteBtn" type="button" value="'.$data->id.'"><i class="fa fa-trash"></i></button>
                         
                }) 
                ->rawColumns(['is_active','start_date','end_date','is_active','position','action'])
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
            ->make(true);
    }
	public function slotlist_filter_today(Request $request)
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
                    $view=url('slotlistview/'.$data->id);
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
                        '.$complete.$absent
                        ;
                         
                })
                 ->rawColumns(['slot_number','start_date','end_date','is_active','action','is_approved'])
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
	}
	public function slotlist_filter_absent(Request $request)
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
                    $view=url('slotlistview/'.$data->id);
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
	public function slotlistview($slot_id)
	{
		$data = Slot::select('slot.*','client.full_name','positions.position_title','client.id as client_id_pk')
              ->leftjoin('users','users.id','slot.client_id')
              ->leftjoin('client','client.user_id','users.id')
              ->leftjoin('positions','positions.id','slot.position_id')
              ->find($slot_id);
        $position=Positions::find($data->position_id); 
        $requirements=Requirements::where('position_id',$data->position_id)->get();
        return view('admin.slotlist.view',[
            'data' => $data,
            'position'=>$position,
            'requirements'=>$requirements
        ]);
	}
}


?>