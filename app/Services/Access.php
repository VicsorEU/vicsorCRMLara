<?php

namespace App\Services;

use App\Models\{Project, User};
use Illuminate\Database\Eloquent\Builder;

class Access
{
    /** Нормализуем значение к множеству разрешений */
    public static function normalize(mixed $val): array
    {
        if (is_bool($val)) {
            return $val ? ['full','view','own'] : ['none'];
        }
        if (is_string($val)) {
            $v = strtolower(trim($val));
            if ($v === 'read') $v = 'view';
            return match ($v) {
                'full' => ['full','view','own'],
                'view' => ['view'],
                'own'  => ['own','view'],
                default => ['none'],
            };
        }
        if (is_array($val)) {
            $flat = [];
            foreach ($val as $item) $flat = array_unique(array_merge($flat, self::normalize($item)));
            return $flat ?: ['none'];
        }
        return ['none'];
    }

    /** Возвращает набор разрешений по ресурсу */
    public static function permissionsFor(User $user, string $resource): array
    {
        $abilities = $user->accessRole?->abilities ?? [];

        // Back-compat для настроек
        if ($resource === 'settings') {
            $val = $abilities['settings'] ?? ($abilities['settings_edit'] ?? false);
            return self::normalize($val);
        }

        $val = $abilities[$resource] ?? 'none';
        return self::normalize($val);
    }

    /** Сводим набор разрешений к одному уровню: full|own|view|none */
    public static function ability(User $user, string $resource): string
    {
        $perms = self::permissionsFor($user, $resource);
        if (in_array('full', $perms, true)) return 'full';
        if (in_array('own',  $perms, true)) return 'own';
        if (in_array('view', $perms, true)) return 'view';
        return 'none';
    }

    /**
     * Проверка доступа (OR по требуемым уровням).
     * Примеры:
     *   Access::can($u, 'settings')                   // любой доступ, кроме none
     *   Access::can($u, 'settings', 'view')           // минимум view
     *   Access::can($u, 'projects', ['own','full'])   // own ИЛИ full
     */
    public static function can(User $user, string $resource, array|string|null $needs = null): bool
    {
        if (!$user->accessRole) return false;

        $have  = self::ability($user, $resource); // full|own|view|none
        $rank  = fn(string $lvl) => match ($lvl) {
            'full' => 3, 'own' => 2, 'view' => 1, default => 0,
        };

        if ($needs === null) {
            return $rank($have) > 0;
        }

        $needs = is_array($needs) ? $needs : [$needs];
        $needs = array_map(fn($n) => strtolower($n)==='read' ? 'view' : strtolower($n), $needs);

        foreach ($needs as $need) {
            if ($rank($have) >= $rank($need)) return true; // full ≥ own ≥ view
        }
        return false;
    }

    /** Own-проверка (владелец или full) */
    public static function canOwn(User $user, string $resource, int $ownerId): bool
    {
        $ability = self::ability($user, $resource);
        return $ability === 'full' || ($ability === 'own' && (int)$user->id === (int)$ownerId);
    }

    // -------------------------- Role helpers --------------------------

    protected static function roleSlug(User $u): ?string
    {
        return $u->accessRole?->slug;
    }

    protected static function isAdmin(User $u): bool
    {
        return self::roleSlug($u) === 'admin';
    }

    protected static function isManager(User $u): bool
    {
        return self::roleSlug($u) === 'manager';
    }

    // ---------------------- Project membership -----------------------

    /** true если пользователь — ответственный ИЛИ в team_users ИЛИ в одной из team_groups */
    protected static function isMemberOfProject(User $u, Project $p): bool
    {
        if ((int)$p->manager_id === (int)$u->id) return true;

        $uid = (int)$u->id;
        $inUsers = in_array($uid, array_map('intval', (array)$p->team_users), true);
        if ($inUsers) return true;

        $userGroupIds = $u->groups()->pluck('groups.id')->map(fn($x)=>(int)$x)->all();
        $projGroupIds = array_map('intval', (array)$p->team_groups);
        return count(array_intersect($userGroupIds, $projGroupIds)) > 0;
    }

    // ================== ПРОЕКТЫ (видимость/редактирование/скоуп) ==================

