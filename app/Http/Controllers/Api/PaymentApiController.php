<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

use App\Models\Order;
use App\Models\Transaksi;

use Xendit\Invoice;

class PaymentApiController extends Controller
{
    /**
     * Resolve user from session or token:
     * - Authorization: Bearer <token>
     * - token in body
     * - token in query string
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

    /**
     * GET /api/payment/qris?order_id=xx
     * atau POST /api/payment/qris { order_id: xx }
     *
     * Response JSON:
     * - order_id
     * - total_harga
     * - reference_payment
     * - qris_url
     * - qr_string
     * - status_pembayaran
     */
    public function qris(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $order = Order::with('customer')->findOrFail($validated['order_id']);

        // Pastikan order ini milik customer yang sedang login (opsional tapi disarankan)
        // Kalau user penjual/superadmin juga boleh akses, kamu bisa longgarkan.
        // Di sini kita enforce customer hanya boleh akses order miliknya.
        if (($user->role ?? null) === 'customer') {
            if ((int) $order->customers_id !== (int) ($user->customer?->id ?? 0)) {
                // Kalau model user kamu tidak punya relasi customer, gunakan query:
                // $customer = \App\Models\Customers::where('users_id', $user->id)->first();
                // lalu bandingkan dengan $order->customers_id
                $customer = \App\Models\Customers::where('users_id', $user->id)->first();
                if (!$customer || (int) $order->customers_id !== (int) $customer->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Forbidden'
                    ], 403);
                }
            }
        }

        $transaksi = Transaksi::where('orders_id', $order->id)
            ->where('metode_pembayaran', 'qris')
            ->first();

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi QRIS untuk order ini tidak ditemukan.'
            ], 404);
        }

        $apiKey = config('services.xendit.secret');
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Xendit API KEY tidak terbaca (services.xendit.secret).'
            ], 500);
        }

        \Xendit\Xendit::setApiKey($apiKey);

        // Kalau belum ada invoice / url / qr_string, buat & simpan
        if (
            !$transaksi->reference_payment ||
            !$transaksi->qris_url ||
            !$transaksi->qr_string
        ) {
            $customer = $order->customer;

            $params = [
                'external_id'     => 'order-' . $order->id . '-' . time(),
                'payer_email'     => $customer->email ?? '',
                'description'     => "Pembayaran pesanan #{$order->id}",
                'amount'          => (int) $order->total_harga,
                'payment_methods' => ['QRIS'],
            ];

            Log::info('[PaymentApi] Create Xendit invoice', $params);

            try {
                $invoice = Invoice::create($params);
                Log::info('[PaymentApi] Xendit invoice response', (array) $invoice);

                $invoiceId = $invoice['id'] ?? null;
                $invoiceUrl = $invoice['invoice_url'] ?? null;

                $transaksi->reference_payment = $invoiceId;
                $transaksi->qris_url = $invoiceUrl;

                // Ambil detail invoice untuk dapatkan qr_string
                $qrString = null;

                if ($invoiceId) {
                    $response = Http::withBasicAuth($apiKey, '')
                        ->get("https://api.xendit.co/v2/invoices/{$invoiceId}");

                    Log::info('[PaymentApi] GET invoice detail', [
                        'status' => $response->status(),
                        'body' => $response->json(),
                    ]);

                    if ($response->successful()) {
                        $qrString = $response->json('qr_string');
                    }
                }

                $transaksi->qr_string = $qrString;
                $transaksi->save();
            } catch (\Throwable $e) {
                Log::error('[PaymentApi] Error create/get invoice', [
                    'message' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat invoice QRIS: ' . $e->getMessage(),
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_id'          => $order->id,
                'total_harga'       => (int) $order->total_harga,
                'reference_payment' => $transaksi->reference_payment,
                'qris_url'          => $transaksi->qris_url,
                'qr_string'         => $transaksi->qr_string,
                'status_pembayaran' => $transaksi->status_pembayaran ?? 'pending',
            ]
        ], 200);
    }

    /**
     * GET /api/payment/qris/status?order_id=xx
     * Response: { success: true, data: { status: "pending|paid|..." } }
     */
    public function checkQrisStatus(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        $transaksi = Transaksi::where('orders_id', $order->id)
            ->where('metode_pembayaran', 'qris')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'status' => $transaksi->status_pembayaran ?? 'pending',
            ]
        ], 200);
    }
}