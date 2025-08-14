<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\Auditable;


class Task extends Model
{
    use Auditable;

    use HasFactory;

    protected $fillable = [
        'board_id','column_id','title','details','due_at',
        'priority','type','assignee_id','steps',
    ];

    protected $casts = [
        'steps'  => 'array',
        'due_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if (empty($task->created_by)) {
                $task->created_by = Auth::id();
            }
        });
    }

    // ─── Связи ─────────────────────────────────────────────────────────────────
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assignee_id'); }
    public function board(): BelongsTo   { return $this->belongsTo(TaskBoard::class, 'board_id'); }
    public function column(): BelongsTo  { return $this->belongsTo(TaskColumn::class, 'column_id'); }

    public function files(): HasMany
    {
        return $this->hasMany(TaskFile::class, 'task_id')->orderBy('id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'task_id')->latest('id');
    }

    public function timers(): HasMany
    {
        return $this->hasMany(\App\Models\TaskTimer::class, 'task_id')
            ->latest('started_at');
    }

    public function getTotalSecondsAttribute(): int
    {
        return $this->timers->sum(
            fn($t) => (int)($t->duration_sec ?? ($t->stopped_at
                ? $t->started_at->diffInSeconds($t->stopped_at)
                : 0))
        );
    }

    public function activeTimer()
    {
        return $this->timers()->whereNull('stopped_at')->latest('id');
    }

}
