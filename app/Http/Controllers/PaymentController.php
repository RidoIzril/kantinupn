<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Xendit\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function qris(Request $request)
    {
        \Xendit\Xendit::setApiKey(config('services.xendit.secret'));
        $order_id = $request->get('order_id');
        $order = Order::findOrFail($order_id);

        $transaksi = Transaksi::where('orders_id', $order->id)
            ->where('metode_pembayaran', 'qris')
            ->firstOrFail();

        $qr_string = $transaksi->qr_string ?? null;
        $token = $request->input('token') ?? $request->query('token');

        if (
            !$transaksi->reference_payment ||
            !$transaksi->qris_url ||
            !$transaksi->qr_string
        ) {
            $customer = $order->customer;

            $params = [
                'external_id'    => 'order-' . $order->id . '-' . time(),
                'payer_email'    => $customer->email ?? '',
                'description'    => "Pembayaran pesanan #{$order->id}",
                'amount'         => intval($order->total_harga),
                'payment_methods'=> ['QRIS'],
            ];

            Log::info('👀 Mengirim request invoice ke Xendit', $params);

            // Create Invoice
            $invoice = Invoice::create($params);
            Log::info('📡 Xendit invoice response', $invoice);

            $transaksi->reference_payment = $invoice['id'] ?? null;
            $transaksi->qris_url = $invoice['invoice_url'] ?? null;

            // GET detail invoice KE XENDIT untuk cari qr_string  
            $qr_string = null;
            try {
                $apiKey = config('services.xendit.secret');
                Log::info('🔑 Xendit API Key (should NOT be null)', ['apiKey' => $apiKey]);

                if (!$apiKey) {
                    // Stop eksekusi, tampilkan error
                    return response()->json(['error' => 'Xendit API KEY tidak terbaca.'], 500);
                }

                $invoice_id = $invoice['id'] ?? null;
                Log::info('Invoice id yang didapat', ['invoice_id' => $invoice_id]);
                if ($invoice_id) {
                    $response = Http::withBasicAuth($apiKey, '')
                        ->get("https://api.xendit.co/v2/invoices/{$invoice_id}");
                    Log::info('Response GET detail invoice Xendit:', [
                        'status' => $response->status(),
                        'body'   => $response->json(),
                    ]);
                    if ($response->successful()) {
                        $qr_string = $response->json('qr_string');
                        Log::info('Berhasil dapat qr_string', ['qr_string' => $qr_string]);
                    } else {
                        Log::warning('Gagal ambil detail/qr_string dari Xendit', [
                            'status' => $response->status(),
                            'body'   => $response->json(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('ERROR saat ambil qr_string Xendit', [
                    'err' => $e->getMessage(),
                ]);
                $qr_string = null;
            }

            $transaksi->qr_string = $qr_string;
            $transaksi->save();
        }

        return view('customer.payment.qris', [
            'order_id'    => $order->id,
            'qris_url'    => $transaksi->qris_url,
            'total_harga' => $order->total_harga,
            'qr_string'   => $qr_string ?? $transaksi->qr_string,
            'token'       => $token
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