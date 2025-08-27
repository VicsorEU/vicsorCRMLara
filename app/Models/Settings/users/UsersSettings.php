<?php

namespace App\Models\Settings\users;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSettings
{
    // ------- USERS -------
    public static function listUsers()
    {
        return User::query()
            ->select('id','name','email','phone','company','blocked_at','created_at','access_role_id')
            ->orderBy('name')
            ->get();
    }

    public static function createUser(array $data): User
    {
        $payload = [
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'company'  => $data['company'] ?? null,
            'password' => Hash::make($data['password']),
            'access_role_id' => $data['access_role_id'] ?? null,
        ];
        return User::create($payload);
    }

    public static function updateUser(User $user, array $data): User
    {
        $user->fill([
            'name'    => $data['name'],
            'email'   => $data['email'],
            'phone'   => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'access_role_id' => $data['access_role_id'] ?? null,
        ]);
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        return $user;
    }

    public static function toggleBlock(User $user): User
    {
        $user->blocked_at = $user->blocked_at ? null : now();
        $user->save();
        return $user;
    }

    // ------- GROUPS -------
    public static function listGroups()
    {
        return Group::query()
            ->withCount('users')
            ->orderBy('name')
            ->get();
    }

    public static function createGroup(string $name, array $userIds = []): Group
    {
        $g = Group::create(['name' => $name]);
        if ($userIds) $g->users()->sync($userIds);
        return $g->loadCount('users');
    }

    public static function updateGroup(Group $group, string $name, array $userIds = []): Group
    {
        $group->update(['name' => $name]);
        $group->users()->sync($userIds);
        return $group->loadCount('users');
    }
}
