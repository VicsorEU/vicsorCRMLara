<?php

use App\Http\Controllers\Communications\CommunicationController;
use App\Http\Controllers\Communications\MailChatController;
use App\Http\Controllers\Communications\OnlineChatController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::group(['prefix' => 'communications'], function () {
        Route::get('/', [CommunicationController::class,'index'])->name('communications.index');
        Route::get('/ajax', [CommunicationController::class,'indexAjax'])->name('communications.index_ajax');

        Route::group(['prefix' => 'online-chat'], function () {
            Route::get('/{onlineChat}', [OnlineChatController::class, 'show'])->name('online-chat.show');
            Route::get('/{onlineChat}/{onlineChatUser}/unread-count-messages', [OnlineChatController::class, 'unreadCountMessages'])->name('online-chat.unread_count_messages');

            Route::get('/{onlineChat}/users-list', [OnlineChatController::class,'listOfUsers'])->name('online_chat.users_list');
            Route::get('/{onlineChat}/messages', [OnlineChatController::class,'listOfMessages'])->name('online_chat.messages');
            Route::post('/send', [OnlineChatController::class,'sendMessage'])->name('online_chat.send_message');
        });

        Route::group(['prefix' => 'email-chat'], function () {
            Route::get('/{mailChat}', [MailChatController::class, 'show'])->name('email-chat.show');
            Route::get('/{mailChat}/messages', [MailChatController::class,'listOfMessages'])->name('email-chat.messages');

            Route::post('/{mailChat}/send', [MailChatController::class,'sendMessage'])->name('email-chat.send_message');
        });

        Route::get('messages/check-new', [OnlineChatController::class, 'checkOnNewMessages'])->name('online-chat.check-new-messages');
    });
});
