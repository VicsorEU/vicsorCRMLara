<?php

namespace App\Http\Controllers\Settings\users;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Settings\users\UsersSettings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsersSettingsController extends Controller
{
    // ---------- USERS ----------
    public function usersIndex()
    {
        return response()->json([
            'items' => UsersSettings::listUsers(),
        ]);
    }

    public function usersStore(Request $r)
    {
        $data = $r->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'phone'    => ['nullable','string','max:32'],
            'company'  => ['nullable','string','max:191'],
            'password' => ['required','string','min:6'],
            'access_role_id' => ['nullable','integer','exists:access_roles,id'],
        ]);
        $user = UsersSettings::createUser($data);
        return response()->json(['user' => $user], 201);
    }

    public function usersUpdate(Request $r, User $user)
    {
        $data = $r->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'phone'    => ['nullable','string','max:32'],
            'company'  => ['nullable','string','max:191'],
            'password' => ['nullable','string','min:6'],
            'access_role_id' => ['nullable','integer','exists:access_roles,id'],
        ]);
        $user = UsersSettings::updateUser($user, $data);
        return response()->json(['user' => $user]);
    }

    public function usersBlockToggle(User $user)
    {
        $user = UsersSettings::toggleBlock($user);
        return response()->json(['user' => $user]);
    }

    public function usersDestroy(User $user)
    {
        $user->delete();
        return response()->json(['ok' => true]);
    }

    // ---------- GROUPS ----------
    public function groupsIndex()
    {
        // грузим группы с пользователями (только id), считаем участников
        $groups = Group::with(['users:id'])
            ->withCount('users')
            ->orderBy('name')
            ->get();

        // упакуем под фронт
        $items = $groups->map(function ($g) {
            return [
                'id'          => $g->id,
                'name'        => $g->name,
                'users_count' => $g->users_count,
                // важное поле для модалки:
                'user_ids'    => $g->users->pluck('id')->values(),
            ];
        });

        return response()->json(['items' => $items]);
    }

    public function groupsStore(Request $r)
    {
        $data = $r->validate([
            'name'    => ['required','string','max:255','unique:groups,name'],
            'users'   => ['array'],
            'users.*' => ['integer','exists:users,id'],
        ]);
        $group = UsersSettings::createGroup($data['name'], $data['users'] ?? []);
        return response()->json(['group' => $group], 201);
    }

    public function groupsUpdate(Request $r, Group $group)
    {
        $data = $r->validate([
            'name'    => ['required','string','max:255', Rule::unique('groups','name')->ignore($group->id)],
            'users'   => ['array'],
            'users.*' => ['integer','exists:users,id'],
        ]);
        $group = UsersSettings::updateGroup($group, $data['name'], $data['users'] ?? []);
        return response()->json(['group' => $group]);
    }

    public function groupsDestroy(Group $group)
    {
        $group->delete();
        return response()->json(['ok' => true]);
    }
}
