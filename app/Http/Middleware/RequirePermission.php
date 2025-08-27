<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequirePermission
{
    /**
     * Использование:
     *  ->middleware('perm:settings.edit')                   // булевый флаг
     *  ->middleware('perm:projects.access,full,view')       // значение из набора
     */
    public function handle(Request $request, Closure $next, ...$params)
    {
        $user = $request->user();
        if (!$user) abort(403);

        $key = array_shift($params);    // первый аргумент — ключ
        $allowed = $params;             // остальные — допустимые значения

        $ok = $allowed
            ? $user->allows($key, $allowed)
            : $user->allows($key);

        if (!$ok) abort(403);

        return $next($request);
    }
}
