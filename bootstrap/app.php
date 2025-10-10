<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        // Вариант 1: встроенный health-check на /up
        health: '/up',
    // Вариант 2 (если используешь свой файл): health: __DIR__.'/../routes/health.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Доверяем прокси хостинга и читаем X-Forwarded-* для https/host/port
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO
        );

        // Алиасы для роут-мидлварей
        $middleware->alias([
            'role'               => \Spatie\Permission\Middlewares\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,

            // Наш алиас проверки доступов по abilities JSON
            'access'             => \App\Http\Middleware\AccessMiddleware::class,
        ]);

        // При желании:
        // $middleware->redirectGuestsTo('/login');
        // $middleware->redirectUsersTo('/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
