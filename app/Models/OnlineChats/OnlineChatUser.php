<?php

namespace App\Models\OnlineChats;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnlineChatUser extends Model
{
    protected $table = 'online_chat_users';

    protected $fillable = [
        'online_chat_id',
        'auth_id',
        'name'
    ];
    public function onlineChat():  BelongsTo
    {
        return $this->belongsTo(OnlineChat::class, 'online_chat_id', 'id');
    }


    public function onlineChatData(): HasMany
    {
        return $this->hasMany(OnlineChatData::class, 'online_chat_user_id', 'id');
    }
}
