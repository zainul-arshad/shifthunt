<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\User;
use \App\Models\Customer;
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
class FormController extends Controller
{
    public function form($customer_id)
    {
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
        return view('front.form.index',[
            'last_step'=>$user->last_step,
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
    public function store(Request $request)
    {
		$data=Personal::where('customer_id',$request->customer_id)->where('is_disapproved','!=','yes')->first();
		if(!isset($data))
		{
			$data=new Personal();
			$data->customer_id=$request->customer_id;
		}
		$data->vacancy_title=$request->vacancy_title; 
		$data->about_vacancy=$request->about_vacancy; 
		$data->save();
        if($request->step == '1')
        {
            $data=Personal::where('customer_id',$request->customer_id)->where('is_disapproved','!=','yes')->first();
            if(!isset($data))
            {
                $data=new Personal();
            }
            $data->customer_id=$request->customer_id; 
            $data->first_name=$request->first_name; 
            $data->last_name=$request->last_name; 
            $data->email=$request->email; 
            $data->address=$request->address; 
            $data->home_contact_number=$request->home_contact_number; 
            $data->daytime_contact_number=$request->daytime_contact_number; 
            $data->postcode=$request->postcode; 
            $data->dob=$request->dob; 
            $data->national_insurance_no=$request->national_insurance_no; 
            $data->valid_driving_licence=$request->valid_driving_licence;
            $data->vacancy_title=$request->vacancy_title; 
            $data->about_vacancy=$request->about_vacancy; 
            if($data->save())
            {
                $user=User::find($request->customer_id);
                $user->last_step=1;
                $user->save();
            } 
            echo 1;
            
        }
        if($request->step == '2')
        {
            $preffered_hours=PrefferedHours::where('customer_id',$request->customer_id)->where('is_disapproved','!=','yes')->first();
            if(!isset($preffered_hours))
            {
                $preffered_hours=new PrefferedHours();
            }
            $preffered_hours->customer_id=$request->customer_id;
            $preffered_hours->mon_day=$request->has('mon_day') ? 'yes' : 'no';
            $preffered_hours->tue_day=$request->has('tue_day') ? 'yes' : 'no';
            $preffered_hours->wed_day=$request->has('wed_day') ? 'yes' : 'no';
            $preffered_hours->thu_day=$request->has('thu_day') ? 'yes' : 'no';
            $preffered_hours->fri_day=$request->has('fri_day') ? 'yes' : 'no';
            $preffered_hours->sat_day=$request->has('sat_day') ? 'yes' : 'no';
            $preffered_hours->sun_day=$request->has('sun_day') ? 'yes' : 'no';

            $preffered_hours->mon_night=$request->has('mon_night') ? 'yes' : 'no';
            $preffered_hours->tue_night=$request->has('tue_night') ? 'yes' : 'no';
            $preffered_hours->wed_night=$request->has('wed_night') ? 'yes' : 'no';
            $preffered_hours->thu_night=$request->has('thu_night') ? 'yes' : 'no';
            $preffered_hours->fri_night=$request->has('fri_night') ? 'yes' : 'no';
            $preffered_hours->sat_night=$request->has('sat_night') ? 'yes' : 'no';
            $preffered_hours->sun_night=$request->has('sun_night') ? 'yes' : 'no';
            if($preffered_hours->save())
            {
                $user=User::find($request->customer_id);
                $user->last_step=2;
                $user->save();
            } 
            echo 1;
        }
        if($request->step == '3')
        {
            if($request->has('qualification_title') && count($request->qualification_title) != 0 )
            {
                for($i=0;$i < count($request->qualification_title);$i++)
                {
                   $qualifications=new Qualifications(); 
                   $qualifications->customer_id=$request->customer_id;
                   $qualifications->qualification=$request->qualification_title[$i];
                   $qualifications->institute_name=$request->institute[$i];
                   $qualifications->study_dates=$request->study_dates[$i];
                   $qualifications->date_obtained=$request->date_obtained[$i];
                   $qualifications->qualification_and_grade=$request->qualification_and_grade[$i];
                   $qualifications->save();

                }
            }
            if($request->has('training_course') && count($request->training_course) != 0 )
            {
                for($i=0;$i < count($request->training_course);$i++)
                {
                    $training_and_development=new TrainingAndDevelopment();
                    $training_and_development->customer_id=$request->customer_id;
                    $training_and_development->training_course=$request->training_course[$i];
                    $training_and_development->course_details=$request->course_details[$i];
                    $training_and_development->save();
                }
            } 

            //edit

            if($request->has('qualification_id') && count($request->qualification_id) != 0 )
            {
                for($i=0;$i < count($request->qualification_id);$i++)
                {
                   $qualifications=Qualifications::find($request->qualification_id[$i]); 
                   $qualifications->qualification=$request->qualification_title_edit[$i];
                   $qualifications->institute_name=$request->institute_edit[$i];
                   $qualifications->study_dates=$request->study_dates_edit[$i];
                   $qualifications->date_obtained=$request->date_obtained_edit[$i];
                   $qualifications->qualification_and_grade=$request->qualification_and_grade_edit[$i];
                   $qualifications->save();

                }
            }
            if($request->has('training_id') && count($request->training_id) != 0 )
            {
                for($i=0;$i < count($request->training_id);$i++)
                {
                    $training_and_development=TrainingAndDevelopment::find($request->training_id[$i]);
                    $training_and_development->training_course=$request->training_course_edit[$i];
                    $training_and_development->course_details=$request->course_details_edit[$i];
                    $training_and_development->save();
                }
            }

            $data=Personal::where('customer_id',$request->customer_id)->where('is_disapproved','!=','yes')->first();
            $data->current_membership_pro_body=$request->current_membership_pro_body;
            $data->save();
            $user=User::find($request->customer_id);
            $user->last_step=3;
            $user->save();
            echo 1;
        }
        if($request->step == '4')
        {
            if($request->has('name_of_employer') && count($request->name_of_employer) != 0 )
            {
                for($i=0;$i < count($request->name_of_employer);$i++)
                {
                   $employment_history=new EmploymentHistory(); 
                   $employment_history->customer_id=$request->customer_id;
                   $employment_history->name_of_employer=$request->name_of_employer[$i];
                   $employment_history->address_of_employer=$request->address_of_employer[$i];
                   $employment_history->pincode_of_employer=$request->pincode_of_employer[$i];
                   $employment_history->position_held=$request->position_held[$i];
                   $employment_history->date_started=$request->date_started[$i];
                   $employment_history->leaving_date=$request->leaving_date[$i];
                   $employment_history->reason_for_leaving=$request->reason_for_leaving[$i];
                   $employment_history->name_of_line_manager=$request->name_of_line_manager[$i];
                   $employment_history->description_of_duty=$request->description_of_duty[$i];
                   $employment_history->save();
                   
                }
               
            }
			if($request->has('employment_history_id') && count($request->employment_history_id) != 0 )
            {
                for($i=0;$i < count($request->employment_history_id);$i++)
                {
                   $employment_history=EmploymentHistory::find($request->employment_history_id[$i]); 
                   $employment_history->customer_id=$request->customer_id;
                   $employment_history->name_of_employer=$request->name_of_employer_edit[$i];
                   $employment_history->address_of_employer=$request->address_of_employer_edit[$i];
                   $employment_history->pincode_of_employer=$request->pincode_of_employer_edit[$i];
                   $employment_history->position_held=$request->position_held_edit[$i];
                   $employment_history->date_started=$request->date_started_edit[$i];
                   $employment_history->leaving_date=$request->leaving_date_edit[$i];
                   $employment_history->reason_for_leaving=$request->reason_for_leaving_edit[$i];
                   $employment_history->name_of_line_manager=$request->name_of_line_manager_edit[$i];
                   $employment_history->description_of_duty=$request->description_of_duty_edit[$i];
                   $employment_history->save();
                   
                }
               
            }
			$user=User::find($request->customer_id);
			$user->last_step=4;
			$user->save();
			echo 1;
        }
		if($request->step == '5')
        {
			$disqualifications=Disqualifications::where('customer_id',$request->customer_id)->where('is_disapproved','!=','yes')->first();
			if(!isset($disqualifications))
			{
				$disqualifications=new Disqualifications();
				$disqualifications->customer_id=$request->customer_id;
			}
			$disqualifications->court_conviction=$request->court_conviction ;
			$disqualifications->aware_of_police_enquiry=$request->aware_of_police_enquiry ;
			$disqualifications->is_suspended=$request->is_suspended ;
			$disqualifications->reason_for_court_conviction=$request->court_conviction == 'yes' ? $request->reason_for_court_conviction : '';
			$disqualifications->reason_for_police_enquiry=$request->aware_of_police_enquiry == 'yes' ? $request->reason_for_police_enquiry : '';
			$disqualifications->reason_for_suspension=$request->is_suspended == 'yes' ? $request->reason_for_suspension : '';
			$disqualifications->notice_period_or_start=$request->notice_period_or_start;
			$disqualifications->save();
			$user=User::find($request->customer_id);
			$user->last_step=5;
			$user->save();
			echo 1;

		}
		if($request->step == '6')
		{
			for($i=0;$i<count($request->name_of_referee);$i++)
			{
				$referers=Referers::where('customer_id',$request->customer_id)->where('is_disapproved','!=','yes')->skip($i)->take($i+1)->get();
				if(count($referers) == 0)
				{
					$referers=new Referers();
				    $referers->customer_id=$request->customer_id;
				}else{
					$referers=Referers::find($referers[0]->id);
				}
				
				$referers->name_of_referee=$request->name_of_referee[$i];
				$referers->referee_relationship=$request->referee_relationship[$i];
				$referers->referee_address=$request->referee_address[$i];
				$referers->referee_post_code=$request->referee_post_code[$i];
				$referers->referee_tel=$request->referee_tel[$i];
				$referers->referee_email=$request->referee_email[$i];
				$referers->save();
			}	
			$user=User::find($request->customer_id);
			$user->last_step=6;
			$user->save();
			echo 1;
		}
		if($request->step == '7')
		{
			$bank_details=BankDetails::where('customer_id',$request->customer_id)->where('is_disapproved','!=','yes')->first();
			if(!isset($bank_details))
			{
				$bank_details=new BankDetails();
				$bank_details->customer_id=$request->customer_id;
			}
			$bank_details->work_type=$request->work_type;
			$bank_details->name_of_bank=$request->name_of_bank;
			$bank_details->address_of_bank=$request->address_of_bank;
			$bank_details->postcode_of_bank=$request->postcode_of_bank;
			$bank_details->name_of_accountholder=$request->name_of_accountholder;
			$bank_details->account_no=$request->account_no;
			$bank_details->sort_code=$request->sort_code;
			$bank_details->p45=$request->p45;
			$bank_details->p46=$request->p46;
			if($request->hasFile('certificate_of_incorporation'))
			{
				$file=$request->file('certificate_of_incorporation');
				$filename=time().'.'.$file->getClientOriginalExtension();
				$file->move(public_path('uploads/bank_details/coi/'),$filename);
				$bank_details->certificate_of_incorporation='uploads/bank_details/coi/'.$filename;
			}
			if($request->hasFile('vat_registration_certificate'))
			{
				$file=$request->file('vat_registration_certificate');
				$filename=time().'.'.$file->getClientOriginalExtension();
				$file->move(public_path('uploads/bank_details/vat/'),$filename);
				$bank_details->vat_registration_certificate='uploads/bank_details/vat/'.$filename;
			}
			$bank_details->save();
			$user=User::find($request->customer_id);
			$user->last_step=7;
			$user->save();
			echo 1;

		}
		if($request->step == '8')
		{
			$declaration=Declaration::where('customer_id',$request->customer_id)->where('is_disapproved','!=','yes')->first();
			if(!isset($declaration))
			{
				$declaration=new Declaration();
				$declaration->customer_id=$request->customer_id;
			}
			$declaration->date=$request->date;
			if($request->signature_type == 'file' && $request->hasFile('signaturef'))
			{
				$file=$request->file('signaturef');
				$filename=$request->customer_id.'-'.time().'.'.$file->getClientOriginalExtension();
				$file->move(public_path('uploads/signature/'),$filename);
				$declaration->signature='uploads/signature/'.$filename;
			}
			if($request->signature_type == 'sign' && $request->signatureh != '')
			{
				//base64 to image laravel
				$image = $request->signatureh;
				$image = str_replace('data:image/png;base64,', '', $image);
				$image = str_replace(' ', '+', $image);
				$imageName = $request->customer_id.'-'.time().'.'.'png';
				$path = public_path('uploads/signature/').$imageName;
				\File::put($path, base64_decode($image));
				$declaration->signature='uploads/signature/'.$imageName;
			}
			$declaration->save();
			$user=User::find($request->customer_id);
			$user->last_step=8;
			$user->save();
			echo 1;
		}
		if($request->step == '9')
		{
			$declaration=Declaration::where('customer_id',$request->customer_id)->where('is_disapproved','!=','yes')->first();
			if(!isset($declaration))
			{
				$declaration=new Declaration();
				$declaration->customer_id=$request->customer_id;
			}
			$declaration->declare_date=$request->declare_date;
		  	$declaration->print_name=$request->print_name;
			if($request->signature_type1 == 'file1' && $request->hasFile('signature1f'))
			{
				$file=$request->file('signature1f');
				$filename=$request->customer_id.'-'.time().'.'.$file->getClientOriginalExtension();
				$file->move(public_path('uploads/signature1/'),$filename);
				$declaration->signature1='uploads/signature1/'.$filename;
			}
			if($request->signature_type1 == 'sign1' && $request->signature1h != '')
			{
				//base64 to image laravel
				$image = $request->signature1h;
				$image = str_replace('data:image/png;base64,', '', $image);
				$image = str_replace(' ', '+', $image);
				$imageName = $request->customer_id.'-'.time().'.'.'png';
				$path = public_path('uploads/signature1/').$imageName;
				\File::put($path, base64_decode($image));
				$declaration->signature1='uploads/signature1/'.$imageName;
			}
			$declaration->save();
			$user=User::find($request->customer_id);
			$user->last_step=9;
			$user->save();
			echo 1;

		}
		

    } 
	public function storeLastForms(Request $request)
	{
		if($request->step == '10')
		{
			$questionnaire=Questionnaire::where('customer_id',$request->customer_id)->where('is_disapproved','!=','yes')->first();
			if(!isset($questionnaire))
			{
				$questionnaire=new Questionnaire();
				$questionnaire->customer_id=$request->customer_id;
			}
			$questionnaire->sex=$request->sex;
			$questionnaire->dob=$request->dob1;
			$questionnaire->marital_status=$request->marital_status;
			$questionnaire->marital_status_other=$request->marital_status == 'other' ? $request->marital_status_other : '';
			$questionnaire->disability=$request->disability;
			$questionnaire->ethnics=$request->ethnics;
			if(in_array($request->ethnics,['White Other','Black Other','Other']))
			{
				$questionnaire->ethnics_other=$request->ethnics_other;
			}
			if($questionnaire->save())
			{
				$user=User::find($request->customer_id);
				$user->last_step=10;
				$user->save();
				echo 1;
			}
			else
			{
				echo 0;
			}
		}
		if($request->step == '11')
		{
			$selfDeclaration=SelfDeclaration::where('customer_id',$request->customer_id)->where('is_disapproved','!=','yes')->first();
			if(!isset($selfDeclaration))
			{
				$selfDeclaration=new SelfDeclaration();
				$selfDeclaration->customer_id=$request->customer_id;
			}
			$selfDeclaration->affect_your_work=$request->affect_your_work;
			$selfDeclaration->affect_your_work_details=$request->affect_your_work == 'yes' ? $request->affect_your_work_details : '';
			$selfDeclaration->caused_at_work=$request->caused_at_work;
			$selfDeclaration->caused_at_work_details=$request->caused_at_work == 'yes' ? $request->caused_at_work_details : '';
			$selfDeclaration->medical_investigation=$request->medical_investigation;
			$selfDeclaration->medical_investigation_details=$request->medical_investigation == 'yes' ? $request->medical_investigation_details : '';
			$selfDeclaration->assistance_need=$request->assistance_need;
			$selfDeclaration->assistance_need_details=$request->assistance_need == 'yes' ? $request->assistance_need_details : '';

			$selfDeclaration->cough=$request->cough;
			$selfDeclaration->weight_loss=$request->weight_loss;
			$selfDeclaration->fever=$request->fever;
			$selfDeclaration->tb=$request->tb;
			$selfDeclaration->following_issue_details=$request->following_issue_details != '' ? $request->following_issue_details : '';

			$selfDeclaration->chickenpox=$request->chickenpox;
			$selfDeclaration->rubella=$request->rubella;
			$selfDeclaration->bcg_vaccination=$request->bcg_vaccination;
			$selfDeclaration->btest_5=$request->btest_5;
			$selfDeclaration->nursing_issue_details=$request->nursing_issue_details != '' ? $request->nursing_issue_details : '';

			$selfDeclaration->tetanus=$request->tetanus;
			$selfDeclaration->tetanus_date=$request->tetatus == 'yes' ? date('Y-m-d',strtotime($request->tetanus_date)) : null;
			$selfDeclaration->diptheria=$request->diptheria;
			$selfDeclaration->diptheria_date=$request->diptheria == 'yes' ? date('Y-m-d',strtotime($request->diptheria_date)) : null;
			$selfDeclaration->poliomyelitis=$request->poliomyelitis;
			$selfDeclaration->poliomyelitis_date=$request->poliomyelitis == 'yes' ? date('Y-m-d',strtotime($request->poliomyelitis_date)) : null;
			$selfDeclaration->hepatitis_a=$request->hepatitis_a;
			$selfDeclaration->hepatitis_a_date=$request->hepatitis_a == 'yes' ? date('Y-m-d',strtotime($request->hepatitis_a_date)) : null;
			$selfDeclaration->hepatitis_b=$request->hepatitis_b;
			$selfDeclaration->hepatitis_b_date=$request->hepatitis_b == 'yes' ? date('Y-m-d',strtotime($request->hepatitis_b_date)) : null;
			$selfDeclaration->rubella_gm=$request->rubella_gm;
			$selfDeclaration->rubella_date=$request->rubella_gm == 'yes' ? date('Y-m-d',strtotime($request->rubella_date)) : null;
			$selfDeclaration->varicella=$request->varicella;
			$selfDeclaration->varicella_date=$request->varicella == 'yes' ? date('Y-m-d',strtotime($request->varicella_date)) : null;
			$selfDeclaration->bcg=$request->bcg;
			$selfDeclaration->bcg_date=$request->bcg == 'yes' ? date('Y-m-d',strtotime($request->bcg_date)) : null;

			if($selfDeclaration->save())
			{
				$user=User::find($request->customer_id);
				$user->last_step=11;
				$user->save();
				echo 1;
			}
			else
			{
				echo 0;
			}
			 
		}
	}
    public function deleteQualification(Request $request)
    {
        $data=Qualifications::find($request->id)->delete();
        echo 1;
    }
    public function deleteTraining(Request $request)
    {
        $data=TrainingAndDevelopment::find($request->id)->delete();
        echo 1;
    }
    public function deleteHistory(Request $request)
    {
        $data=EmploymentHistory::find($request->id)->delete();
        echo 1;
    }
    public function lastStepModify(Request $request)
    {
            $step=$request->step - 1;
             
        	$user=User::find($request->id);
			$user->last_step=$step  <= 0 ? null : $step;
			$user->save();
			echo 1;
    }
}
