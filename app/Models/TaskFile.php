<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TaskFile extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'draft_token',
        'original_name',
        'path',
        'size',
        'mime',
    ];

    protected $appends = ['url'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): string
    {
        // диск "public" должен быть настроен (стандарт Laravel)
        return Storage::disk('public')->url($this->path);
        // либо так, если используешь симлинк storage: return asset('storage/'.$this->path);
    }
}
