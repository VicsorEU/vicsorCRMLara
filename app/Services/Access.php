<?php

namespace App\Services;

use App\Models\User;

class Access
{
    /** Нормализуем значение к множеству разрешений */
    public static function normalize(mixed $val): array
    {
        // bool — удобно для settings_edit
        if (is_bool($val)) {
            return $val ? ['full','view','own'] : ['none'];
        }

        // строка: 'full' | 'read' | 'view' | 'own' | 'none'
        if (is_string($val)) {
            $v = strtolower($val);
            if ($v === 'read') $v = 'view';
            return match ($v) {
                'full' => ['full','view','own'],
                'view' => ['view'],
                'own'  => ['own','view'],
                default => ['none'],
            };
        }

        // массив значений (если будешь хранить комбинированно)
        if (is_array($val)) {
            $flat = [];
            foreach ($val as $item) {
                $flat = array_unique(array_merge($flat, self::normalize($item)));
            }
            return $flat ?: ['none'];
        }

        return ['none'];
    }

    /** Возвращает набор разрешений по ресурсу */
    public static function permissionsFor(User $user, string $resource): array
    {
        $abilities = $user->accessRole?->abilities ?? [];

        // Back-compat: для настроек у тебя есть settings_edit (bool).
        if ($resource === 'settings') {
            $val = $abilities['settings'] ?? ($abilities['settings_edit'] ?? false);
            return self::normalize($val);
        }

        $val = $abilities[$resource] ?? 'none';
        return self::normalize($val);
    }

    /** Может ли пользователь хоть как-то по нуждам ($needs) */
    public static function can(User $user, string $resource, array $needs = []): bool
    {
        if (!$user->accessRole) return false;
        $perms = self::permissionsFor($user, $resource);
        if (in_array('full', $perms, true)) return true;
        if (empty($needs)) return !in_array('none', $perms, true);

        $needs = array_map(fn($n)=>$n==='read'?'view':$n, $needs);
        foreach ($needs as $need) {
            if (in_array($need, $perms, true)) return true;
        }
        return false;
    }

    /** Own-проверка (владелец или full) */
    public static function canOwn(User $user, string $resource, int $ownerId): bool
    {
        $perms = self::permissionsFor($user, $resource);
        if (in_array('full', $perms, true)) return true;
        return in_array('own', $perms, true) && $user->id === $ownerId;
    }
}
