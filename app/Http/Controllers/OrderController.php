<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Penjual;
use App\Models\Tenants;
use Laravel\Sanctum\PersonalAccessToken;

class OrderController extends Controller
{
    /**
     * Resolve user from session or api token (bearer/query/input)
     */
    private function resolveUser(Request $request)
    {
        $user = $request->user() ?? auth()->user();
        if ($user) return $user;

        // Cek token dari Authorization: Bearer atau query string atau input
        $plainTextToken = 
            $request->bearerToken() ? $request->bearerToken() :
            ($request->input('token') ?? $request->query('token'));

        if (!$plainTextToken) return null;

        $accessToken = PersonalAccessToken::findToken($plainTextToken);
        return $accessToken?->tokenable;
    }

    // HALAMAN LIST PESANAN MASUK PENJUAL
    public function pesanan(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            // Redirect or response API error
            if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $penjual = Penjual::where('users_id', $user->id)->first();
        if (!$penjual) {
            return $this->handleApiOrWeb($request, []);
        }

        $tenant = Tenants::where('penjuals_id', $penjual->id)->first();
        $tenantId = $tenant?->id ?? null;
        if (!$tenantId) {
            return $this->handleApiOrWeb($request, []);
        }

        $orders = Order::whereHas('details.produk', function($q) use ($tenantId) {
                $q->where('tenants_id', $tenantId);
            })
            ->with(['details.produk', 'customer', 'transaksi'])
            ->latest()
            ->get();

        return $this->handleApiOrWeb($request, $orders);
    }

    // Helper: return API JSON or web view
    private function handleApiOrWeb(Request $request, $orders)
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
            return response()->json(['orders' => $orders], 200);
        }
        return view('penjual.order.index', ['orders' => $orders]);
    }

    // HALAMAN DETAIL PESANAN PENJUAL
    public function pesananShow($id, Request $request)
    {
        $order = Order::with(['details.produk', 'customer', 'transaksi'])->findOrFail($id);
        if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
            return response()->json(['order' => $order], 200);
        }
        return view('penjual.order.show', compact('order'));
    }

    public function pesananProcess($id, Request $request)
    {
        $order = Order::findOrFail($id);
        $order->order_status = 'diproses';
        $order->save();
        return $this->afterUpdate($request, 'Pesanan telah diproses.');
    }

    public function pesananComplete($id, Request $request)
{
    $order = Order::findOrFail($id);
    $order->order_status = 'selesai';
    $order->save();
    if ($order->transaksi) {
        $order->transaksi->status_pembayaran = 'paid'; // GANTI DARI 'completed' KE 'paid'!
        $order->transaksi->waktu_bayar = now();
        $order->transaksi->save();
    }
    return $this->afterUpdate($request, 'Pesanan ditandai selesai.');
}

    public function pesananCancel($id, Request $request)
    {
       $order = Order::with('details')->findOrFail($id);
        $order->order_status = 'batal';
        $order->save();

        // Kembalikan stok produk
        foreach ($order->details as $item) {
            $produk = \App\Models\Produk::find($item->produks_id);
            if ($produk) {
                $produk->stok += $item->jumlah;
                $produk->save();
            }
            // Jika ingin juga balikan stok varian
            // if ($item->variants_id) {
            //     $variant = \App\Models\Variant::find($item->variants_id);
            //     if ($variant) {
            //         $variant->stok += $item->jumlah;
            //         $variant->save();
            //     }
            // }
        }
        return $this->afterUpdate($request, 'Pesanan berhasil dibatalkan.');
    }

    private function afterUpdate(Request $request, $msg)
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
            return response()->json(['success' => true, 'message' => $msg], 200);
        }
        return redirect()->back()->with('success', $msg);
    }
    public function history(Request $request)
{
    $user = $this->resolveUser($request);
    if (!$user) {
        // API? balas JSON error | web? redirect login
        if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return redirect()->route('login');
    }

    $customer = \App\Models\Customers::where('users_id', $user->id)->first();
    if (!$customer) {
        return redirect()->route('login');
        // atau balikan json unauthorized/error
    }

    $orders = \App\Models\Order::where('customers_id', $customer->id)
        ->with(['details.produk', 'transaksi'])
        ->orderByDesc('order_tanggal')
        ->get();

    // API support
    if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
        return response()->json(['orders' => $orders], 200);
    } else {
        return view('customer.orders.history', compact('orders'));
    }
}

public function historyShow($orderId, Request $request)
{
    $user = $this->resolveUser($request);
    if (!$user) {
        if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return redirect()->route('login');
    }

    $customer = \App\Models\Customers::where('users_id', $user->id)->first();
    if (!$customer) {
        return redirect()->route('login');
    }

    $order = \App\Models\Order::where('id', $orderId)
                ->where('customers_id', $customer->id)
                ->with(['details.produk', 'details.variant', 'transaksi'])
                ->firstOrFail();

    if ($request->expectsJson() || $request->wantsJson() || $request->is('api/*')) {
        return response()->json(['order' => $order], 200);
    } else {
        return view('customer.orders.history_show', compact('order'));
    }
}
}