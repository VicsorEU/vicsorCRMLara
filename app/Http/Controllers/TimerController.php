<?php

namespace App\Http\Controllers;

use App\Models\{Task, TaskTimer};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class TimerController extends Controller
{
    public function start(Request $r, Task $task)
    {
        $t = $task->timers()->create([
            'user_id'    => Auth::id(),
            'started_at' => now()->utc(), // всегда храним в UTC
        ]);

        if ($r->wantsJson()) {
            $task->load('timers.user');

            return response()->json([
                'ok'            => true,
                'timer'         => [
                    'id'           => $t->id,
                    'task_id'      => $t->task_id,
                    'user'         => ['id' => $t->user_id, 'name' => optional($t->user)->name],
                    'started_at'   => optional($t->started_at)->toIso8601String(), // UTC ISO
                    'stopped_at'   => optional($t->stopped_at)->toIso8601String(),  // UTC ISO
                ],
                'total_seconds' => $task->total_seconds,
            ]);
        }

        return back();
    }

    public function stop(Request $r, Task $task)
    {
        // хелпер для парсинга "локального" времени в UTC
        $parseLocalToUtc = function (?string $val, ?int $offsetMinutes, ?string $appTz) : ?Carbon {
            if (!$val) return null;

            // 1) Если пришёл ISO со смещением/суффиксом Z — просто парсим и в UTC
            if (preg_match('/[zZ]|[+\-]\d{2}:?\d{2}$/', $val)) {
                return Carbon::parse($val)->utc();
            }

            // 2) Если прислали offset (UTC - local), используем его.
            //    В JS: getTimezoneOffset() возвращает именно (UTC - local) в минутах.
            //    Значит UTC = local + offset.
            $fmt = (strlen($val) === 16) ? 'Y-m-d\TH:i' : 'Y-m-d\TH:i:s'; // с секундами или без
            if ($offsetMinutes !== null) {
                // трактуем строку как "наивную" в UTC и смещаем на offset
                $dt = Carbon::createFromFormat($fmt, $val, 'UTC');
                if ($dt === false) { // на всякий случай fallback
                    $dt = Carbon::parse($val, 'UTC');
                }
                return $dt->addMinutes($offsetMinutes)->utc();
            }

            // 3) Иначе считаем, что это локальное время приложения (app.timezone)
            $tz = $appTz ?: config('app.timezone') ?: 'UTC';
            $dt = Carbon::createFromFormat($fmt, $val, $tz);
            if ($dt === false) { // fallback
                $dt = Carbon::parse($val, $tz);
            }
            return $dt->utc();
        };

        if ($r->boolean('manual')) {
            $offset = $r->has('tz_offset') ? (int)$r->input('tz_offset') : null;
            $appTz  = config('app.timezone') ?: 'UTC';

            $startUtc = $parseLocalToUtc($r->input('started_at'), $offset, $appTz);
            $stopUtc  = $parseLocalToUtc($r->input('stopped_at'),  $offset, $appTz);

            if (!$startUtc || !$stopUtc || $stopUtc->lt($startUtc)) {
                return $r->wantsJson()
                    ? response()->json(['ok' => false, 'error' => 'Invalid interval'], 422)
                    : back();
            }

            $t = $task->timers()->create([
                'user_id'    => Auth::id(),
                'started_at' => $startUtc,
                'stopped_at' => $stopUtc,
            ]);
        } else {
            // мягкая остановка без 404, если активного нет
            $t = $task->timers()
                ->where('user_id', Auth::id())
                ->whereNull('stopped_at')
                ->latest('id')
                ->first();

            if (!$t) {
                return $r->wantsJson()
                    ? response()->json(['ok' => true, 'status' => 'noop'])
                    : back();
            }

            $t->update(['stopped_at' => now()->utc()]);
        }

        $task->load('timers.user');

        if ($r->wantsJson()) {
            return response()->json([
                'ok'            => true,
                // если используешь частичный Blade-ряд — оставлю для совместимости
                'row'           => view('tasks._timer_row', ['t' => $t])->render(),
                // обязательный блок с id и временами
                'timer'         => [
                    'id'           => $t->id,
                    'task_id'      => $t->task_id,
                    'user'         => ['id' => $t->user_id, 'name' => optional($t->user)->name],
                    'started_at'   => optional($t->started_at)->toIso8601String(), // UTC ISO
                    'stopped_at'   => optional($t->stopped_at)->toIso8601String(),  // UTC ISO
                ],
                'total_seconds' => $task->total_seconds,
            ]);
        }

        return back();
    }

    public function active(): JsonResponse
    {
        $t = TaskTimer::with(['task', 'user'])
            ->whereNull('stopped_at')
            ->where('user_id', Auth::id())
            ->latest('id')
            ->first();

        return response()->json([
            'timer' => $t ? [
                'id'         => $t->id,
                'task_id'    => $t->task_id,
                'task_title' => $t->task->title ?? ('Задача #'.$t->task_id),
                'started_at' => optional($t->started_at)->toIso8601String(), // UTC ISO
                'user'       => ['id' => $t->user_id, 'name' => optional($t->user)->name],
            ] : null,
        ]);
    }

    public function destroy(TaskTimer $timer, Request $request): JsonResponse
    {
        if (is_null($timer->stopped_at)) {
            return response()->json(['message' => 'Сначала остановите таймер'], 422);
        }

        $duration = 0;
        try {
            if ($timer->started_at && $timer->stopped_at) {
                $start = $timer->started_at instanceof Carbon ? $timer->started_at : Carbon::parse($timer->started_at);
                $stop  = $timer->stopped_at instanceof Carbon ? $timer->stopped_at  : Carbon::parse($timer->stopped_at);
                $duration = max(0, $stop->diffInSeconds($start));
            }
        } catch (\Throwable $e) {
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
