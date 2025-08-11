<?php

namespace App\Http\Controllers;

use App\Models\{Task, TaskTimer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimerController extends Controller
{
    // Старт таймера (поддержка ручного старта)
    public function start(Request $r, Task $task)
    {
        // Останавливаем предыдущие активные таймеры пользователя
        TaskTimer::where('user_id',Auth::id())->whereNull('stopped_at')->get()->each->stopNow();

        $manual = (bool) $r->boolean('manual');
        $startedAt = $manual && $r->filled('started_at') ? $r->date('started_at') : now();

        TaskTimer::create([
            'task_id'    => $task->id,
            'user_id'    => Auth::id(),
            'started_at' => $startedAt,
            'manual'     => $manual,
        ]);

        return back()->with('ok','Таймер запущен');
    }

    // Стоп таймера (поддержка ручного стопа)
    public function stop(Request $r, Task $task)
    {
        $manual = (bool) $r->boolean('manual');

        $timer = TaskTimer::where('user_id',Auth::id())
            ->where('task_id',$task->id)
            ->whereNull('stopped_at')
            ->latest()
            ->first();

        if (!$timer && $manual && $r->filled('started_at')) {
            // Если старт был "в прошлом" и активного таймера нет — создаём и сразу стопим
            $timer = TaskTimer::create([
                'task_id'    => $task->id,
                'user_id'    => Auth::id(),
                'started_at' => $r->date('started_at'),
                'manual'     => true,
            ]);
        }

        if ($timer) {
            if ($manual && $r->filled('stopped_at')) {
                $timer->stopped_at  = $r->date('stopped_at');
                $timer->duration_sec = (int) max(0, $timer->started_at->diffInSeconds($timer->stopped_at));
                $timer->save();
            } else {
                $timer->stopNow();
            }
        }

        return back()->with('ok','Таймер остановлен');
    }

    // Для мини-виджета таймера
    public function active()
    {
        $t = TaskTimer::where('user_id',Auth::id())->whereNull('stopped_at')->latest()->first();

        return response()->json([
            'active'      => (bool) $t,
            'task_id'     => $t?->task_id,
            'started_at'  => $t?->started_at?->toIso8601String(),
        ]);
    }
}
