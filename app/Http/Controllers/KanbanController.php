<?php

namespace App\Http\Controllers;

use App\Models\{TaskBoard, TaskColumn, Task, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KanbanController extends Controller
{
    public function show(?int $boardId = null)
    {
        $board = $boardId
            ? TaskBoard::with(['columns.tasks.assignee'])->findOrFail($boardId)
            : TaskBoard::with(['columns.tasks.assignee'])
                ->where('created_by', Auth::id())
                ->first();

        if (!$board) {
            $board = TaskBoard::create(['name' => 'Моя доска', 'created_by' => Auth::id()]);
        }

        // Пресет колонок, если пусто
        if ($board->columns()->count() === 0) {
            $board->columns()->createMany([
                ['name'=>'test',       'color'=>'#5f6368', 'sort_order'=>1],
                ['name'=>'цукуцк',     'color'=>'#2563eb', 'sort_order'=>2],
                ['name'=>'цукцуккккц', 'color'=>'#d53f8c', 'sort_order'=>3],
            ]);
            $board->load(['columns.tasks.assignee']);
        }

        // Фоллбэк-словарь имён исполнителей (на случай проблем с кешем отношений)
        $usersMap = User::pluck('name','id');

        return view('kanban.board', compact('board','usersMap'));
    }

}
