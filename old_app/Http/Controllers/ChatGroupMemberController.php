<?php

namespace App\Http\Controllers;

use App\Models\ChatGroupMember;
use Illuminate\Http\Request;

class ChatGroupMemberController extends Controller
{
    public function store(Request $request)
    {
        // Add a member to a chat group
        $member = ChatGroupMember::create([
            'chat_group_id' => $request->chat_group_id,
            'user_id' => $request->user_id,
        ]);

        return response()->json($member);
    }
}
