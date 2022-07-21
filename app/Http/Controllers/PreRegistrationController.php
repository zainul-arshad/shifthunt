<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PreRegistration;
use DataTables;


class PreRegistrationController extends Controller
{
	public function index()
	{
		return view('front.form.invitation');
	}
	public function regstore(Request $request)
	{
	
		$request->validate([
			'business_name'=>'required',
			'manager_name'=>'required',
			'manager_email'=>'required',
			'is_trading'=>'required',
			'business_address'=>'required',
			'telephone'=>'required',
			'website'=>'required',
			'reg_company_name'=>'required',
			'reg_no'=>'required',
			'director_name'=>'required',
			'services'=>'required',
			'no_of_bed'=>'required',
			'no_of_branches'=>'required',
			'cqc_no'=>'required',
			'no_of_locations'=>'required',
			'no_of_temp_staffs'=>'required',
			'location_names'=>'required',
			'staff_designation'=>'required',
			'credit_facility_needed'=>'required',

		]);
	
	$data= new PreRegistration();
	$data->business_name=$request->business_name;
	$data->manager_name=$request->manager_name;
	$data->manager_email=$request->manager_email;
	
	$data->is_trading=$request->is_trading;
	if($request->is_trading=='no')
	{
		$data->trading_startdate=$request->trading_startdate;
	}
	$data->business_address=$request->business_address;
	$data->telephone=$request->telephone;
	$data->website=$request->website;
	$data->reg_company_name=$request->reg_company_name;
	$data->reg_no=$request->reg_no;
	$data->director_name=$request->director_name;
	$data->services=$request->services;
	$data->no_of_bed=$request->no_of_bed;
	$data->no_of_branches=$request->no_of_branches;
	$data->cqc_no=$request->cqc_no;
	$data->no_of_locations=$request->no_of_locations;
	$data->no_of_temp_staffs=$request->no_of_temp_staffs;
	$data->location_names=$request->location_names;
	$data->staff_designation=implode(',',$request->staff_designation);
	$data->credit_facility_needed=$request->credit_facility_needed;
	if($request->credit_facility_needed=='yes')
	{
		$data->no_of_days=$request->no_of_days;
	}
	$data->created_at=date('Y-m-d H:i:s');
	$data->save();
	echo "Registered Successfully";
}
	
}
?>