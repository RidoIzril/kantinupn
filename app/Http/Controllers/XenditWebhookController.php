<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();
        Log::info('WEBHOOK MASUK', $data);

        // Xendit mengirim external_id, contoh: order-20-1778075000
        $externalId = $data['external_id'] ?? null;
        if (!$externalId) {
            Log::warning('Webhook diabaikan: external_id tidak ada');
            return response()->json(['status' => 'ignored'], 200);
        }

        $status = strtolower($data['status'] ?? '');

        // Ambil orderId dari external_id: order-(\d+)-
        preg_match('/order-(\d+)-?/', $externalId, $match);
        $orderId = $match[1] ?? null;

        if (!$orderId) {
            Log::warning('Webhook invalid: gagal parse orderId dari external_id', ['external_id' => $externalId]);
            return response()->json(['status' => 'invalid'], 200);
        }

        // Cari transaksi berdasarkan orders_id
        $transaksi = Transaksi::where('orders_id', $orderId)
            ->where('metode_pembayaran', 'qris')
            ->first();

        if (!$transaksi) {
            Log::error('TRANSAKSI GA KETEMU', ['order_id' => $orderId]);
            return response()->json(['status' => 'not found'], 200);
        }

        // Update transaksi sesuai status dari Xendit
        if ($status === 'paid') {
            $transaksi->status_pembayaran = 'paid';
            $transaksi->waktu_bayar = now();
            $transaksi->save();

            // Update order status jadi proses
            $order = Order::find($orderId);
            if ($order) {
                $order->order_status = 'diproses';
                $order->save();
            }

            Log::info('Webhook PAID: transaksi & order updated', ['order_id' => $orderId]);
        } elseif ($status === 'expired') {
            $transaksi->status_pembayaran = 'expired';
            $transaksi->save();
            Log::info('Webhook EXPIRED: transaksi updated', ['order_id' => $orderId]);
        } else {
            // biarkan pending, jangan overwrite kalau sudah paid
            if (strtolower($transaksi->status_pembayaran ?? '') !== 'paid') {
                $transaksi->status_pembayaran = 'pending';
                $transaksi->save();
            }
            Log::info('Webhook status lain: transaksi dibiarkan/pending', ['order_id' => $orderId, 'status' => $status]);
        }

        return response()->json(['status' => 'success'], 200);
    }
}