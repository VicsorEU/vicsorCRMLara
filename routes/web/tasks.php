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
    // канбан
    Route::get('/tasks/kanban/{board?}', [KanbanController::class, 'show'])
        ->whereNumber('board')->name('kanban.show');

    // спец-роут без ID должен идти первым
    Route::post('/tasks/move', [TaskController::class, 'move'])->name('tasks.move');

    // создание/просмотр задачи
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->whereNumber('task')->name('tasks.show');

    // все мутации конкретной задачи
    Route::prefix('/tasks/{task}')->whereNumber('task')->group(function () {
        Route::post('/',   [TaskController::class, 'update'])->name('tasks.update');   // фронт шлёт POST
        Route::delete('/', [TaskController::class, 'destroy'])->name('tasks.destroy');

        // таймеры по задаче
        Route::post('/timer/start', [TimerController::class, 'start'])->name('kanban.timer.start');
        Route::post('/timer/stop',  [TimerController::class, 'stop'])->name('kanban.timer.stop');

        // файлы из формы задачи
        Route::post('/files', [TaskFileController::class,'store'])->name('tasks.files.store');

        // комментарии
        Route::post('/comments', [TaskCommentController::class,'store'])->name('tasks.comments.store');

        // таксономии задачи
        Route::post('/taxonomy/sync', [TaskTaxonomyController::class, 'sync'])->name('tasks.taxonomy.sync');
    });

    // отметить задачу выполненной / вернуть в работу
    Route::patch('/tasks/{task}/complete', [TaskController::class, 'markComplete'])
        ->whereNumber('task')->name('tasks.complete');

    // подзадачи
    Route::get   ('/tasks/{task}/subtasks',            [SubtaskController::class,'index'])->whereNumber('task')->name('subtasks.index');
    Route::post  ('/tasks/{task}/subtasks',            [SubtaskController::class,'store'])->whereNumber('task')->name('subtasks.store');
    Route::patch ('/subtasks/{subtask}',               [SubtaskController::class,'update'])->whereNumber('subtask')->name('subtasks.update');
    Route::delete('/subtasks/{subtask}',               [SubtaskController::class,'destroy'])->whereNumber('subtask')->name('subtasks.destroy');
    Route::patch ('/subtasks/{subtask}/complete',      [SubtaskController::class,'complete'])->whereNumber('subtask')->name('subtasks.complete');

    // таймеры у подзадачи
    Route::post  ('/subtasks/{subtask}/timer/start',   [SubtaskController::class,'timerStart'])->whereNumber('subtask')->name('subtasks.timer.start');
    Route::post  ('/subtasks/{subtask}/timer/stop',    [SubtaskController::class,'timerStop'])->whereNumber('subtask')->name('subtasks.timer.stop');
});
