<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\ActivityLogger;
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
            'body' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:jpeg,png,webp,gif,mp4,mov', 'max:10240'],
        ]);

        if (blank($validated['body'] ?? null) && ! $request->hasFile('attachment')) {
            return back()
                ->withErrors([
                    'body' => 'Please enter a message or attach a file.',
                ])
                ->withInput();
        }

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

        $attachmentPath = null;
        $attachmentType = null;
        $attachmentName = null;

        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $file = $request->file('attachment');
            $attachmentType = str_starts_with((string) $file->getMimeType(), 'video/') ? 'video' : 'image';
            $attachmentName = $file->getClientOriginalName();
            $filename = time().'_'.$user->id.'.'.$file->getClientOriginalExtension();
            $file->storeAs('message-attachments', $filename, 'public');
            $attachmentPath = $filename;
        }

        $message = DB::transaction(function () use ($conversation, $user, $receiver, $validated, $attachmentPath, $attachmentType, $attachmentName) {
            $message = Message::query()->create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'receiver_id' => $receiver->id,
                'body' => trim($validated['body'] ?? ''),
                'message_type' => $attachmentType ?? 'text',
                'attachment_path' => $attachmentPath,
                'attachment_type' => $attachmentType,
                'attachment_name' => $attachmentName,
            ]);

            $conversation->touch();

            return $message;
        });

        Log::info('messages.send.created', [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
        ]);

        app(ActivityLogger::class)->record(
            $receiver,
            'new_message',
            sprintf('@%s sent you a message', $user->username ?: $user->name),
            trim($message->body) !== '' ? trim($message->body) : 'Sent an attachment.',
            [
                'conversation_id' => $conversation->id,
                'message_id' => $message->id,
                'sender_id' => $user->id,
            ]
        );

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

    public function destroy(Request $request, Message $message): RedirectResponse
    {
        abort_if($message->sender_id !== $request->user()->id, 403, 'You can only delete your own messages.');

        $conversation = Conversation::query()
            ->withValidParticipants()
            ->forUser($request->user())
            ->findOrFail($message->conversation_id);

        $message->delete();
        $conversation->touch();

        return redirect()
            ->route('messages.index', ['conversation' => $conversation->id])
            ->with('success', 'Message deleted.');
    }
}
