<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'created_by'
    ];

    public function members()
    {
        return $this->hasMany(ChatGroupMember::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'chat_group_id', 'id');
    }
}
