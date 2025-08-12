<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductMediaController;
use App\Http\Controllers\{
    ProjectController, ColumnController, KanbanController, TaskController,
    TimerController, TaskFileController, TaskCommentController
};

Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/register',  [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('auth')->group(function () {
    // dashboard — один раз
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::resource('companies', CompanyController::class);
    Route::resource('contacts', ContactController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('attributes', AttributeController::class)->except(['show']);
    Route::resource('warehouses', WarehouseController::class);
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::resource('products', ProductController::class);

    Route::post('uploads/products', [ProductMediaController::class, 'upload'])->name('products.upload');
    Route::delete('uploads/products/{image}', [ProductMediaController::class, 'destroy'])->whereNumber('image')->name('products.upload.delete');

    Route::get('/tasks/kanban/{board?}', [KanbanController::class, 'show'])->whereNumber('board')->name('kanban.show');

    // ===== Проекты =====
    Route::get('/projects',             [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects',            [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}',   [ProjectController::class, 'show'])->whereNumber('project')->name('projects.show');
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->whereNumber('project')->name('projects.update');
    Route::delete('/projects/{project}',[ProjectController::class, 'destroy'])->whereNumber('project')->name('projects.destroy');

    // Колонки (AJAX)
    Route::post('/projects/{project}/columns',         [ColumnController::class, 'store'])->whereNumber('project')->name('columns.store');
    Route::patch('/columns/{column}',                  [ColumnController::class, 'update'])->whereNumber('column')->name('columns.update');
    Route::delete('/columns/{column}',                 [ColumnController::class, 'destroy'])->whereNumber('column')->name('columns.destroy');
    Route::post('/projects/{project}/columns/reorder', [ColumnController::class, 'reorder'])->whereNumber('project')->name('columns.reorder');

    // ===== Задачи / канбан =====
    // ВАЖНО: спец-роут без ID должен идти ПЕРВЫМ
    Route::post('/tasks/move', [TaskController::class, 'move'])->name('tasks.move');

    Route::post('/tasks',                  [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}',            [TaskController::class, 'show'])->whereNumber('task')->name('tasks.show');
    Route::post('/tasks/{task}',          [TaskController::class, 'update'])->whereNumber('task')->name('tasks.update');

    Route::post('/tasks/{task}/timer/start',[TimerController::class,'start'])->whereNumber('task')->name('kanban.timer.start');
    Route::post('/tasks/{task}/timer/stop', [TimerController::class,'stop'])->whereNumber('task')->name('kanban.timer.stop');
    Route::get('/timer/active',             [TimerController::class,'active'])->name('kanban.timer.active');

    Route::post('/tasks/{task}/files',    [TaskFileController::class,'store'])->whereNumber('task')->name('tasks.files.store');
    Route::delete('/files/{file}',        [TaskFileController::class,'destroy'])->whereNumber('file')->name('tasks.files.delete');
    Route::delete('/tasks/{task}',       [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::post('/tasks/{task}/comments', [TaskCommentController::class,'store'])->whereNumber('task')->name('tasks.comments.store');

    // Если используете отдельные эндпоинты для загрузчика файлов:
    Route::post('/task-files/upload',                    [TaskFileController::class, 'upload'])->name('task-files.upload');
    Route::delete('/task-files/{attachment}',            [TaskFileController::class, 'destroy'])->whereNumber('attachment')->name('task-files.destroy');
});
