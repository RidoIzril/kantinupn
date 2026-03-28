<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Customers;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenjualController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            abort(403);
        }

        $penjualId = $user->penjual_id;

        $jumlahProduk = Produk::where('penjual_id', $penjualId)->count();

        $transaksiPending = Transaksi::where('penjual_id', $penjualId)
            ->where('status', 'pending')
            ->count();

        $transaksiDibatalkan = Transaksi::where('penjual_id', $penjualId)
            ->where(function ($q) {
                $q->where('status', 'failed')
                  ->orWhere('delivery_status', 'failed');
            })->count();

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
            ->sum(fn($t) => $t->order->total_price ?? 0);

        $customers = Customers::count();

        return view('penjual.homepenjual', compact(
            'customers',
            'jumlahProduk',
            'transaksiPending',
            'transaksiDibatalkan',
            'transaksiSelesai',
            'totalPendapatan',
            'produkDalamPengiriman'
        ));
    }
}