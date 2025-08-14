<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\Auditable;


class TaskColumn extends Model
{
    use Auditable;

    protected $fillable = ['board_id', 'name', 'color', 'sort_order'];

    public function board(): BelongsTo
    {
        return $this->belongsTo(TaskBoard::class, 'board_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'column_id')->orderBy('card_order');
    }
}
