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
        // Настройки
        $settings = AppSetting::get('projects', [
            'departments'         => [],
            'departments_colors'  => [],
            // если заведёшь устойчивые ID — добавь сюда 'departments_ids' => []
        ]);

        $names  = array_values($settings['departments'] ?? []);
        $colors = array_values($settings['departments_colors'] ?? []);

        // Карты id->name / id->color (id = i+1)
        $deptIdToName  = [];
        $deptIdToColor = [];
        $DEF = '#94a3b8';

        foreach ($names as $i => $name) {
            $id = $i + 1;
            $deptIdToName[$id]  = trim((string)$name);
            $deptIdToColor[$id] = $colors[$i] ?? $DEF;
        }

        // Проекты
        $projects = Project::with('manager')->orderByDesc('id')->paginate(20);
        $users    = User::orderBy('name')->get();

        // Группы: по id + специальная группа 0 = "Без отдела"
        $groups = collect();
        foreach (array_keys($deptIdToName) as $id) {
            $groups->put($id, collect());
        }
        $groups->put(0, collect());

        foreach ($projects as $p) {
            $id = (int) $p->department;
            if ($id && isset($deptIdToName[$id])) {
                $groups[$id]->push($p);
            } else {
                $groups[0]->push($p);
            }
        }

        return view('projects.index', [
            'groups'        => $groups,
            'deptIdToName'  => $deptIdToName,
            'deptIdToColor' => $deptIdToColor,
            'users'         => $users,
        ]);
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
            'end_date'    => 'nullable|date',
            'note'       => 'nullable|string',
            'department' => ['nullable','integer'],

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
