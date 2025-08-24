<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    protected $fillable = [
        'task_id','title','details','due_at','due_to',
        'assignee_id','priority_id','type_id',
        'total_seconds','running_started_at','completed','created_by',
    ];

    protected $casts = [
        'due_at' => 'date',
        'due_to' => 'date',
        'completed' => 'bool',
        'running_started_at' => 'datetime',
        'total_seconds' => 'int',
    ];

    public function task()      { return $this->belongsTo(Task::class); }
    public function assignee()  { return $this->belongsTo(User::class, 'assignee_id'); }

    public function isRunning(): bool
    {
        return !is_null($this->running_started_at);
    }
}
