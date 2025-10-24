<?php

namespace App\Models\OnlineChats;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineChatData extends Model
{
    protected $table = 'online_chat_data';

    const STATUS_CREATED = 1;
    const STATUS_SENT = 2;
    const STATUS_READ = 3;

    const TYPE_INCOMING = 1;
    const TYPE_OUTGOING = 2;

    protected $fillable = [
        'online_chat_id',
        'online_chat_user_id',
        'status',
        'type',
        'message',
        'source_url',
        'notified',
    ];

    public function onlineChat():  BelongsTo
    {
        return $this->belongsTo(OnlineChat::class, 'online_chat_id', 'id');
    }

    public function onlineChatUser():  BelongsTo
    {
        return $this->belongsTo(OnlineChatUser::class, 'online_chat_user_id', 'id');
    }
}
