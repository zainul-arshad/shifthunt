<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
Class PreRegistration extends Model{

     use SoftDeletes;

     protected $table = 'pre_registration';
     protected $primaryKey = 'id';

     public $timestamps = false;

     protected $dates = ['deleted_at'];

   
}

?>