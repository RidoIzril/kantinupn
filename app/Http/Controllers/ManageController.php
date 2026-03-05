<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Order;
class ManageController extends Controller
{

    public function index()
    {
        $transactions = Transaction::with([
            'order.customers',
            'order.items.product',
            'payment'
            ])
            ->orderBy('transaction_id', 'desc')
            ->get();
        return view('penjual.transaction_manage.manage', compact('transactions'));
    }

    public function show($id)
    {
        $transaction = Transaction::with(['order.customers', 'order.items.product', 'payment'])
            ->where('transaction_id', $id)
            ->firstOrFail();

        return response()->json($transaction);
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        // Update transaction
        $transaction->status = 'success';
        $transaction->delivery_status = 'delivered';
        $transaction->save();

        // Update order terkait
        $order = Order::find($transaction->order_id);
        if ($order) {
            $order->status = 'success';
            $order->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diperbarui'
        ]);
    }

    public function cancel($id)
    {
        $transaction = Transaction::findOrFail($id);

        // Update status transaksi
        $transaction->status = 'failed';
        $transaction->delivery_status = 'failed';
        $transaction->save();

        // Update status order juga (kalau perlu)
        $order = Order::find($transaction->order_id);
        if ($order) {
            $order->status = 'failed';
            $order->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dibatalkan'
        ]);
    }

}
