<?php

namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\ChatGroupMember;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;


class ChatGroupController extends Controller
{
    public function index()
    {
        // Fetch all chat groups
        return response()->json(['chat_groups' => ChatGroup::with('members', 'messages')->get()]); ;
    }

    public function storeByAdmin(Request $request)
    {

        $adminRole = Role::where('name', 'admin')->first();
        $adminUser = User::where('role_id', $adminRole->id)->first()->id;

        if(auth()->id() != $adminUser)
        {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }
        $userIds = $request->input('user_ids');

        // Create a new chat group
        $chatGroup = ChatGroup::create([
            'name' => $request->name,
            'created_by' => auth()->id(),
        ]);

        // Optionally, add the creator as a member
        ChatGroupMember::create([
            'chat_group_id' => $chatGroup->id,
            'user_id' => auth()->id(),
        ]);

        // Add each user to the chat group as a member
        foreach ($userIds as $userId) {

            if(ChatGroupMember::where('user_id', $userId)->exists())
            {
                continue;
            }
            ChatGroupMember::create([
                'chat_group_id' => $chatGroup->id,
                'user_id' => $userId,
            ]);
        }

        return response()->json($chatGroup);
    }
    public function storeByUser(Request $request)
    {
        

        $user_id = auth()->id();
        $adminRole = Role::where('name', 'admin')->first();
        $adminUser = User::where('role_id', $adminRole->id)->first()->id;

        $chatGroupMember = ChatGroupMember::where('user_id', $user_id) ;

        if ($chatGroupMember->exists())
        {
            return response()->json(['message' => 'You are already a member of a group.' , 
            'chat_group'=> $chatGroupMember->first()->group],
             200);
        }
        // Create a new chat group
        $chatGroup = ChatGroup::create([
            'name' => 'Support for:'. auth()->user()->name, 
            'created_by' => $user_id,
        ]);

        ChatGroupMember::create([
            'chat_group_id' => $chatGroup->id,
            'user_id' => $user_id, // Add the authenticated user
        ]);

        ChatGroupMember::create([
            'chat_group_id' => $chatGroup->id,
            'user_id' => $adminUser, // Add the admin user
        ]);
    

        return response()->json($chatGroup);
    }




    public function show(ChatGroup $chatGroup)
    {
        return response()->json([
            'chat_group' => $chatGroup->load('members', 'messages'),
            ]) ;
    }
}
