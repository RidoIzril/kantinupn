<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Customers;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class PenjualController extends Controller
{
    public function index(Request $request)
    {
        dd([
    'auth_check' => auth()->check(),
    'auth_user_id' => auth()->id(),
    'auth_role' => auth()->user()->role ?? null,
    'request_user' => optional(request()->user())->id,
]);
        // Ambil user dari guard aktif (web atau sanctum)
        $user = auth()->user() ?? $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }

        $penjual = $user->penjual;

        if (!$penjual) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Penjual tidak ditemukan'], 404);
            }
            return redirect()->route('login')->withErrors('Data penjual tidak ditemukan.');
        }

        $penjualId = $penjual->id; // penting: pakai PK custom

        $jumlahProduk = Produk::where('penjual_id', $penjualId)->count();

        $transaksiPending = Transaksi::where('penjual_id', $penjualId)
            ->where('status', 'pending')
            ->count();

        $transaksiDibatalkan = Transaksi::where('penjual_id', $penjualId)
            ->where(function ($q) {
                $q->where('status', 'failed')
                  ->orWhere('delivery_status', 'failed');
            })
            ->count();

        $transaksiSelesai = Transaksi::where('penjual_id', $penjualId)
            ->where('delivery_status', 'done')
            ->count();

        $produkDalamPengiriman = Transaksi::where('penjual_id', $penjualId)
            ->where('delivery_status', 'delivered')
            ->count();

        $totalPendapatan = Transaksi::where('penjual_id', $penjualId)
            ->where('delivery_status', 'done')
            ->with('order')
            ->get()
            ->sum(fn ($t) => $t->order->total_price ?? 0);

        $jumlahCustomer = Customers::count();

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Dashboard penjual berhasil diambil',
                'data' => [
                    'penjual' => [
                        'id' => $penjual->penjuals_id,
                        'username' => $user->username ?? null,
                    ],
                    'statistik' => [
                        'jumlah_produk' => $jumlahProduk,
                        'transaksi_pending' => $transaksiPending,
                        'transaksi_dibatalkan' => $transaksiDibatalkan,
                        'transaksi_selesai' => $transaksiSelesai,
                        'produk_dikirim' => $produkDalamPengiriman,
                        'total_pendapatan' => $totalPendapatan,
                        'jumlah_customer' => $jumlahCustomer,
                    ]
                ]
            ]);
        }

        // untuk route web /penjual/home
        return view('penjual.home', compact(
            'jumlahProduk',
            'transaksiPending',
            'transaksiDibatalkan',
            'transaksiSelesai',
            'produkDalamPengiriman',
            'totalPendapatan',
            'jumlahCustomer',
            'penjual'
        ));
    }
}