<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load(['sender', 'attachments']);
    }
    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->message->chat_id);
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'chat_id' => $this->message->chat_id,
            'body' => $this->message->body,
            // add sender_id for clients comparing against current user
            'sender_id' => $this->message->sender_id,
            'sender' => [
                'id' => $this->message->sender_id,
                'name' => $this->message->sender->name ?? 'User',
                'is_admin' => (bool)$this->message->sender_is_admin,
            ],
            'attachments' => $this->message->attachments->map(fn($a) => [
                'id' => $a->id,
                'path' => $a->path,
                'url' => asset('storage/' . $a->path),
                'mime' => $a->mime,
                'size' => $a->size,
            ]),
            'created_at' => $this->message->created_at,
            'created_at_iso' => $this->message->created_at?->toISOString(),
            'sender_is_admin' => (bool)$this->message->sender_is_admin,
        ];
    }
}
