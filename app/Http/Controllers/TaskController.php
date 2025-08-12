<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class TaskController extends Controller
{
    // Никаких middleware() внутри контроллера — всё в routes/web.php

    public function store(Request $request)
    {
        $data = $request->validate([
            'board_id'   => ['required','integer'],
            'column_id'  => ['required','integer'],
            'title'      => ['required','string','max:255'],
            'details'    => ['nullable','string'],
            'due_at'     => ['nullable','date'],
            'priority'   => ['nullable','string'], // 'normal' по умолчанию зададим ниже
            'type'       => ['nullable','string'], // 'common' по умолчанию
            'assignee_id'=> ['nullable','integer'],
            'steps'      => ['nullable','array'],
        ]);

        // фикс not-null для created_by
        $data['created_by'] = Auth::id();

        // дефолты
        $data['priority'] = $data['priority'] ?? 'normal';
        $data['type']     = $data['type'] ?? 'common';

        if (!empty($data['due_at'])) {
            $data['due_at'] = Carbon::parse($data['due_at'])->toDateString();
        }

        // steps как JSON-массив
        $data['steps'] = $data['steps'] ?? [];

        $task = Task::create($data);

        return response()->json(['message' => 'ok', 'id' => $task->id]);
    }

    // Перетаскивание между колонками и переупорядочивание
    public function move(Request $request)
    {
        $payload = $request->validate([
            'task_id'   => ['required','integer','exists:tasks,id'],
            'to_column' => ['required','integer'],
            'new_order' => ['required','array'],
            'new_order.*' => ['integer'],
        ]);

        DB::transaction(function () use ($payload) {
            // перенос задачи в новую колонку
            Task::whereKey($payload['task_id'])->update([
                'column_id' => $payload['to_column'],
            ]);

            // если у вас есть поле для сортировки (position/order_index/sort) — раскомментируйте
            // и замените на реальное имя колонки.
            /*
            foreach ($payload['new_order'] as $index => $id) {
                Task::whereKey($id)->update(['position' => $index + 1]);
            }
            */
        });

        return response()->json(['message' => 'ok']);
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|string',
            'priority'    => 'required|string',
            'assignee_id' => 'nullable|exists:users,id',
            'details'     => 'nullable|string',
            'due_at'      => 'nullable|date',
            'steps'       => 'nullable', // придёт JSON-строкой
        ]);

        // steps может прийти строкой JSON -> в массив
        if (isset($data['steps']) && is_string($data['steps'])) {
            $decoded = json_decode($data['steps'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['steps'] = $decoded;
            } else {
                unset($data['steps']);
            }
        }

        $task->fill($data);
        $task->save();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'ok']);
        }
        return back()->with('ok', 'Сохранено');
    }

    public function destroy(Request $request, Task $task)
    {
        // удаляем прикреплённые файлы с диска
        foreach ($task->files as $f) {
            if ($f->path) {
                Storage::disk('public')->delete($f->path);
            }
            $f->delete();
        }

        $boardId = $task->board_id;
        $task->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'deleted']);
        }
        return redirect()->route('kanban.show', $boardId);
    }

    public function show(Task $task)
    {
        // Подгружаем нужные связи
        $task->load(['files','comments.user','assignee','creator','column','timers.user']);

        return view('tasks.show', compact('task'));
    }

}
