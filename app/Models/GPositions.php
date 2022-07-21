<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GPositions extends Model
{
    use HasFactory;
	 protected $table = 'gpositions';
     protected $primaryKey = 'id';

     public $timestamps = true;
}
