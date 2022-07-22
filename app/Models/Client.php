<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
Class Client extends Model{

     use SoftDeletes;

     protected $table = 'client';
     protected $primaryKey = 'id';

     public $timestamps = true;

     protected $dates = ['deleted_at'];
     protected $guarded = [];
	 public static function getCommission($id)
	 {
		$data=Self::where('user_id',$id)->first();
		if($data)
		{
			return $data->commission_percentage;
		}
		return 0;

	 }
   
}

?>