<?php

use Illuminate\Support\Facades\Route;

// корневой редирект
Route::get('/', fn () => redirect()->route('login'));

// порядок важен: сначала гостевые, затем приватные модули
require __DIR__.'/web/auth.php';
require __DIR__.'/web/core.php';
require __DIR__.'/web/shops.php';
require __DIR__.'/web/projects.php';
require __DIR__.'/web/tasks.php';
require __DIR__.'/web/timers.php';
require __DIR__.'/web/files.php';
require __DIR__.'/web/settings.php';

// при желании: fallback
// Route::fallback(fn() => abort(404));
