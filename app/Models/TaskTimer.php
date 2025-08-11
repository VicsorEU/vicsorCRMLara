<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskTimer extends Model
{
    protected $fillable = ['task_id','user_id','started_at','stopped_at','manual','duration_sec'];
    protected $casts    = ['started_at'=>'datetime','stopped_at'=>'datetime','manual'=>'bool'];

    public function task(): BelongsTo { return $this->belongsTo(Task::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function stopNow(): void
    {
        if ($this->stopped_at) return;
        $this->stopped_at  = now();
        $this->duration_sec = (int) max(0, $this->started_at->diffInSeconds($this->stopped_at));
        $this->save();
    }
}
