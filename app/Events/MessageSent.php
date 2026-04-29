<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
        public array $unreadCounts,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('conversation.'.$this->message->conversation_id),
            new PrivateChannel('users.'.$this->message->sender_id.'.inbox'),
            new PrivateChannel('users.'.$this->message->receiver_id.'.inbox'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return $this->payload();
    }

    public function payload(): array
    {
        $message = $this->message->loadMissing([
            'sender:id,name,username',
            'receiver:id,name,username',
            'conversation.userOne:id,name,username',
            'conversation.userTwo:id,name,username',
        ]);

        return [
            'conversation' => [
                'id' => $message->conversation_id,
            ],
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'message_type' => $message->message_type,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'created_at' => $message->created_at?->toIso8601String(),
            ],
            'sender' => [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
                'username' => $message->sender->username,
            ],
            'receiver' => [
                'id' => $message->receiver->id,
                'name' => $message->receiver->name,
                'username' => $message->receiver->username,
            ],
            'participants' => [
                [
                    'id' => $message->conversation->userOne->id,
                    'name' => $message->conversation->userOne->name,
                    'username' => $message->conversation->userOne->username,
                ],
                [
                    'id' => $message->conversation->userTwo->id,
                    'name' => $message->conversation->userTwo->name,
                    'username' => $message->conversation->userTwo->username,
                ],
            ],
            'preview' => $message->body ?: 'Shared media',
            'unread_counts' => $this->unreadCounts,
        ];
    }
}
