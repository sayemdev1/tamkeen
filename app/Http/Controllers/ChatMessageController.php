<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use App\Models\ChatMessage;
use App\Services\ChatGroupService;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{

    protected $chatGroupService;

    public function __construct(ChatGroupService $chatGroupService)
    {
        $this->chatGroupService = $chatGroupService;
    }
    
    public function index(ChatGroup $chatGroup)
    {
        $user_id = auth()->id();
        $messages = $this->chatGroupService->getChatGroupMessages($chatGroup, $user_id);

        return response()->json(['messages' => $messages]);
    }

    public function store(ChatGroup $chatGroup , Request $request)
    {
        $sender_id = ChatGroupMember::where('user_id',auth()->id())->first()->id;
        // Send a new message to a chat group
        $message = ChatMessage::create([
            'chat_group_id' => $chatGroup->id,
            'sender_id' => $sender_id,
            'message' => $request->message,
        ]);

         event(new MessageSent($message));

        return response()->json($message);
    }
}
