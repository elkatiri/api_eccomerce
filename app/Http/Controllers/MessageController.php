<?php
namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        return response()->json(Message::latest()->get(), 200);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string'
        ]);

        $message = Message::create($request->all());

        return response()->json([
            'message' => 'Message sent successfully!',
            'data' => $message
        ], 201);
    }

    public function show(Message $message)
    {
        return response()->json($message, 200);
    }

    public function update(Request $request, Message $message)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string'
        ]);

        $message->update($request->all());

        return response()->json([
            'message' => 'Message updated successfully!',
            'data' => $message
        ], 200);
    }

    public function destroy(Message $message)
    {
        $message->delete();

        return response()->json(['message' => 'Message deleted successfully!'], 200);
    }
}
