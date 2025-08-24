<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use App\Models\WorkTimer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkTimerController extends Controller
{
    // Активный таймер текущего пользователя
    public function active(Request $r)
    {
        $timer = WorkTimer::with(['task:id,title', 'subtask:id,task_id,title'])
            ->where('user_id', $r->user()->id)
            ->running()
            ->latest('started_at')
            ->first();

        return response()->json(['timer' => $timer ? $this->pack($timer) : null]);
    }

    // Запуск таймера (если уже идёт — 409)
    public function start(Request $r)
    {
        $data = $r->validate([
            'task_id'    => ['nullable','integer','exists:tasks,id'],
            'subtask_id' => ['nullable','integer','exists:subtasks,id'],
            'title'      => ['nullable','string','max:255'],
        ]);

        $userId = $r->user()->id;

        $already = WorkTimer::where('user_id', $userId)->running()->exists();
        if ($already) {
            return response()->json(['message' => 'У вас уже запущен таймер'], 409);
        }

        // Заголовок по умолчанию
        $title = $data['title'] ?? null;
        if (!$title && ($tid = $data['task_id'] ?? null)) {
            $title = optional(Task::select('title')->find($tid))->title;
        }
        if (!$title && ($sid = $data['subtask_id'] ?? null)) {
            $title = optional(Subtask::select('title')->find($sid))->title;
        }

        $timer = WorkTimer::create([
            'user_id'    => $userId,
            'task_id'    => $data['task_id']    ?? null,
            'subtask_id' => $data['subtask_id'] ?? null,
            'title'      => $title,
            'started_at' => now(),
        ]);

        return response()->json(['timer' => $this->pack($timer)], 201);
    }

    // Остановка активного таймера
    public function stop(Request $r)
    {
        $timer = WorkTimer::where('user_id',$r->user()->id)->running()->first();
        if (!$timer) return response()->noContent(); // 204

        $timer->stopped_at = now();
        $timer->save();

        return response()->json(['timer' => $this->pack($timer)]);
    }

    // Ручное добавление интервала
    public function store(Request $r)
    {
        $data = $r->validate([
            'task_id'    => ['nullable','integer','exists:tasks,id'],
            'subtask_id' => ['nullable','integer','exists:subtasks,id'],
            'title'      => ['nullable','string','max:255'],
            'started_at' => ['required','date'],
            'stopped_at' => ['required','date','after:started_at'],
        ]);

        $timer = WorkTimer::create([
            'user_id'    => $r->user()->id,
            'task_id'    => $data['task_id']    ?? null,
            'subtask_id' => $data['subtask_id'] ?? null,
            'title'      => $data['title']      ?? null,
            'started_at' => $data['started_at'],
            'stopped_at' => $data['stopped_at'],
        ]);

        return response()->json(['timer' => $this->pack($timer)], 201);
    }

    // Таблица интервалов по задаче/подзадаче
    public function index(Request $r)
    {
        // принимаем оба варианта ключей
        $taskId = (int)($r->input('task_id') ?? $r->input('task') ?? 0);
        $subId  = (int)($r->input('subtask_id') ?? $r->input('subtask') ?? 0);

        // по умолчанию подзадачи НЕ включаем
        $withSubs = (bool) $r->boolean('with_subtasks');

        abort_unless($taskId || $subId, 422, 'Нужно task_id или subtask_id');

        $q = \App\Models\WorkTimer::with('user:id,name');

        if ($subId) {
            // режим подзадачи — только её интервалы
            $q->where('subtask_id', $subId);
        } else {
            // режим задачи — по умолчанию только прямые интервалы задачи
            $q->where('task_id', $taskId)
                ->when(! $withSubs, fn($qq) => $qq->whereNull('subtask_id'));
        }

        $items = $q->orderBy('started_at', 'desc')->get();

        return response()->json([
            'items' => $items->map(fn($t) => $this->pack($t)),
        ]);
    }



    // Удаление интервала (только свой или админ)
    public function destroy(Request $r, WorkTimer $timer)
    {
        if ($timer->user_id !== $r->user()->id && ! $r->user()->can('delete timers')) {
            abort(403);
        }
        if ($timer->stopped_at === null) {
            abort(422, 'Нельзя удалить незавершённый интервал');
        }
        $timer->delete();
        return response()->json(['ok'=>true]);
    }

    // Сумма по задаче (+опция: включить подзадачи)
    public function summary(Request $r)
    {
        $data = $r->validate([
            'task_id'        => ['required','integer','exists:tasks,id'],
            'with_subtasks'  => ['nullable','boolean'],
        ]);

        $taskId = (int)$data['task_id'];
        $withSub = (bool)($data['with_subtasks'] ?? false);

        $total = WorkTimer::where('task_id',$taskId)->get()
            ->sum(fn($t)=>$t->duration_sec);

        if ($withSub) {
            $subIds = \App\Models\Subtask::where('task_id',$taskId)->pluck('id');
            $total += WorkTimer::whereIn('subtask_id', $subIds)->get()
                ->sum(fn($t)=>$t->duration_sec);
        }

        return response()->json(['total_seconds' => (int)$total]);
    }

    // ---- helpers ----
    private function pack(WorkTimer $t): array
    {
        return [
            'id' => $t->id,
            'user' => $t->user?->only('id','name'),
            'task_id' => $t->task_id,
            'subtask_id' => $t->subtask_id,
            'title' => $t->title ?? $t->task?->title ?? $t->subtask?->title,
            'started_at' => optional($t->started_at)->toIso8601String(),
            'stopped_at' => optional($t->stopped_at)->toIso8601String(),
            'duration_sec' => $t->duration_sec,
            'links' => [
                'task' => $t->task_id ? route('tasks.show', $t->task_id) : null,
                // для подзадачи — на страницу задачи с query, чтобы можно было открыть модалку
                'subtask' => $t->subtask_id && $t->subtask
                    ? route('tasks.show', $t->subtask->task_id).'?subtask_id='.$t->subtask_id
                    : null,
            ],
        ];
    }
}