    /** Видимость проекта */
    public static function canSeeProject(User $user, Project $project): bool
    {
        $lvl = self::ability($user, 'projects'); // full|own|view|none
        if ($lvl === 'none') return false;

        // Админ с full — видит всё
        if (self::isAdmin($user) && $lvl === 'full') return true;

        // Менеджер: видит ТОЛЬКО проекты, где он участник (или ответственный).
        if (self::isManager($user)) {
            if (!self::isMemberOfProject($user, $project)) return false;
            // личный — только если он ответственный
            if ($project->is_private && (int)$project->manager_id !== (int)$user->id) return false;
            // lvl 'view' или 'full' — ок; 'none' уже отфильтровали
            return true;
        }

        // Прочие роли — прежняя логика:
        if ($lvl === 'full') return true;

        if ($project->is_private) {
            return (int)$project->manager_id === (int)$user->id; // личный — только владелец (или админ выше)
        }

        if ($lvl === 'view') return true;

        if ($lvl === 'own') {
            return self::isMemberOfProject($user, $project);
        }

        return false;
    }

    /** Право редактирования проекта */
    public static function canEditProject(User $user, Project $project): bool
    {
        $lvl = self::ability($user, 'projects');

        // Админ с full — редактирует всё
        if (self::isAdmin($user) && $lvl === 'full') return true;

        // Менеджер: редактирует только «свои» проекты и только если у него projects=full.
        if (self::isManager($user)) {
            if ($lvl !== 'full') return false;
            if (!self::isMemberOfProject($user, $project)) return false;
            // личный — редактирует только если он ответственный
            if ($project->is_private) return (int)$project->manager_id === (int)$user->id;
            return true;
        }

        // Прочие роли — как раньше
        if ($lvl === 'full') return true;
        if ($lvl === 'own')  return (int)$project->manager_id === (int)$user->id;
        return false;
    }

    /** Скоуп: проекты, видимые пользователю */
    public static function scopeProjects(Builder $q, User $user): Builder
    {
        $lvl = self::ability($user, 'projects');

        if ($lvl === 'none') return $q->whereRaw('1=0');

        // Админ + full — без ограничений
        if (self::isAdmin($user) && $lvl === 'full') return $q;

        // Менеджер — только проекты по членству (и личные только если он ответственный)
        if (self::isManager($user)) {
            $uid = (int)$user->id;
            $groupIds = $user->groups()->pluck('groups.id')->map(fn($x)=>(int)$x)->all();

            return $q->where(function ($qq) use ($uid, $groupIds) {
                // ответственный — любые (в т.ч. личные)
                $qq->where('manager_id', $uid);

                // член команды — только НЕ личные
                $qq->orWhere(function ($w) use ($uid) {
                    $w->where('is_private', false)
                        ->whereJsonContains('team_users', $uid);
                });

                if (!empty($groupIds)) {
                    $qq->orWhere(function ($w) use ($groupIds) {
                        $w->where('is_private', false)
                            ->where(function($x) use ($groupIds){
                                foreach ($groupIds as $gid) {
                                    $x->orWhereJsonContains('team_groups', $gid);
                                }
                            });
                    });
                }
            });
        }

        // Прочие роли — как раньше
        if ($lvl === 'full') return $q;

        if ($lvl === 'view') {
            return $q->where('is_private', false);
        }

        if ($lvl === 'own') {
            $uid = (int)$user->id;
            $groupIds = $user->groups()->pluck('groups.id')->map(fn($x)=>(int)$x)->all();

            return $q->where(function ($qq) use ($uid, $groupIds) {
                $qq->where('manager_id', $uid)
                    ->orWhere(function ($w) use ($uid) {
                        $w->where('is_private', false)
                            ->whereJsonContains('team_users', $uid);
                    });

                if (!empty($groupIds)) {
                    $qq->orWhere(function ($w) use ($groupIds) {
                        $w->where('is_private', false)
                            ->where(function($x) use ($groupIds){
                                foreach ($groupIds as $gid) {
                                    $x->orWhereJsonContains('team_groups', $gid);
                                }
                            });
                    });
                }
            });
        }

        return $q->whereRaw('1=0');
    }
}
