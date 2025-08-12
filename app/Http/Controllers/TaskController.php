<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskFile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /** Создание задачи (используется модалка на доске) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'board_id'    => ['required','integer'],
            'column_id'   => ['required','integer'],
            'title'       => ['required','string','max:255'],
            'details'     => ['nullable','string'],
            'due_at'      => ['nullable','date'],
            'priority'    => ['nullable','string','max:20'],
            'type'        => ['nullable','string','max:20'],
            'assignee_id' => ['nullable','integer'],
            'steps'       => ['nullable'],              // может прийти строкой JSON
            'draft_token' => ['nullable','string','max:100'],
        ]);

        // дефолты
        $data['priority']   = $data['priority']   ?? 'normal';
        $data['type']       = $data['type']       ?? 'common';
        $data['created_by'] = Auth::id();

        if (!empty($data['due_at'])) {
            $data['due_at'] = Carbon::parse($data['due_at'])->toDateString();
        }

        // steps: если пришло строкой — декодируем
        if (isset($data['steps']) && is_string($data['steps'])) {
            $steps = json_decode($data['steps'], true);
            $data['steps'] = json_last_error() === JSON_ERROR_NONE ? $steps : [];
        } elseif (!isset($data['steps'])) {
            $data['steps'] = [];
        }

        /** @var Task $task */
        $task = DB::transaction(function () use ($data, $request) {
            $task = Task::create($data);

            // Привязываем загруженные файлы по draft_token к созданной задаче
            if ($request->filled('draft_token')) {
                TaskFile::where('draft_token', $request->string('draft_token'))
                    ->update(['task_id' => $task->id, 'draft_token' => null]);
            }

            return $task->fresh(['assignee','files']);
        });

        // Готовим HTML карточки (чтобы сразу вставить в колонку без перезагрузки)
        $html = sprintf(
            '<a href="%s" class="block bg-white border rounded-xl hover:shadow-soft transition p-3 kanban-card" data-id="%d">
                <div class="font-medium">%s</div>
                <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600"></div>
            </a>',
            route('tasks.show', $task),
            $task->id,
            e($task->title)
        );

        return response()->json([
            'message' => 'ok',
            'id'      => $task->id,
            'html'    => $html,
        ]);
    }

    /** Перетаскивание между колонками / переупорядочивание */
    public function move(Request $request)
    {
        $payload = $request->validate([
            'task_id'    => ['required','integer','exists:tasks,id'],
            'to_column'  => ['required','integer'],
            'new_order'  => ['required','array'],
            'new_order.*'=> ['integer'],
        ]);

        DB::transaction(function () use ($payload) {
            Task::whereKey($payload['task_id'])->update([
                'column_id' => $payload['to_column'],
            ]);

            // Если есть поле позиции — раскомментируйте и подставьте своё имя колонки
            /*
            foreach ($payload['new_order'] as $index => $id) {
                Task::whereKey($id)->update(['position' => $index + 1]);
            }
            */
        });

        return response()->json(['message' => 'ok']);
    }

    /** Обновление задачи */
    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title'       => ['required','string','max:255'],
            'type'        => ['required','string','max:20'],
            'priority'    => ['required','string','max:20'],
            'assignee_id' => ['nullable','exists:users,id'],
            'details'     => ['nullable','string'],
            'due_at'      => ['nullable','date'],
            'steps'       => ['nullable'],              // строка JSON или массив
            'draft_token' => ['nullable','string','max:100'], // новые файлы во время редактирования
        ]);

        if (!empty($data['due_at'])) {
            $data['due_at'] = Carbon::parse($data['due_at'])->toDateString();
        }

        // steps: строка JSON -> массив
        if (isset($data['steps']) && is_string($data['steps'])) {
            $decoded = json_decode($data['steps'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['steps'] = $decoded;
            } else {
                unset($data['steps']); // не портим поле некорректным значением
            }
        }

        DB::transaction(function () use ($task, $data, $request) {
            $task->fill($data);
            $task->save();

            // Если во время редактирования были загружены новые файлы — привязываем
            if ($request->filled('draft_token')) {
                TaskFile::where('draft_token', $request->string('draft_token'))
                    ->update(['task_id' => $task->id, 'draft_token' => null]);
            }
        });

        return $request->wantsJson()
            ? response()->json(['message' => 'ok', 'id' => $task->id])
            : back()->with('ok', 'Сохранено');
    }

    /** Удаление задачи (вместе с файлами) */
    public function destroy(Request $request, Task $task)
    {
        // удаляем прикреплённые файлы с диска
        foreach ($task->files as $f) {
            if (!empty($f->path)) {
                Storage::disk('public')->delete($f->path);
            }
            $f->delete();
        }

        $boardId = $task->board_id;
        $task->delete();

        return $request->wantsJson()
            ? response()->json(['message' => 'deleted'])
            : redirect()->route('kanban.show', $boardId);
    }

    /** Просмотр задачи */
    public function show(Task $task)
    {
        $task->load(['files','comments.user','assignee','creator','column','timers.user']);
        return view('tasks.show', compact('task'));
    }
}
