<?php

namespace App\Models\MailChats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailChatData extends Model
{
    use HasFactory;

    const STATUS_CREATED = 1;
    const STATUS_SENT = 2;
    const STATUS_READ = 3;

    const TYPE_INCOMING = 1;
    const TYPE_OUTGOING = 2;

    protected $table = 'mail_chat_data';

    protected $fillable = [
        'mail_chat_id',
        'status',
        'type',
        'title',
        'message',
        'notified',
    ];

    protected $casts = [
        'status' => 'integer',
        'type' => 'integer',
        'notified' => 'boolean',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mailChat(): BelongsTo
    {
        return $this->belongsTo(MailChat::class, 'mail_chat_id', 'id');
    }
}
