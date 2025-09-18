<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TypingEvent implements ShouldBroadcast
{
    public function __construct(
        public int $chatId,
        public int $userId,
        public string $userName,
        public bool $isAdmin,
        public bool $typing // true = started, false = stopped
    ) {}

    public function broadcastOn() {
        return new PrivateChannel('chat.' . $this->chatId);
    }

    public function broadcastAs() {
        return 'chat.typing';
    }

    public function broadcastWith() {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'is_admin' => $this->isAdmin,
            'typing' => $this->typing,
        ];
    }
}
