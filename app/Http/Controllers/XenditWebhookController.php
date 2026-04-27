<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function handle(Request $request)
{
    $data = $request->all();

    \Log::info('WEBHOOK MASUK', $data);

    // ❗ WAJIB ADA external_id
    if (!isset($data['external_id'])) {
        return response()->json(['status' => 'ignored'], 200);
    }

    $externalId = $data['external_id']; // order-36-xxxx
    $status     = strtolower($data['status'] ?? '');

    // 🔥 AMBIL ORDER ID DARI external_id
    preg_match('/order-(\d+)/', $externalId, $match);
    $orderId = $match[1] ?? null;

    if (!$orderId) {
        return response()->json(['status' => 'invalid'], 200);
    }

    // 🔥 CARI TRANSAKSI BERDASARKAN orders_id
    $transaksi = \App\Models\Transaksi::where('orders_id', $orderId)->first();

    if (!$transaksi) {
        \Log::error('TRANSAKSI GA KETEMU', ['order_id' => $orderId]);
        return response()->json(['status' => 'not found'], 200);
    }

    // 🔥 UPDATE STATUS
    if ($status === 'paid') {

        $transaksi->status_pembayaran = 'paid';
        $transaksi->waktu_bayar = now();

        if ($transaksi->order) {
            $transaksi->order->order_status = 'selesai';
            $transaksi->order->save();
        }

    } elseif ($status === 'expired') {

        $transaksi->status_pembayaran = 'expired';

    } else {

        $transaksi->status_pembayaran = 'pending';
    }

    $transaksi->save();

    return response()->json(['status' => 'success']);
}
}