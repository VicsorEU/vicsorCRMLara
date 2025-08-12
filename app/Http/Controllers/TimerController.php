<?php

namespace App\Http\Controllers;

use App\Models\{Task, TaskTimer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class TimerController extends Controller
{
    public function start(Task $task)
    {
        $t = $task->timers()->create([
            'user_id'    => auth()->id(),
            'started_at' => now(),
        ]);

        if (request()->wantsJson()) {
            $task->load('timers.user');
            return response()->json([
                'ok'            => true,
                'timer'         => $t,
                'total_seconds' => $task->total_seconds,   // без «тика», тикаем на клиенте
            ]);
        }
        return back();
    }

    public function stop(Request $r, Task $task)
    {
        if ($r->boolean('manual')) {
            $t = $task->timers()->create([
                'user_id'    => auth()->id(),
                'started_at' => $r->date('started_at'),
                'stopped_at' => $r->date('stopped_at'),
            ]);
        } else {
            $t = $task->timers()->whereNull('stopped_at')->latest('id')->firstOrFail();
            $t->update(['stopped_at' => now()]);
        }

        $task->load('timers.user');

        if ($r->wantsJson()) {
            return response()->json([
                'ok'            => true,
                'row'           => view('tasks._timer_row', ['t' => $t])->render(),
                'total_seconds' => $task->total_seconds,
            ]);
        }
        return back();
    }

    public function active() // уже есть роут GET /timer/active
    {
        $t = \App\Models\TaskTimer::with('task')
            ->whereNull('stopped_at')
            ->where('user_id', auth()->id())
            ->latest('id')->first();

        return response()->json([
            'timer'         => $t ? [
                'id'         => $t->id,
                'task_id'    => $t->task_id,
                'task_title' => $t->task->title ?? ('Задача #'.$t->task_id),
                'started_at' => $t->started_at,
            ] : null,
        ]);
    }

    public function destroy(TaskTimer $timer, Request $request): JsonResponse
    {
        // (опционально) доступ только своему таймеру
        // if ($timer->user_id !== auth()->id()) {
        //     return response()->json(['message' => 'Forbidden'], 403);
        // }

        // Нельзя удалять «идущий» таймер
        if (is_null($timer->stopped_at)) {
            return response()->json(['message' => 'Сначала остановите таймер'], 422);
        }

        // посчитаем длительность, чтобы на фронте вычесть её из общего времени
        $duration = 0;
        try {
            if (method_exists($timer, 'getAttribute') && $timer->getAttribute('duration_sec') !== null) {
                $duration = (int) $timer->duration_sec;
            } elseif ($timer->started_at && $timer->stopped_at) {
                $start = $timer->started_at instanceof \Carbon\Carbon ? $timer->started_at : \Carbon\Carbon::parse($timer->started_at);
                $stop  = $timer->stopped_at instanceof \Carbon\Carbon ? $timer->stopped_at  : \Carbon\Carbon::parse($timer->stopped_at);
                $duration = max(0, $stop->diffInSeconds($start));
            }
        } catch (\Throwable $e) {
            // не критично, просто не вернём duration
            $duration = 0;
        }

        $id     = $timer->id;
        $taskId = $timer->task_id;

        $timer->delete();

        return response()->json([
            'message'       => 'ok',
            'deleted_id'    => $id,
            'task_id'       => $taskId,
            'duration_sec'  => $duration,
        ]);
    }

}
