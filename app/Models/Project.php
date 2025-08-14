<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use App\Models\Concerns\Auditable;


class Project extends Model
{
    use Auditable;

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
