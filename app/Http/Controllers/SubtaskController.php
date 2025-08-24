<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    // Список подзадач конкретной задачи
    public function index(Task $task)
    {
        $items = $task->subtasks()
            ->with(['assignee:id,name'])
            ->orderBy('id')
            ->get();

        return response()->json([
            'items' => $items->map(fn(Subtask $s) => $this->serialize($s)),
        ]);
    }

    // Создание
    public function store(Request $r, Task $task)
    {
        $this->abortIfParentLocked($task);

        $data = $this->validateData($r);
        $data['task_id']   = $task->id;
        $data['created_by']= auth()->id();

        $s = Subtask::create($data);

        return response()->json(['item' => $this->serialize($s)], 201);
    }

    // Обновление
    public function update(Request $r, Subtask $subtask)
    {
        $this->abortIfParentLocked($subtask->task);

        $data = $this->validateData($r);
        $subtask->fill($data)->save();

        return response()->json(['item' => $this->serialize($subtask)]);
    }

    // Удаление
    public function destroy(Subtask $subtask)
    {
        $this->abortIfParentLocked($subtask->task);

        $subtask->delete();
        return response()->json(['ok' => true]);
    }

    // Завершить / вернуть в работу
    public function complete(Request $r, Subtask $subtask)
    {
        $this->abortIfParentLocked($subtask->task);

        $data = $r->validate(['completed' => 'required|boolean']);
        $subtask->completed = $data['completed'];
        $subtask->save();

        return response()->json(['item' => $this->serialize($subtask)]);
    }

    // Таймер старт
    public function timerStart(Subtask $subtask)
    {
        $this->abortIfParentLocked($subtask->task);

        if ($subtask->running_started_at) {
            return response()->json(['item' => $this->serialize($subtask)]);
        }

        $subtask->running_started_at = now();
        $subtask->save();

        return response()->json(['item' => $this->serialize($subtask)]);
    }

    // Таймер стоп
    public function timerStop(Subtask $subtask)
    {
        $this->abortIfParentLocked($subtask->task);

        if (!$subtask->running_started_at) {
            return response()->noContent(); // нечего останавливать
        }

        $start = Carbon::parse($subtask->running_started_at);
        $sec   = max(0, now()->diffInSeconds($start));

        $subtask->total_seconds += $sec;
        $subtask->running_started_at = null;
        $subtask->save();

        return response()->json(['item' => $this->serialize($subtask)]);
    }

    // --- helpers ---

    private function validateData(Request $r): array
    {
        $data = $r->validate([
            'title'       => ['required','string','max:255'],
            'details'     => ['nullable','string'],
            'due_at'      => ['nullable','date'],
            'due_to'      => ['nullable','date'],
            'assignee_id' => ['nullable','exists:users,id'],
            'priority_id' => ['nullable','integer','min:1'],
            'type_id'     => ['nullable','integer','min:1'],
        ]);

        if (!empty($data['due_at'])) $data['due_at'] = Carbon::parse($data['due_at'])->toDateString();
        if (!empty($data['due_to'])) $data['due_to'] = Carbon::parse($data['due_to'])->toDateString();

        return $data;
    }

    private function serialize(Subtask $s): array
    {
        return [
            'id'         => $s->id,
            'task_id'    => $s->task_id,
            'title'      => $s->title,
            'details'    => (string)($s->details ?? ''),
            'due_at'     => optional($s->due_at)->toDateString(),
            'due_to'     => optional($s->due_to)->toDateString(),
            'assignee_id'=> $s->assignee_id,
            'assignee'   => $s->assignee?->only('id','name'),
            'priority_id'=> $s->priority_id,
            'type_id'    => $s->type_id,
            'total_seconds' => (int)$s->total_seconds,
            'running_started_at' => optional($s->running_started_at)->toIso8601String(),
            'completed'  => (bool)$s->completed,
            'created_at' => optional($s->created_at)->toIso8601String(),
            'updated_at' => optional($s->updated_at)->toIso8601String(),
        ];
    }

    private function abortIfParentLocked(Task $task): void
    {
        if ($task->complete) {
            abort(response()->json(['message' => 'Задача завершена, редактирование запрещено'], 423));
        }
    }
}
