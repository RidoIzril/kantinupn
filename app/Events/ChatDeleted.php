<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatDeleted implements ShouldBroadcast
{
    public $chatId;
    public $orderId;

    public function __construct($chat)
    {
        $this->chatId = $chat->id;
        $this->orderId = $chat->order_id;
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->orderId);
    }
}