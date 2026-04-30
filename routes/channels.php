<?php
use Illuminate\Support\Facades\Auth;

Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return true; // sementara biar lolos
});