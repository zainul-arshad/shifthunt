<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
Class Settings extends Model{


     protected $table = 'settings';
     protected $primaryKey = 'id';

     public $timestamps = false;

    public static function getSettings($key)
     {
     		$data=Settings::where('label',$key)->first();
     		if($data)
     		{
     			return $data->value;
     		}
     		return '';
     }

   
}

?>