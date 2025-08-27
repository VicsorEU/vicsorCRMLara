<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessRole extends Model
{
    protected $fillable = ['name','slug','abilities','system'];

    protected $casts = [
        'abilities' => 'array',   // JSON → array
        'system'    => 'boolean',
    ];
}
