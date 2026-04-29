<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarkConversationReadController extends Controller
{
    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        $conversation = Conversation::query()
            ->withValidParticipants()
            ->forUser($user)
            ->findOrFail($conversation->id);

        Message::query()
            ->where('conversation_id', $conversation->id)
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return response()->json(['ok' => true]);
    }
}
