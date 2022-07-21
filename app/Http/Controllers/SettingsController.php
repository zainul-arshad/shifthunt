<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Settings;
use DataTables;

class SettingsController extends Controller
{
	public function index()
	{
		return view('admin.settings.index');
	}

	public function filter_settings(Request $request)
    {
        if($request->ajax()){
          $data = Settings::select('settings.*');
          if($request->has('label') && !empty($request->label))
          {
            $data=$data->where('label','like','%'.$request->label.'%');
          }
         
        
         
         
          $data=$data->get();  
          return Datatables::of($data)

                ->addIndexColumn()
                ->addColumn('label', function($data) { return $data->label; })
                ->addColumn('value', function($data) { return $data->value; })
                ->addColumn('action',function($data){
                    $edit=url('settings/'.$data->id.'/edit');
                      return '<a href="'.$edit.'" class="btn bg-primary btn-sm text-white rounded-circle"><i class="fa fa-pencil"></i></a>';
                         
                })
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
    }

    public function edit($id)
    {
        $data=Settings::find($id);
        return view('admin.settings.update',[
            'data' => $data
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'label'=>'required',
            'value'=>'required'
        ]);
        $model = Settings::find($request->settings_id);
        $model->label = $request->label;
        $model->value =$request->value;
        $model->save();
        echo "Updated Successfully";
    }
  
}


?>