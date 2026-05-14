<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

use App\Models\Chat;
use App\Models\Customers;
use App\Models\Penjual;

use App\Events\ChatDeleted;
// use App\Events\ChatSent; // kalau kamu memang broadcast saat send

class ChatApiController extends Controller
{
    // =========================
    // AUTH (SAMA KAYA PUNYAMU)
    // =========================
    private function resolveUser(Request $request)
    {
        $token = $request->bearerToken()
            ?? $request->query('token')
            ?? $request->input('token');

        if (!$token) return null;

        $accessToken = PersonalAccessToken::findToken($token);

        return $accessToken?->tokenable;
    }

    private function unauthorized()
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 401);
    }

    // =========================
    // USERS LIST (CHAT LIST)
    // =========================

    /**
     * GET /api/chat/users
     * List lawan chat berdasarkan tabel chats (sender/receiver).
     * Return: user_ids + (optional) data penjual/customer + unread count per user.
     */
    public function users(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) return $this->unauthorized();

        $myId = $user->id;

        // ambil user id lawan chat yang pernah berinteraksi
        $peerIds = Chat::where('sender_id', $myId)
            ->orWhere('receiver_id', $myId)
            ->get()
            ->map(function ($chat) use ($myId) {
                return $chat->sender_id == $myId ? $chat->receiver_id : $chat->sender_id;
            })
            ->unique()
            ->values()
            ->toArray();

        // unread per peer (yang mengirim ke saya)
        $unreadRows = Chat::selectRaw('sender_id, COUNT(*) as cnt')
            ->where('receiver_id', $myId)
            ->where(function ($q) {
                $q->where('is_read', 0)->orWhereNull('is_read');
            })
            ->where('is_deleted', 0)
            ->groupBy('sender_id')
            ->get();

        $unreadMap = [];
        foreach ($unreadRows as $r) {
            $unreadMap[(string) $r->sender_id] = (int) $r->cnt;
        }

        // Ambil info lawan chat:
        // - kalau saya customer: biasanya lawan = penjual
        // - kalau saya penjual: biasanya lawan = customer
        // Tapi biar fleksibel, kita ambil keduanya dan gabungkan.

        $penjualPeers = Penjual::with('tenant')
            ->whereIn('users_id', $peerIds)
            ->get()
            ->keyBy('users_id');

        $customerPeers = Customers::whereIn('users_id', $peerIds)
            ->get()
            ->keyBy('users_id');

        $peers = [];
        foreach ($peerIds as $pid) {
            $peers[] = [
                'user_id' => (int) $pid,
                'unread' => (int) ($unreadMap[(string) $pid] ?? 0),
                'penjual' => $penjualPeers->get($pid),
                'customer' => $customerPeers->get($pid),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'me' => [
                    'id' => $myId,
                    'role' => $user->role ?? null,
                ],
                'peers' => $peers,
            ]
        ], 200);
    }

    // =========================
    // ROOM / GET CHAT
    // =========================

    /**
     * GET /api/chat/room/{userId}
     * Ambil semua chat antara saya dan userId, urut asc.
     * Auto mark read chat dari userId ke saya.
     */
    public function room(Request $request, $userId)
    {
        $user = $this->resolveUser($request);
        if (!$user) return $this->unauthorized();

        $myId = $user->id;
        $peerId = (int) $userId;

        // Mark as read: chat dari peer -> saya
        Chat::where('sender_id', $peerId)
            ->where('receiver_id', $myId)
            ->where(function ($q) {
                $q->where('is_read', 0)->orWhereNull('is_read');
            })
            ->update(['is_read' => 1]);

        $chats = Chat::where(function ($q) use ($myId, $peerId) {
                $q->where('sender_id', $myId)->where('receiver_id', $peerId);
            })
            ->orWhere(function ($q) use ($myId, $peerId) {
                $q->where('sender_id', $peerId)->where('receiver_id', $myId);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($chat) use ($myId) {
                return [
                    'id' => $chat->id,
                    'sender_id' => $chat->sender_id,
                    'receiver_id' => $chat->receiver_id,
                    'message' => $chat->message,
                    'is_read' => (int) ($chat->is_read ?? 0),
                    'is_deleted' => (int) ($chat->is_deleted ?? 0),
                    'created_at' => $chat->created_at,
                    'is_me' => (int) ($chat->sender_id == $myId),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'peer_id' => $peerId,
                'chats' => $chats
            ]
        ], 200);
    }

    // =========================
    // SEND CHAT
    // =========================

    /**
     * POST /api/chat/send
     * Body: { receiver_id: int, message: string }
     */
    public function send(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) return $this->unauthorized();

        $validated = $request->validate([
            'receiver_id' => 'required|integer',
            'message'     => 'required|string',
        ]);

        $chat = Chat::create([
            'sender_id'   => $user->id,
            'receiver_id' => $validated['receiver_id'],
            'message'     => $validated['message'],
            'is_read'     => 0,
            'is_deleted'  => 0,
        ]);

        // Kalau kamu pakai broadcast realtime saat send, bisa nyalakan ini:
        // broadcast(new ChatSent($chat))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Chat terkirim',
            'data' => $chat
        ], 201);
    }

    // =========================
    // DELETE CHAT (SOFT DELETE)
    // =========================

    /**
     * DELETE /api/chat/{id}
     */
    public function delete(Request $request, $id)
    {
        $user = $this->resolveUser($request);
        if (!$user) return $this->unauthorized();

        $chat = Chat::findOrFail($id);

        if ((int) $chat->sender_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        $chat->update([
            'is_deleted' => 1,
            'message' => null,
        ]);

        broadcast(new ChatDeleted($chat))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Chat dihapus',
            'data' => ['id' => $chat->id]
        ], 200);
    }

    // =========================
    // UNREAD COUNT (TOTAL)
    // =========================

    /**
     * GET /api/chat/unread-count
     */
    public function unreadCount(Request $request)
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'success' => true,
                'data' => ['count' => 0]
            ], 200);
        }

        $count = Chat::where('receiver_id', $user->id)
            ->where(function ($q) {
                $q->where('is_read', 0)->orWhereNull('is_read');
            })
            ->where('is_deleted', 0)
            ->count();

        return response()->json([
            'success' => true,
            'data' => ['count' => (int) $count]
        ], 200);
    }

    // =========================
    // UNREAD BY USER (SENDER)
    // =========================

    /**
     * GET /api/chat/unread-by-user
     * Return: { sender_id: count, ... }
     */
    public function unreadByUser(Request $request)
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return response()->json([
                'success' => true,
                'data' => ['counts' => (object)[]]
            ], 200);
        }

        $rows = Chat::selectRaw('sender_id, COUNT(*) as cnt')
            ->where('receiver_id', $user->id)
            ->where(function ($q) {
                $q->where('is_read', 0)->orWhereNull('is_read');
            })
            ->where('is_deleted', 0)
            ->groupBy('sender_id')
            ->get();

        $counts = [];
        foreach ($rows as $r) {
            $counts[(string) $r->sender_id] = (int) $r->cnt;
        }

        return response()->json([
            'success' => true,
            'data' => ['counts' => $counts]
        ], 200);
    }
}