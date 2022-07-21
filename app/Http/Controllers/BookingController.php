<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Slot;
use App\Models\Shift;
use App\Models\Customer;
use App\Models\Position;
use DataTables;
use App\Models\Requirements;
use App\Helpers\SendMail;
use App\Helpers\Fcm;
class BookingController extends Controller
{
    public function index()
    {
        return view('admin.booking.index');
    }

    public function filter_booking(Request $request)
    {
        if($request->ajax()){
          $data = Booking::select('booking.*','slot.slot_number','customer.full_name as customer_name','slot.client_id')
          ->leftjoin('customer','customer.user_id','booking.customer_id')
          ->leftjoin('slot','slot.id','booking.slot_id');
          if($request->has('booking_no') && !empty($request->booking_no))
          {
            $data=$data->where('booking_no','like','%'.$request->booking_no.'%');
          }
           if($request->has('slot_no') && !empty($request->slot_no))
          {
            $data=$data->where('slot_number','like','%'.$request->slot_no.'%');
          }
          $data=$data->where('slot.client_id',\Auth::user()->id);
          $data=$data->orderBy('booking.id','desc');  
          return Datatables::of($data)

                ->addIndexColumn()
                ->addColumn('booking_no', function($data) { return $data->booking_no; })
                ->addColumn('slot_number', function($data) { return $data->slot_number; })
                ->addColumn('is_approved', function($data) {
                if($data->is_approved=='no' && $data->is_cancelled == 'no')
                {
                    return '<span class="badge bg-warning ms-auto">Pending</span>';
                }
                if($data->is_approved=='yes'  )
                {
                    return '<span class="badge bg-success ms-auto">Approved</span>';
                }  
                if( $data->is_cancelled == 'yes')
                {
                    return '<span class="badge bg-danger ms-auto">Rejected</span>';
                }
                })
                ->rawColumns(['booking_no','slot_number','is_approved','action'])
                ->addColumn('action',function($data){
                    $edit=url('booking/'.$data->id.'/edit');
                    $view=url('booking/'.$data->id);
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>    
                        <a href="'.$edit.'" class="btn bg-primary btn-sm text-white rounded-circle"><i class="fa fa-pencil"></i></a>';
                         
                })
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
    }
    public function create()
    {
        $slot=Slot::where('client_id',\Auth::user()->id)->where('is_active','yes')->get();
        $customer=Customer::get();
        return view('admin.booking.create',[
            'slot'=>$slot,
            'customer'=>$customer
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name'=>'required',
            'slot'=>'required' 
        ]);
        $model = new Booking();
        $model->customer_id=$request->customer_name;
        $model->slot_id=$request->slot;
        //$model->is_approved=$request->is_approved;
        $model->created_by=\Auth::user()->id;
        $model->updated_by=\Auth::user()->id;
        $model->created_at=date('Y-m-d H:i:s');
        $model->updated_at=date('Y-m-d H:i:s');
 
        $model->save();

        echo "Created Successfully";
    }

     public function show($id)
    {
       $data = Booking::select('booking.*',
       'customer.full_name','customer.address','customer.street','customer.city','customer.zip','customer.state','customer.country','customer.alt_mobile','customer.year_of_experience','customer.bio',
       'slot.slot_number','slot.start_date','slot.start_time','slot.end_date','slot.end_time','positions.position_title','slot.position_id',
       'positions.icon',
       'users.name','users.email','users.mobile','users.profile_image')
              ->leftjoin('customer','customer.user_id','booking.customer_id')
              ->leftjoin('users','users.id','booking.customer_id')
              ->leftjoin('slot','slot.id','booking.slot_id')
              ->leftjoin('positions','positions.id','slot.position_id')
              ->find($id);
        $requirements=Requirements::where('position_id',$data->position_id)->get();      
        return view('admin.booking.view',[
            'data' => $data,
            'requirements'=>$requirements
        ]);
    }
    public function edit($id)
    {
        $data=Booking::find($id);
        $slot=Slot::where('client_id',\Auth::user()->id)->where('is_active','yes')->get();
        $customer=Customer::select('customer.*')
        ->leftJoin('users','users.id','customer.user_id')
        ->get();
        return view('admin.booking.update',[
            'data' => $data,
            'customer'=>$customer,
            'slot'=>$slot
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            
            'slot'=>'required',
            
        ]);
        $model =Booking::find($request->row_id);
        $model->customer_id=$request->customer_name;
        $model->slot_id=$request->slot;
        
        $model->updated_by=\Auth::user()->id;
        $model->updated_at=date('Y-m-d H:i:s');
 
        $model->save();
        echo "Updated Successfully";
    }
    public function changeBookingStatus(Request $request)
    {
        $data=Booking::find($request->id);
        if($request->status == 'approve')
        {
            $data->is_approved='yes';
            $slot=Slot::find($data->slot_id);
            // $slot->is_booked='yes';
            // $slot->booked_id=$data->customer_id;
            // $slot->save();
			$shift=Shift::where('slot_id',$data->slot_id)->where('is_booked','no')->first();
			if($shift)
			{
				$shift->is_booked='yes';
            	$shift->booked_id=$data->customer_id;
				$shift->save();
			}
			
        }
        if($request->status == 'reject')
        {
            $data->is_cancelled='yes';    
        }
        $data->save();
		SendMail::sendMail('',[
				'template'=>'booked',
				'booking'=>$data,
				'type'=>'status'
				]);
		Fcm::sendBookingStatus($data);		
        echo 1;
    }
	public function checkApprovable(Request $request)
	{
		$shift=Shift::where('slot_id',$request->id)->where('is_booked','no')->count();
		 
		if($shift != 0)
		{
			echo 'yes';		
		}else{
			echo 'no';	
		}
	}

}


?>