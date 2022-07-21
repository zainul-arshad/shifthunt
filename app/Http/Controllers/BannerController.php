<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use DataTables;


class BannerController extends Controller
{
    public function index()
    {
        return view('admin.banner.index');
    }

    public function filter_banner(Request $request)
    {
        if($request->ajax()){
          $data = Banner::select('banner.*');
           if($request->has('filter_status') && ($request->filter_status!='all'))
          {
            $data=$data->where('status',$request->filter_status);
          }
        
         
          $data=$data;
          //$data=$data->get();  
          return Datatables::of($data)

                ->addIndexColumn()
                ->addColumn('banner', function($data) { return '<img src="'.asset('public/images/banner').'/'.$data->banner.'" style="width:50px;height:50px;">'; })
                ->addColumn('status', function($data) {
                if($data->status=='yes')
                {
                    return '<span class="badge bg-primary ms-auto">YES</span>';
                }else{
                    return '<span class="badge bg-danger ms-auto">NO</span>';

                }  
                })
                ->rawColumns(['banner','status','action'])
                ->addColumn('action',function($data){
                    
                   return  '<button class="btn bg-danger btn-sm text-white rounded-circle deleteBtn" type="button" value="'.$data->id.'"><i class="fa fa-trash"></i></button>';
                         
                })
                ->setRowId(function ($data) {
                     return "row_".$data->id;
               })
                ->make(true);

            
        }
    }
    public function create()
    {
        return view('admin.banner.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'banner'=>'required',
            'status'=>'required'
        ]);
        $model = new Banner();
        $model->status=$request->status;
        $model->created_by=\Auth::user()->id;
        $model->created_at=date('Y-m-d H:i:s');

        $path=public_path().'/images/banner';
        

        if($request->hasFile('banner'))
        {
            $files=$request->file('banner');
            $name=time().$files->getClientOriginalName();
            $files->move($path,$name);
            $model->banner=$name;
        }

        
       $model->save();
    
        echo "Created Successfully";
    }
    
    public function destroy(Request $request)
    {
        $data=Banner::find($request->id)->delete();
        echo 1;
    }
}


?>