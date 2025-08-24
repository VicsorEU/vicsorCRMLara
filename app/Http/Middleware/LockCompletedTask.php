<?php

namespace App\Http\Middleware;

use App\Models\Task;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LockCompletedTask
{
    /**
     * Блокирует изменения задачи с флагом complete = true.
     * Параметр $param — имя параметра маршрута, по умолчанию {task}.
     */
    public function handle(Request $request, Closure $next, string $param = 'task'): Response
    {
        $task = $request->route($param);

        if ($task instanceof Task && (bool) $task->complete) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Задача уже выполнена — редактирование запрещено.'
                ], 423);
            }
            abort(423, 'Задача уже выполнена — редактирование запрещено.');
        }

        return $next($request);
    }
}
