<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::all();

        return response()->json(['success' => true, 'data' => $rooms], 200);
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
        logger("files", [$request->hasFile('file')]);

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
}
