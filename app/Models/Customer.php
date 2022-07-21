<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
Class Customer extends Model{

     use SoftDeletes;

     protected $table = 'customer';
     protected $primaryKey = 'id';

     public $timestamps = true;

     protected $dates = ['deleted_at'];
     //protected $fillable = ['first_name','last_name','email','mobile','user_id','created_by','updated_by'];
     protected $guarded = []; 
}

?>