<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
Class Banner extends Model{

     use SoftDeletes;

     protected $table = 'banner';
     protected $primaryKey = 'id';

     public $timestamps = false;

     protected $dates = ['deleted_at'];

   
}

?>