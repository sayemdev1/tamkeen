<?php

namespace App\Services;

use App\Models\ChatGroup;
use App\Models\ChatGroupMember;

class ChatGroupService
{
    public function getChatGroupMessages(ChatGroup $chatGroup, $user_id)
    {
        $messages = $chatGroup->messages;

        foreach ($messages as $message) {
            $member = ChatGroupMember::where('id', $message->sender_id)->first();
            $message->is_mine = $user_id == $member->user_id;
        }

        return $messages;
    }
}
