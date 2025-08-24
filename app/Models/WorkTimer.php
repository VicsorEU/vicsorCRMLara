<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class WorkTimer extends Model
{
    protected $fillable = [
        'user_id','task_id','subtask_id','title','started_at','stopped_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
    ];

    public function user()    { return $this->belongsTo(\App\Models\User::class); }
    public function task()    { return $this->belongsTo(\App\Models\Task::class); }
    public function subtask() { return $this->belongsTo(\App\Models\Subtask::class); }

    // duration_sec — вычисляемое поле (для удобства в API)
    protected function durationSec(): Attribute
    {
        return Attribute::get(function () {
            $start = $this->started_at?->getTimestamp();
            $stop  = ($this->stopped_at?->getTimestamp()) ?? now()->getTimestamp();
            if (!$start) return 0;
            if (!$this->stopped_at) return max(0, now()->getTimestamp() - $start);
            return max(0, $stop - $start);
        });
    }

    public function scopeRunning($q) { return $q->whereNull('stopped_at'); }
}
