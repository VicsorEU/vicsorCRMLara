<?php

namespace App\Http\Controllers;

use App\Models\{Project, User};
use App\Services\Access;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\AppSetting;
use App\Models\Group;           // <-- вот это важно


class ProjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // список доступных мне проектов
        $projects = Access::scopeProjects(Project::with('manager'), $user)->get();

        // справочники отделов — как было
        $rows = \App\Models\Settings\ProjectDepartment::orderBy('position')->orderBy('id')->get();
        $deptIdToName  = $rows->pluck('name','id')->all();
        $deptIdToColor = $rows->pluck('color','id')->all();
        $orderedDepIds = array_values($rows->pluck('id')->all());

        // для модалки: пользователи и группы
        $users  = User::orderBy('name')->get(['id','name']);
        $groups = Group::orderBy('name')->get(['id','name']);

        return view('projects.index', compact(
            'projects','deptIdToName','deptIdToColor','orderedDepIds','users','groups'
        ));
    }

    public function store(Request $r)
    {
        $u = auth()->user();
        if (!Access::can($u, 'projects', ['own','full'])) {
            return response()->json(['message' => 'У вас нет прав на редактирование.'], 403);
        }

        $data = $r->validate([
            'name'        => ['required','string','max:255'],
            'manager_id'  => ['nullable','integer','exists:users,id'],
            'start_date'  => ['nullable','date'],
            'end_date'    => ['nullable','date','after_or_equal:start_date'],
            'note'        => ['nullable','string'],
            'department'  => ['nullable','integer'],
            'is_private'  => ['sometimes','boolean'],
            'team_users'  => ['nullable','array'],
            'team_users.*'=> ['integer','exists:users,id'],
            'team_groups' => ['nullable','array'],
            'team_groups.*'=> ['integer','exists:groups,id'],
        ]);

        // 1) ответственный по умолчанию — создатель
        $data['manager_id'] = $data['manager_id'] ?: $u->id;

        // 2) личное
        $data['is_private'] = (bool)($data['is_private'] ?? false);

        // 3) команда (у личных — гасим)
        $data['team_users']  = $data['is_private'] ? [] : array_values(array_unique(array_map('intval', $data['team_users']  ?? [])));
        $data['team_groups'] = $data['is_private'] ? [] : array_values(array_unique(array_map('intval', $data['team_groups'] ?? [])));

        $data['created_by'] = $u->id;

        $project = Project::create($data);

        // создаём борду как было
        $board = \App\Models\TaskBoard::create([
            'project_id' => $project->id,
            'name'       => 'Доска проекта',
            'created_by' => $u->id,
        ]);
        $board->columns()->createMany([
            ['name'=>'To Do','color'=>'#64748b','sort_order'=>1],
            ['name'=>'In Progress','color'=>'#2563eb','sort_order'=>2],
            ['name'=>'Done','color'=>'#16a34a','sort_order'=>3,'system_key'=>'done'],
        ]);

        return response()->json(['ok'=>true,'redirect'=>route('projects.show',$project)], 201);
    }

    public function show(Project $project)
    {
        if (!Access::canSeeProject(auth()->user(), $project)) abort(403);
        $project->load(['manager','board.columns.tasks.assignee']);
        $users = User::orderBy('name')->get();
        $groups  = Group::orderBy('name')->get();
        $usersMap = $users->pluck('name','id');
        $groupsMap = $groups->pluck('name','id');
        return view('projects.show', compact('project','users','usersMap','groups','groupsMap'));

    }

    public function update(Request $r, Project $project)
    {
        $u = auth()->user();
        if (!Access::canEditProject($u, $project)) {
            return response()->json(['message'=>'У вас нет прав на редактирование.'], 403);
        }

        $data = $r->validate([
            'name'         => ['required','string','max:255'],
            'start_date'   => ['nullable','date'],
            'end_date'     => ['nullable','date','after_or_equal:start_date'],
            'manager_id'   => ['nullable','integer','exists:users,id'],
            'note'         => ['nullable','string'],
            'department'   => ['nullable','integer','exists:settings_project_departments,id'],
            'is_private'   => ['sometimes','boolean'],
            'team_users'   => ['nullable','array'],
            'team_users.*' => ['integer','exists:users,id'],
            'team_groups'  => ['nullable','array'],
            'team_groups.*'=> ['integer','exists:groups,id'],
        ]);

        // если менеджера не прислали — не трогаем; если прислали null — очистим
        if (array_key_exists('manager_id', $data) && !$data['manager_id']) {
            $data['manager_id'] = null;
        }

        if (array_key_exists('is_private', $data)) {
            $data['is_private'] = (bool)$data['is_private'];
            if ($data['is_private']) {
                $data['team_users']  = [];
                $data['team_groups'] = [];
            }
        }

        if (isset($data['team_users'])) {
            $data['team_users'] = array_values(array_unique(array_map('intval', $data['team_users'])));
        }
        if (isset($data['team_groups'])) {
            $data['team_groups'] = array_values(array_unique(array_map('intval', $data['team_groups'])));
        }

        $project->fill($data)->save();

        return $r->wantsJson()
            ? response()->json(['message'=>'ok'])
            : back()->with('ok','Сохранено');
    }

    public function destroy(Request $r, Project $project)
    {
        if (!Access::canEditProject(auth()->user(), $project)) {
            return response()->json(['message'=>'У вас нет прав на редактирование.'], 403);
        }
        $project->delete();
        return $r->wantsJson()
            ? response()->json(['ok'=>true,'redirect'=>route('projects.index')])
            : redirect()->route('projects.index')->with('ok','Проект удалён');
    }
}
