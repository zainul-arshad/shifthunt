<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
Class Positions extends Model{

     use SoftDeletes;

     protected $table = 'positions';
     protected $primaryKey = 'id';

     public $timestamps = true;

     protected $dates = ['deleted_at'];
	public static function getName($id)
	{
		$data=Self::find($id);
		if(!empty($data))
		{
			return $data->position_title;
		}
		else
		{
			return '';
		}
	}

   
}

?>