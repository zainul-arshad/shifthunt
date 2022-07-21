<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referers extends Model
{
    use HasFactory;
	protected $table = 'referers';
	public $timestamps = false;
}
