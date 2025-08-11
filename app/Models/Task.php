<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    use HasFactory;

    // Что можно массово заполнять из запроса
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

    // Касты
    protected $casts = [
        'steps'  => 'array',
        'due_at' => 'datetime', // хранится как timestamp/date; в JSON вернётся ISO
    ];

    /**
     * Гарантируем, что created_by всегда выставлен
     */
    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if (empty($task->created_by)) {
                $task->created_by = Auth::id(); // обязательна аутентификация
            }
        });
    }

    // Связи (по желанию, если используете)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function board()
    {
        return $this->belongsTo(TaskBoard::class, 'board_id');
    }

    public function column()
    {
        return $this->belongsTo(TaskColumn::class, 'column_id');
    }
}
