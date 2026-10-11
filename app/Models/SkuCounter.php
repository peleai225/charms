<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkuCounter extends Model
{
    protected $fillable = [
        'scope_key',
        'next_value',
    ];

    protected $casts = [
        'next_value' => 'integer',
    ];
}
