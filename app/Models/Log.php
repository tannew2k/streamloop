<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'model',
        'model_id',
        'action',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
