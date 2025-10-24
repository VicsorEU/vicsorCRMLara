<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OnlineChatController;

Route::prefix('communications/online-chat')->group(function () {
    Route::get('widget-settings/{token}', [OnlineChatController::class, 'getSettings'])->name('online-chat.widget.settings');
    Route::post('send', [OnlineChatController::class, 'sendMessage'])->name('online-chat.send.message');

    Route::get('check-new', [OnlineChatController::class, 'checkOnNewMessages'])->name('online-chat.check-new-messages');
    Route::get('messages/{token}/{authId}', [OnlineChatController::class, 'getMessages'])->name('online-chat.messages');

    Route::post('update-status', [OnlineChatController::class, 'updateMessageStatus'])->name('online-chat.update-status');
    Route::post('register-user', [OnlineChatController::class, 'registerUser']);
});
