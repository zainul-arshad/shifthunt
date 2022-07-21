<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GRequirements extends Model
{
    use HasFactory;
	 protected $table = 'grequirements';
     protected $primaryKey = 'id';

     public $timestamps = false;
}
