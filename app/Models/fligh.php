<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class fligh extends Model
{
    use HasFactory;

    protected $fillable =[
        'name',
        'date',
        'duration',
        'type',
        'customer_id'
    ];
}
