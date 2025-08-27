<?php

namespace App\Http\Controllers\Settings\users;

use App\Http\Controllers\Controller;
use App\Models\AccessRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{
    public function index()
    {
        // вернём роли + count пользователей и abilities
        $items = AccessRole::query()
            ->select('access_roles.*')
            ->selectSub(function ($q) {
                $q->from('users')->selectRaw('count(*)')
                    ->whereColumn('users.access_role_id', 'access_roles.id');
            }, 'users_count')
            ->orderBy('system','desc')->orderBy('name')
            ->get();

        return response()->json([
            'items' => $items->map(function ($r) {
                return [
                    'id'          => $r->id,
                    'name'        => $r->name,
                    'slug'        => $r->slug,
                    'system'      => (bool)$r->system,
                    'users_count' => (int)($r->users_count ?? 0),
                    'abilities'   => $r->abilities,
                ];
            })
        ]);
    }

    public function store(Request $r)
    {
        $data = $this->validateData($r);

        $slug = Str::slug($data['name']);
        if (AccessRole::where('slug',$slug)->exists()) {
            $slug .= '-'.Str::random(4);
        }

        $role = AccessRole::create([
            'name'      => $data['name'],
            'slug'      => $slug,
            'abilities' => $data['abilities'],
            'system'    => false,
        ]);

        return response()->json(['role'=>$role], 201);
    }

    public function update(Request $r, AccessRole $role)
    {
        $data = $this->validateData($r);
        $role->update([
            'name'      => $data['name'],
            // slug для системных и существующих не меняем (простота)
            'abilities' => $data['abilities'],
        ]);
        return response()->json(['role'=>$role]);
    }

    public function destroy(AccessRole $role)
    {
        if ($role->system) {
            return response()->json(['message'=>'Системную роль удалить нельзя'], 422);
        }
        $hasUsers = DB::table('users')->where('access_role_id',$role->id)->exists();
        if ($hasUsers) {
            return response()->json(['message'=>'Роль назначена пользователям'], 422);
        }
        $role->delete();
        return response()->json(['ok'=>true]);
    }

    private function validateData(Request $r): array
    {
        $v = $r->validate([
            'name' => ['required','string','max:100'],
            'abilities' => ['nullable','array'],
            'abilities.settings_edit' => ['nullable','boolean'],
            'abilities.projects'      => ['nullable','in:full,read,own,none'],
        ]);

        // нормализация
        $abilities = array_merge(
            ['settings_edit'=>false,'projects'=>'none'],
            $v['abilities'] ?? []
        );

        return [
            'name'      => $v['name'],
            'abilities' => $abilities,
        ];
    }
}
