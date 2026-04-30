<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Customers;
use App\Models\User;
use App\Models\Penjual;
use Illuminate\Http\Request;
use App\Events\ChatSent;
use App\Events\ChatDeleted;
use Laravel\Sanctum\PersonalAccessToken;

class ChatController extends Controller
{
    // =========================
    // 🔥 AMBIL USER DARI TOKEN
    // =========================
    private function getUserFromToken(Request $request)
    {
        $token = $request->query('token') 
            ?? $request->input('token') 
            ?? $request->bearerToken();

        if (!$token) return null;

        $accessToken = PersonalAccessToken::findToken($token);

        return $accessToken?->tokenable;
    }

    public function list(Request $request)
    {
        $user = $this->getUserFromToken($request);

        if (!$user) {
            abort(403, 'BELUM LOGIN');
        }

        $userId = $user->id;

        $users = Chat::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->get()
            ->map(function ($chat) use ($userId) {
                return $chat->sender_id == $userId
                    ? $chat->receiver_id
                    : $chat->sender_id;
            })
            ->unique()
            ->values();

        $listUser = Penjual::with('tenant')
            ->whereIn('users_id', $users)
            ->get();

        return view('customer.chat.list', [
            'listUser' => $listUser,
            'token' => $request->query('token') // 🔥 tambahan penting
        ]);
    }

    public function room(Request $request, $userId)
{
    $user = $this->getUserFromToken($request);

    if (!$user) {
        abort(403, 'BELUM LOGIN');
    }

    $penjual = \App\Models\Penjual::with('tenant')
        ->where('users_id', $userId)
        ->firstOrFail();

    return view('customer.chat.index', [
        'penjual' => $penjual,
        'token' => $request->query('token'),
        'myId' => $user->id // 🔥 penting
    ]);
}
    // =========================
    // 🔥 PENJUAL SIDE
    // =========================

    public function listPenjual(Request $request)
    {
        $user = $this->getUserFromToken($request);

        if (!$user) {
            abort(403, 'BELUM LOGIN');
        }

        $userId = $user->id;

        $users = Chat::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->get()
            ->map(function ($chat) use ($userId) {
                return $chat->sender_id == $userId
                    ? $chat->receiver_id
                    : $chat->sender_id;
                    
            })
            ->unique()
            ->values();

        $listUser = Customers::whereIn('users_id', $users)->get();
        // 🔥 TAMBAHAN: HITUNG UNREAD
    foreach ($listUser as $u) {
        $u->unread = Chat::where('sender_id', $u->users_id)
            ->where('receiver_id', $userId)
            ->where('is_read', 0)
            ->count();
    }

        return view('penjual.chat.list', compact('listUser'));
    }

    public function roomPenjual(Request $request, $userId)
    {
        $user = $this->getUserFromToken($request);

        if (!$user) {
            abort(403, 'BELUM LOGIN');
        }

        $customer = Customers::where('users_id', $userId)->first();

        if (!$customer) {
            abort(404, 'Customer tidak ditemukan');
        }
        // 🔥 TAMBAHAN: AUTO READ SAAT MASUK ROOM
    Chat::where('sender_id', $userId)
        ->where('receiver_id', $user->id)
        ->where('is_read', 0)
        ->update(['is_read' => 1]);

        return view('penjual.chat.index', compact('customer'));
    }

    public function getChatPenjual(Request $request, $userId)
{
    $user = $this->getUserFromToken($request);

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $myId = $user->id;
     // 🔥 TAMBAHAN: AUTO READ SAAT FETCH CHAT
    Chat::where('sender_id', $userId)
        ->where('receiver_id', $myId)
        ->where('is_read', 0)
        ->update(['is_read' => 1]);


    $chats = Chat::where(function($q) use ($myId, $userId) {
            $q->where('sender_id', $myId)
              ->where('receiver_id', $userId);
        })
        ->orWhere(function($q) use ($myId, $userId) {
            $q->where('sender_id', $userId)
              ->where('receiver_id', $myId);
        })
        ->orderBy('created_at', 'asc')
        ->get()
        ->map(function ($chat) use ($myId) {
            return [
                'id' => $chat->id,
                'sender_id' => $chat->sender_id,
                'receiver_id' => $chat->receiver_id,
                'message' => $chat->message,
                'is_me' => $chat->sender_id == $myId // 🔥 PENENTU KANAN/KIRI
            ];
        });

    return response()->json($chats);
}
    // =========================
    // 🔥 CHAT LOGIC
    // =========================

    public function getChat(Request $request, $userId)
{
    $user = $this->getUserFromToken($request);

    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $myId = $user->id;

    $chats = Chat::where(function($q) use ($myId, $userId) {
            $q->where('sender_id', $myId)
              ->where('receiver_id', $userId);
        })
        ->orWhere(function($q) use ($myId, $userId) {
            $q->where('sender_id', $userId)
              ->where('receiver_id', $myId);
        })
        ->orderBy('created_at', 'asc')
        ->get();

    return response()->json($chats);
}

    public function send(Request $request)
{
    try {

        // 🔥 ambil user dari token (SAMA seperti getChat)
        $user = $this->getUserFromToken($request);

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$request->receiver_id || !$request->message) {
            return response()->json(['error' => 'Data tidak lengkap'], 400);
        }

        Chat::create([
            'sender_id' => $user->id, // 🔥 INI YANG BENAR
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        return response()->json(['status' => 'success']);

    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function delete(Request $request, $id)
    {
        $user = $this->getUserFromToken($request);

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chat = Chat::findOrFail($id);

        if ($chat->sender_id != $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chat->update([
            'is_deleted' => true,
            'message' => null
        ]);

        broadcast(new ChatDeleted($chat))->toOthers();

        return response()->json(['status' => 'deleted']);
    }
}