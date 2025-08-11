<?php

namespace App\Http\Controllers;

use App\Models\{Task, TaskColumn};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function store(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'board_id'    => ['required','integer','exists:task_boards,id'],
            'column_id'   => ['required','integer','exists:task_columns,id'],
            'title'       => ['required','string','max:255'],
            'details'     => ['nullable','string'],
            'due_at'      => ['nullable','date'],
            'priority'    => ['nullable','string','max:20'],
            'type'        => ['nullable','string','max:20'],
            'assignee_id' => ['nullable','integer','exists:users,id'],
            'steps'       => ['nullable','array'],
            'steps.*'     => ['nullable','string','max:1000'],
            'draft_token' => ['nullable','string','max:100'],
        ]);

        // очистим пустые этапы
        if (isset($data['steps'])) {
            $data['steps'] = array_values(array_filter($data['steps'], fn($s) => trim((string)$s) !== ''));
        }

        $task = \App\Models\Task::create([
            'board_id'    => $data['board_id'],
            'column_id'   => $data['column_id'],
            'title'       => $data['title'],
            'details'     => $data['details'] ?? null,
            'due_at'      => $data['due_at'] ?? null,
            'priority'    => $data['priority'] ?? 'normal',
            'type'        => $data['type'] ?? 'common',
            'assignee_id' => $data['assignee_id'] ?? null,
            'steps'       => $data['steps'] ?? null,
        ]);

        // Привязываем загруженные файлы (через draft_token)
        if (!empty($data['draft_token'])) {
            \App\Models\TaskFile::where('draft_token', $data['draft_token'])
                ->where('user_id', $request->user()->id)
                ->update(['task_id' => $task->id, 'draft_token' => null]);
        }

        // возвращаем HTML карточки (как в твоей системе)
        $html = view('kanban._card', ['task' => $task->fresh('assignee')])->render();

        return response()->json(['html' => $html]);
    }


    public function show(Task $task)
    {
        $task->load(['files','comments.user','timers','assignee']);
        return view('tasks.show', compact('task'));
    }

    public function update(Request $r, Task $task)
    {
        $data = $r->validate([
            'title'       => 'required|string|max:255',
            'details'     => 'nullable|string',
            'due_at'      => 'nullable|date',
            'priority'    => 'required|in:low,normal,high,p1,p2',
            'type'        => 'required|in:in,out,transfer,adjust,common',
            'assignee_id' => 'nullable|exists:users,id',
        ]);
        $task->update($data);
        return back()->with('ok','Сохранено');
    }

    // DnD перетаскивание
    public function move(Request $r)
    {
        $r->validate([
            'task_id'   => 'required|exists:tasks,id',
            'to_column' => 'required|exists:task_columns,id',
            'new_order' => 'required|array',
        ]);

        $task = Task::findOrFail($r->task_id);
        $task->column_id = $r->to_column;
        $task->save();

        foreach ($r->new_order as $idx => $id) {
            Task::where('id',$id)->update(['card_order' => $idx + 1]);
        }

        return response()->json(['ok' => true]);
    }
}
