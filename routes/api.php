<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OnlineChatController;

Route::prefix('communications/online-chat')->group(function () {
    Route::get('widget-settings/{token}', [OnlineChatController::class, 'getSettings'])->name('online-chat.widget.settings');
    Route::post('send', [OnlineChatController::class, 'sendMessage'])->name('online-chat.send.message');
    Route::get('messages/{token}', [OnlineChatController::class, 'getMessages'])->name('online-chat.messages');
});
