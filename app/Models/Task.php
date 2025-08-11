<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'board_id',
        'column_id',
        'title',
        'details',
        'due_at',
        'priority',
        'type',
        'assignee_id',
        'steps',
    ];

    protected $casts = [
        'due_at' => 'date',
        'steps'  => 'array',
    ];

    public function board(): BelongsTo   { return $this->belongsTo(TaskBoard::class, 'board_id'); }
    public function column(): BelongsTo  { return $this->belongsTo(TaskColumn::class, 'column_id'); }
    public function assignee(): BelongsTo{ return $this->belongsTo(User::class, 'assignee_id'); }
    public function files(): HasMany     { return $this->hasMany(TaskFile::class); }
}
