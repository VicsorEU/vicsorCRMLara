<?php

namespace App\Http\Controllers;

use App\Models\{Task, TaskComment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskCommentController extends Controller
{
    public function store(Request $r, Task $task)
    {
        $r->validate(['body' => 'required|string']);
        $task->comments()->create([
            'user_id' => Auth::id(),
            'body'    => $r->body,
        ]);
        return back();
    }
}
