<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkTimerController;
use App\Http\Controllers\TimerController;

// глобальные таймеры (не привязаны к конкретной задаче)
Route::middleware('auth')->group(function () {
    Route::get ('/time/active',   [WorkTimerController::class, 'active'])->name('time.active');
    Route::post('/time/start',    [WorkTimerController::class, 'start'])->name('time.start');
    Route::post('/time/stop',     [WorkTimerController::class, 'stop'])->name('time.stop');

    Route::post('/time',          [WorkTimerController::class, 'store'])->name('time.store');
    Route::get ('/time',          [WorkTimerController::class, 'index'])->name('time.index');
    Route::delete('/time/{timer}',[WorkTimerController::class, 'destroy'])->whereNumber('timer')->name('time.destroy');
    Route::get ('/time/summary',  [WorkTimerController::class, 'summary'])->name('time.summary');

    // служебные маршруты таймера
    Route::get('/timer/active',   [TimerController::class, 'active'])->name('kanban.timer.active');
    Route::delete('/timers/{timer}', [TimerController::class, 'destroy'])->name('timers.destroy');
});
