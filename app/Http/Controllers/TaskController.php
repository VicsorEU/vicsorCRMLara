<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskFile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\{Project, User};

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
            'due_to'      => ['nullable','date'],
            'priority'    => ['nullable','integer','min:1'],
            'type'        => ['nullable','integer','min:1'],
            'assignee_id' => ['nullable','integer'],
            'steps'       => ['nullable'],              // может прийти строкой JSON
            'draft_token' => ['nullable','string','max:100'],
        ]);

        // дефолты
        $data['priority']   = $data['priority']   ?? '1';
        $data['type']       = $data['type']       ?? '1';
        $data['created_by'] = Auth::id();

        if (!empty($data['due_at'])) {
            $data['due_at'] = Carbon::parse($data['due_at'])->toDateString();
        }

        if (!empty($data['due_to'])) {
            $data['due_to'] = Carbon::parse($data['due_to'])->toDateString();
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

            // привязываем загруженные файлы по draft_token к созданной задаче
            if ($request->filled('draft_token')) {
                TaskFile::where('draft_token', $request->string('draft_token'))
                    ->update(['task_id' => $task->id, 'draft_token' => null]);
            }

            return $task;
        });

        // подгружаем то, что нужно карточке
        $task->loadMissing(['assignee']);
        // если в модели нет кастов, убедимся что due_at — Carbon (на всякий случай)
        if ($task->due_at && !($task->due_at instanceof Carbon)) {
            $task->due_at = Carbon::parse($task->due_at);
        }

        if ($task->due_to && !($task->due_to instanceof Carbon)) {
            $task->due_to = Carbon::parse($task->due_to);
        }

        // Рендерим ту же карточку, что используется на доске
        // resources/views/kanban/_card.blade.php
        $html = view('kanban._card', ['task' => $task])->render();

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
            'priority'    => ['nullable','integer','min:1'],
            'type'        => ['nullable','integer','min:1'],
            'assignee_id' => ['nullable','exists:users,id'],
            'details'     => ['nullable','string'],
            'due_at'      => ['nullable','date'],
            'due_to'      => ['nullable','date'],
            'steps'       => ['nullable'],              // строка JSON или массив
            'draft_token' => ['nullable','string','max:100'], // новые файлы во время редактирования
        ]);

        if (!empty($data['due_at'])) {
            $data['due_at'] = Carbon::parse($data['due_at'])->toDateString();
        }

        if (!empty($data['due_to'])) {
            $data['due_to'] = Carbon::parse($data['due_to'])->toDateString();
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
        $users = User::orderBy('name')->get();
        $usersMap = $users->pluck('name','id');
        return view('tasks.show', compact('task','users','usersMap'));
    }
}
