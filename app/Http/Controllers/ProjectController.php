<?php

namespace App\Http\Controllers;

use App\Models\{Project, User};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\AppSetting;

class ProjectController extends Controller
{
    public function index()
    {
        //return view('projects.index', compact('projects','users'));

        // список отделов из настроек
        $settings = AppSetting::get('projects', ['departments'=>[]]);
        $departments = $settings['departments'] ?? [];

        // проекты
        $projects = Project::with('manager')->orderByDesc('id')->paginate(20);

        //пользователи
        $users = User::orderBy('name')->get();

        // подготовим группы по отделам (+ блок "Без отдела")
        $groups = collect();
        foreach ($departments as $dep) {
            $groups->put($dep, collect());
        }
        $groups->put('— Без отдела —', collect());

        foreach ($projects as $p) {
            $key = $p->department ?: '— Без отдела —';
            if (!$groups->has($key)) $groups->put($key, collect());
            $groups[$key]->push($p);
        }

        return view('projects.index', [
            'groups'      => $groups,
            'departments' => $departments,
            'users'       => $users,
        ]);
    }

    public function store(Request $r)
    {
        $departments = AppSetting::get('projects', ['departments'=>[]])['departments'] ?? [];

        $data = $r->validate([
            'name'       => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'note'       => 'nullable|string',
            'department'  => ['nullable','string','max:100', Rule::in($departments)],
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
        $departments = AppSetting::get('projects', ['departments'=>[]])['departments'] ?? [];

        $data = $r->validate([
            'name'       => 'required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'note'       => 'nullable|string',
            'department'  => ['nullable','string','max:100', Rule::in($departments)],

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
