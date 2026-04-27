<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Xendit\Invoice;

class PaymentController extends Controller
{
    public function qris(Request $request)
    {
        $order_id = $request->get('order_id');

        $order = Order::findOrFail($order_id);

        $transaksi = Transaksi::where('orders_id', $order->id)
            ->where('metode_pembayaran', 'qris')
            ->firstOrFail();

        if (!$transaksi->reference_payment || !$transaksi->qris_url) {

            $customer = $order->customer;

            $params = [
                'external_id'    => 'order-' . $order->id . '-' . time(),
                'payer_email'    => $customer->email ?? '',
                'description'    => "Pembayaran pesanan #{$order->id}",
                'amount'         => intval($order->total_harga),
                'payment_methods'=> ['QRIS'],
            ];

            $invoice = Invoice::create($params);

            // ✅ INI PENTING
            $transaksi->reference_payment = $invoice['id'] ?? null;
            $transaksi->qris_url = $invoice['invoice_url'] ?? null;
            $transaksi->save();
        }

        return view('customer.payment.qris', [
            'order_id'    => $order->id,
            'qris_url'    => $transaksi->qris_url,
            'total_harga' => $order->total_harga,
        ]);
    }

    public function checkQrisStatus(Request $request)
    {
        $order_id = $request->get('order_id');

        $order = Order::findOrFail($order_id);

        $transaksi = Transaksi::where('orders_id', $order->id)
            ->where('metode_pembayaran', 'qris')
            ->first();

        return response()->json([
            'status' => $transaksi->status_pembayaran ?? 'pending'
        ]);
    }
}