<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GPositions;
use App\Models\GRequirements;
use DataTables;
class GPositionsController extends Controller
{
    public function index()
    {
        return view('admin.global-positions.index');
    }

    public function filter_gpositions(Request $request)
    {
        if($request->ajax()){
          $data = GPositions::select('gpositions.*');
          if($request->has('title') && !empty($request->title))
          {
            $data=$data->where('position_title','like','%'.$request->title.'%');
          }
           if($request->has('is_active') && $request->is_active!='all')
          {
            $data=$data->where('is_active',$request->is_active);
          }
        
         
          //$data=$data->get();  
          return Datatables::of($data)

                ->addIndexColumn()
                ->addColumn('position_title', function($data) { return ucfirst($data->position_title); })
                ->addColumn('is_active', function($data) {
                if($data->is_active=='yes')
                {
                    return '<span class="badge bg-primary ms-auto">YES</span>';
                }else{
                    return '<span class="badge bg-danger ms-auto">NO</span>';

                }  
                })
                ->rawColumns(['position_title','is_active','action'])
                ->addColumn('action',function($data){
                    $edit=url('global-positions/'.$data->id.'/edit');
                    $view=url('global-positions/'.$data->id);
                   return  '<a class="btn bg-secondary btn-sm text-white rounded-circle" href="'.$view.'"><i class="fa fa-eye"></i></a>
                            <a href="'.$edit.'" class="btn bg-primary btn-sm text-white rounded-circle"><i class="fa fa-pencil"></i></a>

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
        return view('admin.global-positions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'position_title'=>'required',
            'is_active'=>'required',
            'description'=>'required',
        ]);
        $model = new GPositions();
        $model->position_title=$request->position_title;
        $model->is_active=$request->is_active;
        $model->position_desc=$request->description;
		$model->template=$request->template;
        $model->created_by=\Auth::user()->id;
        $model->updated_by=\Auth::user()->id;
        $model->created_at=date('Y-m-d H:i:s');
        $model->updated_at=date('Y-m-d H:i:s');

        $path=public_path().'/uploads/positions';
        

        if($request->hasFile('icon'))
        {
            $files=$request->file('icon');
            $name=time().$files->getClientOriginalName();
            $files->move($path,$name);
            $model->icon=$name;
        }

        
        if($model->save()){
            if($request->has('requirements') && count($request->requirements))
            {
                for($i=0;$i < count($request->requirements) ;$i++)
                {
                    $newData=new GRequirements();
                    $newData->name=$request->requirements[$i];
                    $newData->position_id=$model->id;
                    $newData->save(); 
                }
            }
            
        }
    
        echo "Created Successfully";
    }
     public function show($id)
    {
        $data=GPositions::find($id);
        $requirements=GRequirements::where('position_id',$id)->get();
        return view('admin.global-positions.view',[
            'data' => $data,
            'requirements'=>$requirements
        ]);
    }
    public function edit($id)
    {
        $data=GPositions::find($id);
        $requirements=GRequirements::where('position_id',$id)->get();
        return view('admin.global-positions.update',[
            'data' => $data,
            'requirements'=>$requirements
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'position_title'=>'required',
            'description'=>'required',
            'is_active'=>'required',
        ]);
        $model = GPositions::find($request->row_id);
        $model->position_title=$request->position_title;
        $model->is_active=$request->is_active;
        $model->position_desc=$request->description;
		$model->template=$request->template;
        $model->updated_by=\Auth::user()->id;
        $model->updated_at=date('Y-m-d H:i:s');


        $path=public_path().'/uploads/positions';
        

        if($request->hasFile('icon'))
        {
             $files=$request->file('icon');
            $name=time().$files->getClientOriginalName();
            $files->move($path,$name);
            $model->icon=$name;
        }

        
        if($model->save()){
            if($request->has('requirements_edit') && count($request->requirements_edit))
            {
                for($i=0;$i < count($request->requirements_edit) ;$i++)
                {
                    $editData=GRequirements::find($request->requirement_id[$i]);
                    $editData->name=$request->requirements_edit[$i];
                    $editData->position_id=$model->id;
                    $editData->save(); 
                }
            }

            if($request->has('requirements') && count($request->requirements))
            {
                for($i=0;$i < count($request->requirements) ;$i++)
                {
                    $newData=new GRequirements();
                    $newData->name=$request->requirements[$i];
                    $newData->position_id=$model->id;
                    $newData->save(); 
                }
            }
            
        }
        echo "Updated Successfully";
    }

    public function destroy(Request $request)
    {
        $data=GPositions::find($request->id)->delete();
        GRequirements::where('position_id',$request->id)->delete();
        echo 1;
    }
    public function deleteRequirement(Request $request)
    {
        $data=GRequirements::find($request->id);
        if($data->delete())
        {
            echo 1;
        }else{
            echo 0;
        }
    }
    public function deletePhoto(Request $request)
    {
        if($request->has('id') && $request->id != '')
        {
          $data=GPositions::find($request->id);
        } 
        
        $path=public_path('uploads/positions/'.$data->icon);
        $data->icon=null;
        if($data->save())
        {
             
        }
        echo 1;
    }
}
