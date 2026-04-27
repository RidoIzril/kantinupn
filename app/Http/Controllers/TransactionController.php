<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Tampilkan halaman invoice cash dengan countdown
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showInvoice($id)
    {
        $order = Order::with([
            'details.produk',
            'details.variant',
            'customer',
            'transaksi'
        ])->findOrFail($id);

        // Validasi, transaksi harus ada & metode pembayaran cash
        if (
            !$order->transaksi ||
            $order->transaksi->metode_pembayaran !== 'cash'
        ) {
            return redirect()->route('orders.history') // atau halaman lain yang cocok
                ->with('error', 'Bukan pembayaran tunai.');
        }

        $created = $order->order_tanggal ?? $order->created_at;
        $waktu_order = Carbon::parse($created);
        $deadline = $waktu_order->copy()->addMinutes(10);

        return view('customer.invoice.cash', [
            'order' => $order,
            'deadline' => $deadline,
            'waktu_order' => $waktu_order,
        ]);
    }

    /**
     * AJAX: Cek status pesanan (pending/dibayar/dibatalkan)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus($id)
    {
        $order = Order::with('transaksi')->findOrFail($id);

        // Standarisasi status
        if ($order->order_status === 'batal') {
            $status = 'canceled';
        } elseif ($order->transaksi && $order->transaksi->status_pembayaran === 'completed') {
            $status = 'paid';
        } else {
            $status = 'pending';
        }

        return response()->json(['status' => $status]);
    }
}