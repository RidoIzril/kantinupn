<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Xendit\Xendit;

class PaymentController extends Controller
{
    public function createQr(Request $req)
    {
        $amount = $req->input('amount', 10000); // ganti sesuai input dari form
        $orderId = 'ORDER-' . uniqid();

        // Set API Key Xendit
        Xendit::setApiKey(config('services.xendit.secret_key'));

        // QRIS QR Code charge
        $params = [
            'external_id' => $orderId,
            'amount'      => (int)$amount,
            'type'        => 'DYNAMIC'
        ];

        try {
            $resp = \Xendit\QRCode::create($params);
            // $resp->qr_string -> data QR
            // $resp->qr_url (image url)
            // $resp->id (harus disimpan untuk cek status)
            return view('payment.qris', [
                'qr_url'   => $resp['qr_url'],
                'qr_id'    => $resp['id'],
                'amount'   => $amount,
                'order_id' => $orderId
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'QRIS Error: ' . $e->getMessage());
        }
    }

    // Untuk cek status payment QRIS
    public function statusQr(Request $req)
    {
        $qr_id = $req->input('qr_id');
        Xendit::setApiKey(config('services.xendit.secret_key'));
        try {
            $resp = \Xendit\QRCode::retrieve($qr_id);
            // $resp['status'] == 'ACTIVE' / 'INACTIVE' / 'COMPLETED'
            return response()->json($resp);
        } catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage()], 500);
        }
    }
}