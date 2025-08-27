<?php

namespace App\Http\Middleware;

use App\Services\Access;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessMiddleware
{
    /**
     * Использование: ->middleware('access:settings,view')
     * или 'access:settings,full' или 'access:projects,own|full'
     */
    public function handle(Request $request, Closure $next, string $resource, string $needs = null): Response
    {
        $user = $request->user();
        if (!$user) {
            return $this->deny($request, 'Доступ запрещён.');
        }

        $needsList = [];
        if ($needs) {
            $needsList = array_map('trim', explode('|', $needs));
        }

        $authorized = Access::can($user, $resource, $needsList);
        if ($authorized) {
            return $next($request);
        }

        // Спец-случай: у пользователя только VIEW, а запрос требует редактирование (own/full)
        $perms       = Access::permissionsFor($user, $resource);
        $wantsWrite  = in_array('full', $needsList, true) || in_array('own', $needsList, true);
        $hasOnlyView = in_array('view', $perms, true) && !in_array('full', $perms, true) && !in_array('own', $perms, true);

        $message = ($wantsWrite && $hasOnlyView)
            ? 'У вас нет прав на редактирование.'
            : 'Недостаточно прав.';

        return $this->deny($request, $message);
    }

    protected function deny(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }
        abort(403, $message);
    }
}
