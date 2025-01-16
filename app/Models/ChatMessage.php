<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'chat_group_id', 'message'];

    public function group()
    {
        return $this->belongsTo(ChatGroup::class);
    }

    public function member()
    {
        return $this->belongsTo(ChatGroupMember::class, 'sender_id', 'id');
    }
}
