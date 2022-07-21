<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Validation\Rule;
use DataTables;
use \App\Models\Personal;
use \App\Models\PrefferedHours;
use \App\Models\Qualifications;
use \App\Models\TrainingAndDevelopment;
use \App\Models\EmploymentHistory;
use \App\Models\Disqualifications;
use \App\Models\Referers;
use \App\Models\BankDetails;
use \App\Models\Declaration;
use \App\Models\Questionnaire;
use \App\Models\SelfDeclaration;
use \App\Helpers\SendMail;
class CustomerController extends Controller
{
	public function index()
	{
		return view('admin.customer.index');
	}

	public function filter_customer(Request $request)
    {
        if($request->ajax()){
          $data = Customer::select('customer.*','users.name','users.mobile','users.email','users.profile_image','users.last_step','users.approve_status')
          ->leftJoin('users','users.id','customer.user_id');
          if($request->has('name') && !empty($request->name))
          {
            $data=$data->where('customer.full_name','like','%'.$request->name.'%');
          }
          
          if($request->has('mobile') && !empty($request->mobile))
          {
            $data=$data->where('users.mobile','like','%'.$request->mobile.'%');
          }
          
          if($request->has('email') && !empty($request->email))
          {
            $data=$data->where('users.email','like','%'.$request->email.'%');
          }
           
         
         
          //$data=$data->get();  
          return Datatables::of($data)

                ->addIndexColumn()
                ->addColumn('first_name', function($data) { return ucfirst($data->full_name); })
                ->addColumn('email', function($data) { return $data->email; })
                ->addColumn('mobile', function($data) { return $data->mobile; })
                ->addColumn('is_approved', function($data) { 
                            $html = '<div class="btn-group mt-3">
                                <input type="radio" class="btn-check approveStatusBtn" data-id="'.$data->user_id.'" value="approved" name="approve_'.$data->id.'" id="approve_'.$data->id.'" ';
                                if($data->approve_status == 'approved'){
                                $html .= 'checked=""';
                                }
                            $html .= ' >
                                <label class="btn btn-outline-primary" for="approve_'.$data->id.'">Approved</label>
                                
                                <input type="radio" class="btn-check approveStatusBtn" data-id="'.$data->user_id.'"  value="disapproved" name="approve_'.$data->id.'" id="disapprove_'.$data->id.'" ';
                                if($data->approve_status == 'disapproved'){
                                $html .= 'checked=""';
                                }
                                
                            $html .= ' >
                                <label class="btn btn-outline-primary" for="disapprove_'.$data->id.'">Disapproved</label>
                            </div>';
                    return $html; 
                    
                })
                ->addColumn('action',function($data){
                    $edit=url('customer/'.$data->id.'/edit');
                    $view=url('customer/'.$data->id);
                    $regUrl=url('customer-registration/'.$data->user_id);
                    $regView='<a href="'.$regUrl.'" class="btn bg-success btn-sm text-white rounded-circle" title="Registration"><i class="fa fa-wpforms"></i></a>';
                     
                    if($data->last_step != '11')
                    {
                      $regView='';  
                    }
                   return  '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>
                            <a href="'.$edit.'" class="btn bg-primary btn-sm text-white rounded-circle"><i class="fa fa-pencil"></i></a>
                            '.$regView.'
                            <button class="btn bg-danger btn-sm text-white rounded-circle deleteBtn" type="button" value="'.$data->id.'"><i class="fa fa-trash"></i></button>';
                         
                })
                ->rawColumns(['action', 'is_approved'])
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
    }
    public function create()
    {
    	return view('admin.customer.create');
    }

    public function store(Request $request)
    {
    	$request->validate([
    		'full_name'=>'required',
    		//'last_name'=>'required',
    		'dob'=>'required',
    		'email'=>'required|unique:users',
    		'mobile'=>'required|unique:users',
    		'address'=>'required',
    		'city'=>'required',
    		'state'=>'required',
    		'country'=>'required',
    		'city'=>'required',
    		'year_of_experience'=>'required',
    		'bio'=>'required',
            
    	]);
        $userdata= new User();
        $userdata->name=$request->full_name ;
        $userdata->email=$request->email;
        $userdata->mobile=$request->mobile;
        $userdata->user_type='carer';
        $userdata->password=bcrypt('12345678');
        $userdata->created_at=date('Y-m-d H:i:s');
        $userdata->updated_at=date('Y-m-d H:i:s');
        $userdata->user_type='carer';
        $userdata->otp_mobile='112233';//rand(10000,99999);
        $userdata->otp_email='112233';//rand(10000,99999);
        $userdata->mobile_otp_created_at= date('Y-m-d H:i:s');
        $userdata->email_otp_created_at=date('Y-m-d H:i:s');
        if($request->hasFile('profile_image'))
        {
                $path=public_path().'/uploads/customer';
                $files=$request->file('profile_image');
                $name=time().$files->getClientOriginalName();
                $files->move($path,$name);
                $userdata->profile_image=$name;
        }
        if($userdata->save())
        {
        	$model = new Customer();
             

        	$model->full_name=$request->full_name;
        	 
            $model->user_id=$userdata->id;
        	$model->dob=$request->dob;
        	 
        	 
        	$model->alt_mobile=$request->alt_mobile;
        	$model->year_of_experience=$request->year_of_experience;
        	$model->bio=$request->bio;
        	$model->address=$request->address;
        	$model->lat=$request->lat;
            $model->lng=$request->lng;
        	$model->street=$request->street;
        	$model->city=$request->city;
        	$model->state=$request->state;
        	$model->country=$request->country;
        	$model->zip=$request->zip;
        	$model->created_by=\Auth::user()->id;
    		$model->updated_by=\Auth::user()->id;
    		$model->created_at=date('Y-m-d H:i:s');
    		$model->updated_at=date('Y-m-d H:i:s');

    		
    		

            

            
    		$model->save();
        }
	
		echo "Created Successfully";
    }
     public function show($id)
    {
        $data=Customer::select('customer.*','users.name','users.mobile','users.email','users.profile_image')
          ->leftJoin('users','users.id','customer.user_id')
          ->where('customer.id',$id)
          ->first();
        return view('admin.customer.view',[
            'data' => $data
        ]);
    }
    public function edit($id)
    {
        $data=Customer::select('customer.*','users.name','users.mobile','users.email','users.profile_image')
          ->leftJoin('users','users.id','customer.user_id')
          ->where('customer.id',$id)
          ->first();
        return view('admin.customer.update',[
            'data' => $data
        ]);
    }

    public function update(Request $request)
    {
        $model = Customer::find($request->customer_id);
        $request->validate([
    		'full_name'=>'required',
    		//'last_name'=>'required',
    		'dob'=>'required',
    		'email'=>['required',Rule::unique('users')->ignore($model->user_id)],
    		'mobile'=>['required',Rule::unique('users')->ignore($model->user_id)],
    		'address'=>'required',
    		'city'=>'required',
    		'state'=>'required',
    		'country'=>'required',
    		'city'=>'required',
    		'year_of_experience'=>'required',
    		'bio'=>'required'
    	]);
    	
    	$model->full_name=$request->full_name;
    	 
    	$model->dob=$request->dob;
    	 
    	$model->alt_mobile=$request->alt_mobile;
    	$model->year_of_experience=$request->year_of_experience;
    	$model->bio=$request->bio;
    	$model->address=$request->address;
    	$model->lat=$request->lat;
        $model->lng=$request->lng; 
    	$model->street=$request->street;
    	$model->city=$request->city;
    	$model->state=$request->state;
    	$model->country=$request->country;
    	$model->zip=$request->zip;
		$model->updated_by=\Auth::user()->id;
		$model->updated_at=date('Y-m-d H:i:s');

		$path=public_path().'/uploads/customer';
		

         

        
		if($model->save())
        {
            $userdata=User::find($model->user_id);
            if($request->hasFile('profile_image'))
            {
                $path=public_path().'/uploads/customer';
                $files=$request->file('profile_image');
                $name=time().$files->getClientOriginalName();
                $files->move($path,$name);
                $userdata->profile_image=$name;
            }
            $userdata->email=$request->email;
            $userdata->mobile=$request->mobile;
            $userdata->save();
            
        }        
        echo "Updated Successfully";
    }

    public function destroy(Request $request)
    {
        $data=Customer::find($request->id);
        User::find($data->user_id);
        $data->delete();
        echo 1;
    }
    public function customerRegistration($customer_id)
	{
		$user=User::find($customer_id);
		$data=Personal::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->first();
        $user=User::find($customer_id);
        $preffered_hours=PrefferedHours::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->first();
        $qualifications=Qualifications::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->get();
        $training_and_development=TrainingAndDevelopment::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->get();
        $employment_history=EmploymentHistory::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->get();
		$disqualifications=Disqualifications::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->first();
		$referers=Referers::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->get();
		$bank_details=BankDetails::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->first();
		$declaration=Declaration::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->first();
		$questionnaire=Questionnaire::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->first();
		$self_declaration=SelfDeclaration::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->first();
        return view('admin.customer.registration',[
             'user'=>$user,
            'customer_id'=>$customer_id,
            'data'=>$data,
            'preffered_hours'=>$preffered_hours,
            'qualifications'=>$qualifications,
            'training_and_development'=>$training_and_development,
            'employment_history'=>$employment_history,
			'disqualifications'=>$disqualifications,
			'referers'=>$referers,
			'bank_details'=>$bank_details,
			'declaration'=>$declaration,
			'questionnaire'=>$questionnaire,
			'self_declaration'=>$self_declaration,


		]);
	}
    public function customerRegistrationStatus(Request $request)
    {
        $customer_id=$request->id;
    	$user=User::find($request->id);
    	$user->approve_status=$request->status;
    	if($request->status == 'disapproved')
    	{
    	    $user->approve_remarks=$request->remarks;
    	    $user->last_step=null;
    	    $user->is_resubmit='no';
    	    if($request->has('resubmit') && $request->resubmit == 'yes')
    	    {
    	        $user->is_resubmit='yes';
    	        $data=Personal::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->first();
    	        $data->is_disapproved='yes';
    	        $data->save();
                $preffered_hours=PrefferedHours::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->first();
                $preffered_hours->is_disapproved='yes';
        		$preffered_hours->save();
                $qualifications=Qualifications::where('customer_id',$customer_id)->update(['is_disapproved'=>'yes']);
                $training_and_development=TrainingAndDevelopment::where('customer_id',$customer_id)->update(['is_disapproved'=>'yes']);
                $employment_history=EmploymentHistory::where('customer_id',$customer_id)->update(['is_disapproved'=>'yes']);
        		$disqualifications=Disqualifications::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->first();
        		$disqualifications->is_disapproved='yes';
        		$disqualifications->save();
        		$referers=Referers::where('customer_id',$customer_id)->update(['is_disapproved'=>'yes']);
        		$bank_details=BankDetails::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->first();
        		$bank_details->is_disapproved='yes';
        		$bank_details->save();
        		$declaration=Declaration::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->first();
        		$declaration->is_disapproved='yes';
        		$declaration->save();
        		$questionnaire=Questionnaire::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->first();
        		$questionnaire->is_disapproved='yes';
        		$questionnaire->save();
        		$self_declaration=SelfDeclaration::where('customer_id',$customer_id)->where('is_disapproved','!=','yes')->first();
        		$self_declaration->is_disapproved='yes';
        		$self_declaration->save();
    	    }
    	}
    	$user->save();
		SendMail::sendMail($user->email,[
		'template'=>'approve_status',
		'user'=>$user
		]);
    	echo 1;
    }
}


?>