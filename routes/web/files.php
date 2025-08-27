<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskFileController;

// удаление файла по ID (вне /tasks/{task})
Route::middleware('auth')->group(function () {
    Route::delete('/files/{file}', [TaskFileController::class,'destroy'])
        ->whereNumber('file')->name('tasks.files.delete');

    // отдельные URL загрузчика (черновики, drag&drop и т.п.)
    Route::post('/task-files/upload', [TaskFileController::class, 'upload'])->name('task-files.upload');
    Route::delete('/task-files/{attachment}', [TaskFileController::class, 'destroy'])
        ->whereNumber('attachment')->name('task-files.destroy');

    // отдельный URL для удаления черновых вложений (не конфликтует с destroy)
    Route::delete('/task-files/draft/{attachment}', [TaskFileController::class, 'destroyDraft'])
        ->whereNumber('attachment')->name('task-files.destroyDraft');
});
