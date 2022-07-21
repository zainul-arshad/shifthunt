<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrefferedHours extends Model
{
    use HasFactory;
    protected $table='preffered_hours';
    public $timestamps=false;
}
