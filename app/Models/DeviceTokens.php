<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceTokens extends Model
{
    use HasFactory;
	protected $table='device_tokens';
	public $timestamps=false;

}
