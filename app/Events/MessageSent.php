<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    use SerializesModels;

    public Message $message;

    public function __construct(Message $message) {
        $this->message = $message->load(['sender','attachments']);
    }
    public function broadcastOn() {
        return new PrivateChannel('chat.' . $this->message->chat_id);
    }

    public function broadcastAs() {
        return 'message.sent';
    }

    public function broadcastWith() {
        return [
            'id' => $this->message->id,
            'chat_id' => $this->message->chat_id,
            'body' => $this->message->body,
            'sender' => [
                'id' => $this->message->sender_id,
                'name' => $this->message->sender->name ?? 'User',
                'is_admin' => (bool)$this->message->sender_is_admin,
            ],
            'attachments' => $this->message->attachments->map(fn($a)=>[
                'url' => asset('storage/'.$a->path),
                'mime' => $a->mime,
                'size' => $a->size,
            ]),
            'created_at' => $this->message->created_at?->toIso8601String(),
            'sender_is_admin' => (bool)$this->message->sender_is_admin, // Thêm field này để debug
        ];
    }
}
