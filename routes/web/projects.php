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

    // колонки проекта
    Route::post('/projects/{project}/columns',         [ColumnController::class, 'store'])->whereNumber('project')->name('columns.store');
    Route::patch('/columns/{column}',                  [ColumnController::class, 'update'])->whereNumber('column')->name('columns.update');
    Route::delete('/columns/{column}',                 [ColumnController::class, 'destroy'])->whereNumber('column')->name('columns.destroy');
    Route::post('/projects/{project}/columns/reorder', [ColumnController::class, 'reorder'])->whereNumber('project')->name('columns.reorder');
});
