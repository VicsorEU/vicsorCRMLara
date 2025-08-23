<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use App\Models\Concerns\Auditable;
use App\Models\Settings\ProjectDepartment;



class Project extends Model
{
    use Auditable;

    protected $fillable = [    'name', 'manager_id', 'start_date', 'end_date', 'department', 'note', 'created_by',];
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function board(): HasOne
    {
        return $this->hasOne(TaskBoard::class);
    }

    public function departmentRef()
    {
        return $this->belongsTo(ProjectDepartment::class, 'department_id');
    }
}
