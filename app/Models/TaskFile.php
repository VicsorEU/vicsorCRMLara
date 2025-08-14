<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Models\Concerns\Auditable;


class TaskFile extends Model
{
    use Auditable;

    protected $fillable = [
        'task_id','original_name','path','size','user_id','draft_token','mime',
    ];

    // чтобы в JSON всегда был url
    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
