<?php

namespace App\Http\Controllers;

use App\Models\{Project, TaskBoard, TaskColumn, Task, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('manager')->orderByDesc('id')->paginate(20);
        $users = User::orderBy('name')->get();
        return view('projects.index', compact('projects','users'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'       => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'note'       => 'nullable|string',
        ]);
        $data['created_by'] = \Auth::id();

        $project = \App\Models\Project::create($data);
        $board = \App\Models\TaskBoard::create([
            'project_id' => $project->id,
            'name'       => 'Доска проекта',
            'created_by' => \Auth::id(),
        ]);
        $board->columns()->createMany([
            ['name'=>'To Do','color'=>'#64748b','sort_order'=>1],
            ['name'=>'In Progress','color'=>'#2563eb','sort_order'=>2],
            ['name'=>'Done','color'=>'#16a34a','sort_order'=>3],
        ]);

        return response()->json([
            'ok' => true,
            'redirect' => route('projects.show', $project),
        ], 201);
    }


    public function show(Project $project)
    {
        $project->load(['manager','board.columns.tasks.assignee']);
        $users = User::orderBy('name')->get();
        $usersMap = $users->pluck('name','id');

        return view('projects.show', compact('project','users','usersMap'));
    }

    public function update(Request $r, Project $project)
    {
        $data = $r->validate([
            'name'       => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'note'       => 'nullable|string',
        ]);
        $project->update($data);

        return response()->json(['ok'=>true]);
    }

    public function destroy(Request $r, Project $project)
    {
        // каскад сработает по FK: board -> columns -> tasks (если настроены cascadeOnDelete)
        $project->delete();

        if ($r->wantsJson()) {
            return response()->json(['ok'=>true,'redirect'=>route('projects.index')]);
        }
        return redirect()->route('projects.index')->with('ok', 'Проект удалён');
    }
}
