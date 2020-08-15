<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Message;
use App\User;
use App\Events\MessageSent;
class ChatsController extends Controller
{
    //
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index() {
    	return view('chats');
    }

    public function fetchMessage(Request $request)
    {
        // user_id is recognize the Sender.
        return Message::with('user')
            // messages I sent to specific receiver.
            ->where([
                'user_id' => auth()->user()->id,
                'receiver_id' => $request->get('receiver'),
            ])
            //messages that receiver sent to me.
            ->orWhere([
                'user_id' => $request->get('receiver'),
                'receiver_id' => auth()->user()->id
            ])
            ->get();
    }

    public function sendMessage(Request $request)
    {
        $message = auth()->user()->messages()->create([
            'message' => $request->message,
            'receiver_id' => $request->receiver
        ]);

        broadcast(new MessageSent($message->load('user')))->toOthers();
        return ['status' => 'success'];
    }


    public function getUsers() {
        return User::where('id', '<>', auth()->user()->id)->get();
    }
}
