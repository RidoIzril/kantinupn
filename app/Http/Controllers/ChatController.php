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
            'token' => $request->query('token')
        ]);
    }

    public function room(Request $request, $userId)
    {
        $user = $this->getUserFromToken($request);

        if (!$user) {
            abort(403, 'BELUM LOGIN');
        }

        // ✅ CUSTOMER MENJADI RECEIVER
        // ✅ SAAT BUKA ROOM → CHAT DARI PENJUAL MENJADI READ

        Chat::where('sender_id', $userId)
            ->where('receiver_id', $user->id)
            ->where(function ($q) {
                $q->where('is_read', 0)
                  ->orWhereNull('is_read');
            })
            ->update([
                'is_read' => 1
            ]);

        $penjual = \App\Models\Penjual::with('tenant')
            ->where('users_id', $userId)
            ->firstOrFail();

        return view('customer.chat.index', [
            'penjual' => $penjual,
            'token' => $request->query('token'),
            'myId' => $user->id
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
                ->where(function ($q) {
                    $q->where('is_read', 0)
                      ->orWhereNull('is_read');
                })
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

        // ✅ PENJUAL MENJADI RECEIVER
        // ✅ SAAT MASUK ROOM → CHAT CUSTOMER MENJADI READ

        Chat::where('sender_id', $userId)
            ->where('receiver_id', $user->id)
            ->where(function ($q) {
                $q->where('is_read', 0)
                  ->orWhereNull('is_read');
            })
            ->update([
                'is_read' => 1
            ]);

        return view('penjual.chat.index', compact('customer'));
    }

    public function getChatPenjual(Request $request, $userId)
    {
        $user = $this->getUserFromToken($request);

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $myId = $user->id;

        // ✅ AUTO READ REALTIME

        Chat::where('sender_id', $userId)
            ->where('receiver_id', $myId)
            ->where(function ($q) {
                $q->where('is_read', 0)
                  ->orWhereNull('is_read');
            })
            ->update([
                'is_read' => 1
            ]);

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
                    'is_me' => $chat->sender_id == $myId
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

        // ✅ CUSTOMER MENJADI RECEIVER
        // ✅ REALTIME READ SAAT FETCH CHAT

        Chat::where('sender_id', $userId)
            ->where('receiver_id', $myId)
            ->where(function ($q) {
                $q->where('is_read', 0)
                  ->orWhereNull('is_read');
            })
            ->update([
                'is_read' => 1
            ]);

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

            // 🔥 ambil user dari token
            $user = $this->getUserFromToken($request);

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if (!$request->receiver_id || !$request->message) {
                return response()->json(['error' => 'Data tidak lengkap'], 400);
            }

            Chat::create([

                'sender_id'   => $user->id,
                'receiver_id' => $request->receiver_id,
                'message'     => $request->message,

                // ✅ PENTING
                'is_read'     => 0,
                'is_deleted'  => 0,
            ]);

            return response()->json([
                'status' => 'success'
            ]);

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

    // =========================
    // ✅ TAMBAHAN: UNREAD COUNT (TOTAL)
    // =========================

    public function unreadCount(Request $request)
    {
        $user = $this->getUserFromToken($request);

        if (!$user) {
            return response()->json(['count' => 0], 200);
        }

        $count = Chat::where('receiver_id', $user->id)
            ->where(function ($q) {
                $q->where('is_read', 0)
                  ->orWhereNull('is_read');
            })
            ->where('is_deleted', 0)
            ->count();

        return response()->json([
            'count' => (int) $count
        ], 200);
    }

    // =========================
    // ✅ TAMBAHAN: UNREAD BY TENANT
    // =========================

    public function unreadByTenant(Request $request)
    {
        $user = $this->getUserFromToken($request);

        if (!$user) {
            return response()->json([
                'counts' => (object)[]
            ], 200);
        }

        $rows = Chat::selectRaw('sender_id, COUNT(*) as cnt')

            ->where('receiver_id', $user->id)

            ->where(function ($q) {
                $q->where('is_read', 0)
                  ->orWhereNull('is_read');
            })

            ->where('is_deleted', 0)

            ->groupBy('sender_id')

            ->get();

        $counts = [];

        foreach ($rows as $r) {

            $counts[(string) $r->sender_id] = (int) $r->cnt;
        }

        return response()->json([
            'counts' => $counts
        ], 200);
    }
}