<?php

use App\Http\Controllers\Communications\CommunicationController;
use App\Http\Controllers\Communications\OnlineChatController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::group(['prefix' => 'communications'], function () {
        Route::get('/', [CommunicationController::class,'index'])->name('communications.index');
        Route::get('/ajax', [CommunicationController::class,'indexAjax'])->name('communications.index_ajax');

        Route::post('/store', [CommunicationController::class,'store'])->name('communications.store');
        Route::get('/communications/{chat}', [CommunicationController::class, 'show'])->name('communications.show');
        Route::get('/unread-count-messages', [CommunicationController::class, 'unreadCountMessages'])->name('communications.unread_count_messages');

        Route::group(['prefix' => 'online-chat'], function () {
            Route::get('/{onlineChat}/edit', [OnlineChatController::class,'edit'])->name('online-chat.edit');
            Route::put('/{onlineChat}/update', [OnlineChatController::class,'update'])->name('online-chat.update');

            Route::delete('/{onlineChat}/destroy', [OnlineChatController::class,'destroy'])->name('online-chat.destroy');

            Route::get('/{onlineChat}//unread-count-messages', [OnlineChatController::class, 'unreadCountMessages'])->name('online-chat.unread_count_messages');

            Route::get('/{onlineChat}/messages', [OnlineChatController::class,'listOfMessages'])->name('online_chat.messages');
            Route::post('/send', [OnlineChatController::class,'sendMessage'])->name('online_chat.send_message');

        });
    });
});
