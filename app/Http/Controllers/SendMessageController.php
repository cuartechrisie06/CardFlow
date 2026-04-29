<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendMessageController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'conversation_id' => ['required', 'integer', 'exists:conversations,id'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        Log::info('messages.send.hit', [
            'conversation_id' => $validated['conversation_id'],
            'user_id' => $request->user()?->id,
        ]);

        $user = $request->user();

        $conversation = Conversation::query()
            ->withValidParticipants()
            ->forUser($user)
            ->with(['userOne:id,name,username', 'userTwo:id,name,username'])
            ->findOrFail($validated['conversation_id']);

        $receiver = $conversation->otherParticipant($user);
        abort_unless($receiver, 403);

        $message = DB::transaction(function () use ($conversation, $user, $receiver, $validated) {
            $message = Message::query()->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'receiver_id' => $receiver->id,
                'body' => $validated['body'],
                'message_type' => 'text',
            ]);

            $conversation->touch();

            return $message;
        });

        Log::info('messages.send.created', [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
        ]);

        $unreadCounts = [
            (string) $user->id => 0,
            (string) $receiver->id => Message::query()
                ->where('conversation_id', $conversation->id)
                ->where('receiver_id', $receiver->id)
                ->whereNull('read_at')
                ->count(),
        ];

        $event = new MessageSent($message, $unreadCounts);

        broadcast($event);

        if (! $request->expectsJson()) {
            return redirect()->route('messages.index', [
                'conversation' => $conversation->id,
            ])->setStatusCode(Response::HTTP_SEE_OTHER);
        }

        return response()->json([
            'ok' => true,
            'message_id' => $message->id,
            'event' => $event->payload(),
        ]);
    }
}
