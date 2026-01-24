<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessengerController extends Controller
{
    public function index(Request $request, $user_for_chat_view_id){

        // у обычных админов доступа нет
        if( (int)Auth::user()->role === 2 ){
            abort(403);
        }

        $user_owner_chats = User::where('id', $user_for_chat_view_id)->first();
        
        // проверка, что если роль не не админ, переписки смотреть можно только свои
        if(in_array((int)Auth::user()->id, [0, 1])){
            if( Auth::id() !== (int)$user_for_chat_view_id ){
                abort(403);
            }
        }

        $chats = [];

        if((int)$user_owner_chats->role === 1){
            $chats = Conversation::where('user_one_id', $user_for_chat_view_id) 
                ->with(['messages', 'userOne', 'userTwo'])
                ->get();
        }

        if((int)$user_owner_chats->role === 0){
            $chats = Conversation::where('user_two_id', $user_for_chat_view_id) 
                ->with(['messages', 'userOne', 'userTwo'])
                ->get();
        }


        $data = [
            'chats' => $chats,
            'user_owner_chats' => $user_owner_chats,
        ];

        return view('messenger.messenger', $data);
    }

    public function single(Request $request, $user_for_chat_view_id, $chatId){
        $user_owner_chats = User::where('id', $user_for_chat_view_id)->first();
        $current_user = Auth::user();


        // проверка, что если роль не не админ, переписки смотреть можно только свои
        if(in_array($current_user->role, ['0','1'])){
            if($current_user->id !== (int)$user_for_chat_view_id){
                abort(404);
            }
        }

        $chat = [];

        $chat = Conversation::where('id', $chatId) 
            ->with(['messages', 'userOne', 'userTwo'])
            ->firstOrFail();

        $dialog_with_user = $chat->userOne;
        if((int)$user_owner_chats->role === 1){
            $dialog_with_user = $chat->userTwo;
        }
            
        $data = [
            'chat' => $chat,
            'user_owner_chats' => $user_owner_chats,
            'current_user' => $current_user,
            'dialog_with_user' => $dialog_with_user
        ];

        return view('messenger.messenger_single', $data);
    }


    public function addMessage(Request $request){
        
        $request->validate([
            'content' => 'required|string|max:2000',
        ], [
        ]);
       
        $senderId = Auth::id();
        $content = $request->input('content');
        $conversation_id = $request->input('conversation_id');

        $conversation = Conversation::where('id', $conversation_id)->firstOrFail();
        
        $message = $conversation->messages()->create([
            'sender_id' => $senderId,
            'content' => $content,
        ]);

        $dialog_with_user = $conversation->userTwo;

        if((int)Auth::user()->role === 0 ){
            $dialog_with_user = $conversation->userOne;
        }

        return redirect()->route('messenger.single', [
            'id' => Auth::user()->id,
            'chatId' => $conversation->id
        ])->with([
            'chat' => $conversation,
            'user_for_chat_view' => Auth::user(),
            'current_user' => Auth::user(),
            'dialog_with_user' => $dialog_with_user
        ]);
    }


}
