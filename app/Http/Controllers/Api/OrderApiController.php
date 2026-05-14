<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

use App\Models\Order;
use App\Models\Penjual;
use App\Models\Tenants;
use App\Models\Customers;
use App\Models\Produk;

class OrderApiController extends Controller
{
    /**
     * Resolve user from session or api token (bearer/query/input)
     */
    private function resolveUser(Request $request)
    {
        $user = $request->user() ?? auth()->user();
        if ($user) return $user;

        $plainTextToken = $request->bearerToken()
            ?? $request->input('token')
            ?? $request->query('token');

        if (!$plainTextToken) return null;

        $accessToken = PersonalAccessToken::findToken($plainTextToken);
        return $accessToken?->tokenable;
    }

    private function unauthorized()
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized'
        ], 401);
    }

    private function forbidden($message = 'Forbidden')
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], 403);
    }

    // =========================
    // PENJUAL API
    // =========================

    /**
     * GET /api/penjual/orders
     * List orders masuk untuk penjual berdasarkan tenant produk pada detail order.
     */
    public function penjualOrders(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) return $this->unauthorized();

        if (($user->role ?? null) !== 'penjual') {
            return $this->forbidden('Hanya penjual yang boleh mengakses endpoint ini.');
        }

        $penjual = Penjual::where('users_id', $user->id)->first();
        if (!$penjual) {
            return response()->json(['success' => true, 'data' => []], 200);
        }

        $tenant = Tenants::where('penjuals_id', $penjual->id)->first();
        $tenantId = $tenant?->id;
        if (!$tenantId) {
            return response()->json(['success' => true, 'data' => []], 200);
        }

        $orders = Order::whereHas('details.produk', function ($q) use ($tenantId) {
                $q->where('tenants_id', $tenantId);
            })
            ->with(['details.produk', 'details.variant', 'customer', 'transaksi', 'delivery'])
            ->orderByDesc('order_tanggal')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ], 200);
    }

    /**
     * GET /api/penjual/orders/{id}
     * Detail order untuk penjual.
     */
    public function penjualOrderShow($id, Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) return $this->unauthorized();

        if (($user->role ?? null) !== 'penjual') {
            return $this->forbidden('Hanya penjual yang boleh mengakses endpoint ini.');
        }

        $penjual = Penjual::where('users_id', $user->id)->first();
        $tenant  = $penjual ? Tenants::where('penjuals_id', $penjual->id)->first() : null;
        $tenantId = $tenant?->id;

        if (!$tenantId) {
            return $this->forbidden('Tenant penjual tidak ditemukan.');
        }

        $order = Order::with(['details.produk', 'details.variant', 'customer', 'transaksi', 'delivery'])
            ->findOrFail($id);

        // Pastikan order ini memang punya produk milik tenant penjual
        $allowed = $order->details()
            ->whereHas('produk', fn ($q) => $q->where('tenants_id', $tenantId))
            ->exists();

        if (!$allowed) {
            return $this->forbidden('Order ini bukan milik tenant Anda.');
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ], 200);
    }

    /**
     * POST /api/penjual/orders/{id}/process
     */
    public function penjualOrderProcess($id, Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) return $this->unauthorized();

        if (($user->role ?? null) !== 'penjual') {
            return $this->forbidden('Hanya penjual yang boleh mengakses endpoint ini.');
        }

        $order = Order::with('transaksi')->findOrFail($id);

        $order->order_status = 'diproses';
        $order->save();

        if ($order->transaksi && strtolower($order->transaksi->metode_pembayaran) === 'cash') {
            $order->transaksi->status_pembayaran = 'paid';
            $order->transaksi->waktu_bayar = now();
            $order->transaksi->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesanan telah diproses.',
            'data' => $order->fresh(['transaksi'])
        ], 200);
    }

    /**
     * POST /api/penjual/orders/{id}/ready
     */
    public function penjualOrderReady($id, Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) return $this->unauthorized();

        if (($user->role ?? null) !== 'penjual') {
            return $this->forbidden('Hanya penjual yang boleh mengakses endpoint ini.');
        }

        $order = Order::findOrFail($id);
        $order->order_status = 'siap';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Pesanan siap diambil.',
            'data' => $order
        ], 200);
    }

    /**
     * POST /api/penjual/orders/{id}/complete
     */
    public function penjualOrderComplete($id, Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) return $this->unauthorized();

        if (($user->role ?? null) !== 'penjual') {
            return $this->forbidden('Hanya penjual yang boleh mengakses endpoint ini.');
        }

        $order = Order::with('transaksi')->findOrFail($id);
        $order->order_status = 'selesai';
        $order->save();

        if ($order->transaksi) {
            $order->transaksi->status_pembayaran = 'paid';
            $order->transaksi->waktu_bayar = now();
            $order->transaksi->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesanan ditandai selesai.',
            'data' => $order->fresh(['transaksi'])
        ], 200);
    }

    /**
     * POST /api/penjual/orders/{id}/cancel
     */
    public function penjualOrderCancel($id, Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) return $this->unauthorized();

        if (($user->role ?? null) !== 'penjual') {
            return $this->forbidden('Hanya penjual yang boleh mengakses endpoint ini.');
        }

        $order = Order::with(['details'])->findOrFail($id);
        $order->order_status = 'batal';
        $order->save();

        // Kembalikan stok produk
        foreach ($order->details as $item) {
            $produk = Produk::find($item->produks_id);
            if ($produk) {
                $produk->stok += $item->jumlah;
                $produk->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan.',
            'data' => $order
        ], 200);
    }

    // =========================
    // CUSTOMER API
    // =========================

    /**
     * GET /api/customer/orders
     */
    public function customerOrders(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) return $this->unauthorized();

        if (($user->role ?? null) !== 'customer') {
            return $this->forbidden('Hanya customer yang boleh mengakses endpoint ini.');
        }

        $customer = Customers::where('users_id', $user->id)->first();
        if (!$customer) {
            return $this->unauthorized();
        }

        $orders = Order::where('customers_id', $customer->id)
            ->with(['details.produk', 'details.variant', 'transaksi', 'delivery'])
            ->orderByDesc('order_tanggal')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ], 200);
    }

    /**
     * GET /api/customer/orders/{id}
     * Tidak redirect QRIS; API akan mengembalikan info pembayaran.
     */
    public function customerOrderShow($orderId, Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) return $this->unauthorized();

        if (($user->role ?? null) !== 'customer') {
            return $this->forbidden('Hanya customer yang boleh mengakses endpoint ini.');
        }

        $customer = Customers::where('users_id', $user->id)->first();
        if (!$customer) return $this->unauthorized();

        $order = Order::where('id', $orderId)
            ->where('customers_id', $customer->id)
            ->with(['details.produk', 'details.variant', 'transaksi', 'delivery'])
            ->firstOrFail();

        $transaksi = $order->transaksi;

        // Jika QRIS dan belum paid, informasikan endpoint untuk generate QR
        $needsQris = false;
        $qrisEndpoint = null;

        if (
            strtolower($transaksi->metode_pembayaran ?? '') === 'qris'
            && strtolower($transaksi->status_pembayaran ?? '') !== 'paid'
        ) {
            $needsQris = true;
            $qrisEndpoint = url('/api/payment/qris') . '?order_id=' . $order->id;
        }

        // Logic cash: jika status diproses => paid (sama dengan web controller)
        if (
            strtolower($transaksi->metode_pembayaran ?? '') === 'cash'
            && strtolower($order->order_status ?? '') === 'diproses'
            && strtolower($transaksi->status_pembayaran ?? '') !== 'paid'
        ) {
            $transaksi->status_pembayaran = 'paid';
            $transaksi->waktu_bayar = now();
            $transaksi->save();
            $order->load('transaksi');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $order,
                'payment' => [
                    'metode_pembayaran' => $order->transaksi->metode_pembayaran ?? null,
                    'status_pembayaran' => $order->transaksi->status_pembayaran ?? null,
                    'waktu_bayar'       => $order->transaksi->waktu_bayar ?? null,
                    'needs_qris'        => $needsQris,
                    'qris_endpoint'     => $qrisEndpoint,
                ]
            ]
        ], 200);
    }
}