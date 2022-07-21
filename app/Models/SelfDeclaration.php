<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SelfDeclaration extends Model
{
    use HasFactory;
	protected $table = 'self_declaration';
	public $timestamps = false;
}
