<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Haruncpi\LaravelIdGenerator\IdGenerator;
Class Slot extends Model{

     use SoftDeletes;

     protected $table = 'slot';
     protected $primaryKey = 'id';

     public $timestamps = true;

     protected $dates = ['deleted_at'];

    public static function boot()
	{
      parent::boot();
      self::creating(function ($model) {
        $model->slot_number = IdGenerator::generate(['table' => 'slot','field'=>'slot_number', 'length' => 10, 'prefix' =>'SLT']);
    	});
	}   
}
?>




