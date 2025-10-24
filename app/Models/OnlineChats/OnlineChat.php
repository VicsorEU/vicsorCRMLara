<?php

namespace App\Models\OnlineChats;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnlineChat extends Model
{
    protected $table = 'online_chats';

    protected $fillable = [
        'user_id',
        'name',
        'token',
        'work_days',
        'work_from',
        'work_to',
        'widget_color',
        'telegram',
        'instagram',
        'facebook',
        'viber',
        'whatsapp',
        'title',
        'online_text',
        'offline_text',
        'placeholder',
        'greeting_offline',
        'greeting_online',
    ];

    /**
     * @return BelongsTo
     */
    public function user():  BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function onlineChatData(): HasMany
    {
        return $this->hasMany(OnlineChatData::class, 'online_chat_id', 'id');
    }

    public function onlineChatUsers(): HasMany
    {
        return $this->hasMany(OnlineChatData::class, 'online_chat_user_id', 'id');
    }
}
