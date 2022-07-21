<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Haruncpi\LaravelIdGenerator\IdGenerator;
Class Booking extends Model{

     //use SoftDeletes;

     protected $table = 'booking';
     protected $primaryKey = 'id';

     public $timestamps = true;

    // protected $dates = ['deleted_at'];
   	public static function boot()
	 {
      parent::boot();
      self::creating(function ($model) {
        $model->booking_no = IdGenerator::generate(['table' => 'booking','field'=>'booking_no', 'length' => 10, 'prefix' =>'BKG']);
    	});
	 }
   
   
}

?>