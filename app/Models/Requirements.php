<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;
Class Requirements extends Model{

     //use SoftDeletes;

     protected $table = 'requirements';
     protected $primaryKey = 'id';

     public $timestamps = false;

     //protected $dates = ['deleted_at'];

   
}

?>