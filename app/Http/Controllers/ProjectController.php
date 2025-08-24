<?php

namespace App\Http\Controllers;

use App\Models\{Project, User};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\AppSetting;

class ProjectController extends Controller
{
    // ProjectsController@index
    public function index()
    {
        $projects = \App\Models\Project::with('manager')->get();

        // Справочник отделов (можешь получать так, как у тебя сделано)
        $rows = \App\Models\Settings\ProjectDepartment::orderBy('position')->orderBy('id')->get();
        $deptIdToName  = $rows->pluck('name','id')->all();
        $deptIdToColor = $rows->pluck('color','id')->all();
        $orderedDepIds = array_values($rows->pluck('id')->all());

        return view('projects.index', compact('projects','deptIdToName','deptIdToColor','orderedDepIds'));
    }



    public function store(Request $r)
    {
        $departments = AppSetting::get('projects', ['departments'=>[]])['departments'] ?? [];

        $data = $r->validate([
            'name'       => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date'    => 'nullable|date',
            'note'       => 'nullable|string',
            'department' => ['nullable','integer'],
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
            ['name' => 'Done',        'color' => '#16a34a', 'sort_order' => 3, 'system_key' => 'done'],
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
            'name'          => ['required','string','max:255'],
            'start_date'    => ['nullable','date'],
            'end_date'      => ['nullable','date','after_or_equal:start_date'],
            'manager_id'    => ['nullable','exists:users,id'],
            'note'          => ['nullable','string'],
            'department'    => ['nullable','integer','exists:settings_project_departments,id'],
        ]);

        $project->fill($data)->save();

        return $r->wantsJson()
            ? response()->json(['message'=>'ok'])
            : back()->with('ok','Сохранено');
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
