<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use App\Models\Concerns\Auditable;


class TaskBoard extends Model
{
    use Auditable;

    protected $fillable = ['project_id','name','created_by'];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }

    public function columns(): HasMany {
        return $this->hasMany(TaskColumn::class, 'board_id')->orderBy('sort_order');
    }

    public function tasks(): HasMany {
        return $this->hasMany(Task::class, 'board_id');
    }

    public function doneColumn(): ?TaskColumn
    {
        return $this->columns()->where('system_key', 'done')->first();
    }
}
