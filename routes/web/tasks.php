<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\KanbanController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimerController;
use App\Http\Controllers\TaskFileController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\Tasks\TaskTaxonomyController;

Route::middleware('auth')->group(function () {

    // ===========================
    // READ-ONLY (просмотр)
    // ===========================

    // Канбан-доска (просмотр)
    Route::get('/tasks/kanban/{board?}', [KanbanController::class, 'show'])
        ->whereNumber('board')
        ->name('kanban.show');

    // Просмотр конкретной задачи
    Route::get('/tasks/{task}', [TaskController::class, 'show'])
        ->whereNumber('task')
        ->name('tasks.show');

    // Список подзадач (просмотр)
    Route::get('/tasks/{task}/subtasks', [SubtaskController::class, 'index'])
        ->whereNumber('task')
        ->name('subtasks.index');

    // Текущий активный таймер (для панели/индикатора)
    Route::get('/timer/active', [TimerController::class, 'active'])
        ->name('kanban.timer.active');


    // ===========================
    // MUTATIONS (требует full-доступ)
    // ===========================
    Route::middleware(['access:projects,full'])->group(function () {

        // Спец-роут без ID — перетаскивание задач между колонками
        Route::post('/tasks/move', [TaskController::class, 'move'])->name('tasks.move');

        // Создание задачи
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');

        // Все мутации конкретной задачи
        Route::prefix('/tasks/{task}')->whereNumber('task')->group(function () {

            // Обновление/удаление задачи
            // (на фронте update идёт POST-ом)
            Route::post('/',   [TaskController::class, 'update'])->name('tasks.update');
            Route::delete('/', [TaskController::class, 'destroy'])->name('tasks.destroy');

            // Таймеры по задаче
            Route::post('/timer/start', [TimerController::class, 'start'])->name('kanban.timer.start');
            Route::post('/timer/stop',  [TimerController::class, 'stop'])->name('kanban.timer.stop');

            // Файлы из формы задачи
            Route::post('/files', [TaskFileController::class, 'store'])->name('tasks.files.store');

            // Комментарии к задаче
            Route::post('/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');

            // Таксономии задачи
            Route::post('/taxonomy/sync', [TaskTaxonomyController::class, 'sync'])->name('tasks.taxonomy.sync');
        });

        // Отметить задачу выполненной / вернуть в работу
        Route::patch('/tasks/{task}/complete', [TaskController::class, 'markComplete'])
            ->whereNumber('task')
            ->name('tasks.complete');

        // Подзадачи (мутации)
        Route::post  ('/tasks/{task}/subtasks',       [SubtaskController::class, 'store'])
            ->whereNumber('task')->name('subtasks.store');
        Route::patch ('/subtasks/{subtask}',          [SubtaskController::class, 'update'])
            ->whereNumber('subtask')->name('subtasks.update');
        Route::delete('/subtasks/{subtask}',          [SubtaskController::class, 'destroy'])
            ->whereNumber('subtask')->name('subtasks.destroy');
        Route::patch ('/subtasks/{subtask}/complete', [SubtaskController::class, 'complete'])
            ->whereNumber('subtask')->name('subtasks.complete');

        // Таймеры для подзадач
        Route::post('/subtasks/{subtask}/timer/start', [SubtaskController::class, 'timerStart'])
            ->whereNumber('subtask')->name('subtasks.timer.start');
        Route::post('/subtasks/{subtask}/timer/stop',  [SubtaskController::class, 'timerStop'])
            ->whereNumber('subtask')->name('subtasks.timer.stop');

        // Удаление файла по ID (вне /tasks/{task})
        Route::delete('/files/{file}', [TaskFileController::class, 'destroy'])
            ->whereNumber('file')->name('tasks.files.delete');

        // Отдельные эндпоинты загрузчика (черновики, drag&drop и т.п.)
        Route::post('/task-files/upload',               [TaskFileController::class, 'upload'])->name('task-files.upload');
        Route::delete('/task-files/{attachment}',       [TaskFileController::class, 'destroy'])
            ->whereNumber('attachment')->name('task-files.destroy');
        Route::delete('/task-files/draft/{attachment}', [TaskFileController::class, 'destroyDraft'])
            ->whereNumber('attachment')->name('task-files.destroyDraft');

        // Удаление таймера по ID
        Route::delete('/timers/{timer}', [TimerController::class, 'destroy'])
            ->whereNumber('timer')->name('timers.destroy');
    });
});
