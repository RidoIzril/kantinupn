<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customers;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenjualController extends Controller
{

 public function __construct()
    {
        $this->middleware('auth:penjual');
    }

    public function index()
    {
        $penjualId = Auth::guard('penjual')->id();

        // Produk milik penjual yang login
        $jumlahProduk = Product::where('penjual_id', $penjualId)->count();

        // Transaksi pending milik penjual
        $transaksiPending = Transaction::where('penjual_id', $penjualId)
            ->where('status', 'pending')
            ->count();

        // Transaksi dibatalkan milik penjual
        $transaksiDibatalkan = Transaction::where('penjual_id', $penjualId)
            ->where(function ($query) {
                $query->where('status', 'failed')
                      ->orWhere('delivery_status', 'failed');
            })->count();

        // Transaksi selesai milik penjual
        $transaksiSelesai = Transaction::where('penjual_id', $penjualId)
            ->where('delivery_status', 'done')
            ->count();

        // Produk dalam pengiriman milik penjual
        $produkDalamPengiriman = Transaction::where('penjual_id', $penjualId)
            ->where('delivery_status', 'delivered')
            ->count();

        // Total pendapatan milik penjual
        $totalPendapatan = Transaction::where('penjual_id', $penjualId)
            ->where('delivery_status', 'done')
            ->with('order')
            ->get()
            ->sum(function ($transaction) {
                return $transaction->order->total_price ?? 0;
            });

        $customers = Customers::count(); // ini global boleh

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