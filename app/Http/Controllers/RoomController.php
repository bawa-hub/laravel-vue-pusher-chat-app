<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\UserEndTyping;
use App\Events\UserTyping;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();

        return response()->json(['success' => true, 'data' => $rooms], 200);
    }

    public function onlineUsers()
    {
        $users = User::where('is_online', true)->get();

        return response()->json(['success' => true, 'data' => $users], 200);
    }

    public function store(Request $request)
    {
        Room::create(['name' => $request->name]);

        return response()->json(['success' => true], 201);
    }

    public function messages($roomId, Request $request)
    {
        logger("room", [$roomId]);
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 20);

        $messages = Message::where('room_id', $roomId)
            ->with('user:name,id')
            ->orderBy('created_at', 'desc')
            ->paginate($limit, ['*'], 'page', $page);

        // return response()->json([
        //     'success' => true,
        //     'data' => $messages->items(), // Get the messages for the current page
        //     'current_page' => $messages->currentPage(),
        //     'last_page' => $messages->lastPage(),
        //     'total' => $messages->total(),
        // ], 200);

        return response()->json(['success' => true, 'data' => $messages->items()], 200);
    }

    public function storeMessage(Request $request)
    {
        $filePath = null;
        if ($request->hasFile('file')) {
            logger("has fiue");
            $filePath = $request->file('file')->store('chat_files', 'public');
        }

        $message = Message::create([
            'user_id' => Auth::user()->id,
            'room_id' => $request->input('room_id'),
            'content' => $request->input('content'),
            'file_path' => $filePath
        ]);

        broadcast(new MessageSent($message))->toOthers();

        $message->user = ['name' => $message->user->name, 'id' => $message->user->id];

        return response()->json(['success' => true, 'data' => $message], 201);
    }

    public function searchMessage(Request $request)
    {
        $query = $request->input('query');
        $roomId = $request->input('room_id');

        if (!$query || !$roomId) {
            return response()->json(['success' => false, 'message' => 'Invalid parameters'], 400);
        }

        $messages = Message::where('room_id', $roomId)
            ->where('content', 'LIKE', '%' . $query . '%')
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $messages], 200);
    }

    public function userTyping()
    {
        $user = Auth::user();
        broadcast(new UserTyping($user))->toOthers();

        return response()->json(['success' => true], 200);
    }

    public function userEndTyping()
    {
        $user = Auth::user();
        broadcast(new UserEndTyping($user))->toOthers();

        return response()->json(['success' => true], 200);
    }
}
