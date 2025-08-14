<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};

class Project extends Model
{
    protected $fillable = ['name','manager_id','start_date','note','created_by','department'];
    protected $casts = ['start_date' => 'date'];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function board(): HasOne
    {
        return $this->hasOne(TaskBoard::class);
    }
}
