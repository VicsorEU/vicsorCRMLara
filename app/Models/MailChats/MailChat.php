<?php

namespace App\Models\MailChats;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailChat extends Model
{
    use HasFactory;

    protected $table = 'mail_chats';

    protected $fillable = [
        'user_id',
        'name',
        'token',
        'email',
        'mail_type',
        'work_days',
        'work_from',
        'work_to',
        'widget_color',
        'is_verified',
    ];

    protected $casts = [
        'work_days' => 'string',
        'work_from' => 'string',
        'work_to' => 'string',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function mailChats(): HasMany
    {
        return $this->hasMany(MailChat::class, 'mail_chat_id', 'id');
    }
}
