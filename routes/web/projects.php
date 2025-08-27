<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ColumnController;

Route::middleware('auth')->group(function () {
    // проекты
    Route::get('/projects',             [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects',            [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}',   [ProjectController::class, 'show'])->whereNumber('project')->name('projects.show');
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->whereNumber('project')->name('projects.update');
    Route::delete('/projects/{project}',[ProjectController::class, 'destroy'])->whereNumber('project')->name('projects.destroy');

});

// ===============================
// Колонки проекта (AJAX) — ТОЛЬКО FULL
// ===============================
Route::middleware(['auth','access:projects,full'])->group(function () {
    // создать колонку
    Route::post('/projects/{project}/columns', [ColumnController::class, 'store'])
        ->whereNumber('project')->name('columns.store');

    // изменить колонку (имя/цвет/и т.п.)
    Route::patch('/columns/{column}', [ColumnController::class, 'update'])
        ->whereNumber('column')->name('columns.update');

    // удалить колонку
    Route::delete('/columns/{column}', [ColumnController::class, 'destroy'])
        ->whereNumber('column')->name('columns.destroy');

    // пересортировать колонки на доске проекта
    Route::post('/projects/{project}/columns/reorder', [ColumnController::class, 'reorder'])
        ->whereNumber('project')->name('columns.reorder');
});
