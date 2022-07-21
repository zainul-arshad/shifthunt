<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PreRegistration;
use DataTables;
use Mail;
use App\Models\Invite;
class PreRegistrationInfoController extends Controller
{
	public function index()
	{
		return view('admin.invitations.index');
	}

	public function filter_preregistration(Request $request)
    {
        if($request->ajax()){
          $data = PreRegistration::select('pre_registration.*');
          if($request->has('business_name') && !empty($request->business_name))
          {
            $data=$data->where('business_name','like','%'.$request->business_name.'%');
          }
          if($request->has('manager_name') && !empty($request->manager_name))
          {
            $data=$data->where('manager_name','like','%'.$request->manager_name.'%');
          }         
         
            
          return Datatables::of($data)

                ->addIndexColumn()
                ->addColumn('business_name', function($data) { return ucfirst($data->business_name); })
                ->addColumn('manager_name', function($data) { return ucfirst($data->manager_name); })
                ->addColumn('reg_no', function($data) { return $data->reg_no; })
                ->addColumn('action',function($data){
                     $view=url('invitations/'.$data->id);
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>';
                         
                })
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
    }


    public function show($id)
    {
      $data= PreRegistration::find($id);
      return view('admin.invitations.view',[
        'data'=>$data
      ]);
    }

    public function inviteSend(Request $request)
	{
		$model=new Invite();
		$model->name=$request->name;
		$model->email=$request->email;
		$model->created_at=date('Y-m-d H:i:s');
		$model->save();
		Mail::send('admin.invitations.email', ['data'=>$request], function($message)use($request) {
         $message->to($request->email, $request->name)
		->subject('Invitation from shifthunt');

      	});
		echo 1;
	}
	public function filterInviteSend(Request $request)
	{
		if($request->ajax()){
          $data = Invite::select('invite.*');
          if($request->has('name') && !empty($request->business_name))
          {
            $data=$data->where('name','like','%'.$request->business_name.'%');
          }
          if($request->has('name') && !empty($request->manager_name))
          {
            $data=$data->where('name','like','%'.$request->manager_name.'%');
          }         
           
          return Datatables::of($data)

                ->addIndexColumn()
                ->addColumn('date', function($data) { return date('Y M d h:i A',strtotime($data->created_at)); })
                 
                
                 
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
	}
	public function filterInviteApprove(Request $request)
	{
		if($request->ajax()){
          $data = PreRegistration::select('pre_registration.*','client.id as client_id')
		  ->leftJoin('users','users.email','pre_registration.manager_email')
		  ->leftJoin('client','client.user_id','users.id')
		  ->where('users.user_type','carer_home');
          if($request->has('name') && !empty($request->business_name))
          {
            $data=$data->where('pre_registration.business_name','like','%'.$request->business_name.'%');
          }
          if($request->has('name') && !empty($request->manager_name))
          {
            $data=$data->where('pre_registration.manager_name','like','%'.$request->manager_name.'%');
          }         
           
          return Datatables::of($data)

                ->addIndexColumn()
                ->addColumn('business_name', function($data) { return ucfirst($data->business_name); })
                ->addColumn('manager_name', function($data) { return ucfirst($data->manager_name); })
                ->addColumn('reg_no', function($data) { return $data->reg_no; })
                ->addColumn('action',function($data){
                     $view=url('client/'.$data->client_id);
                      return '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>';
                         
                })
                 
                
                 
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
	}
    
  
}


?>