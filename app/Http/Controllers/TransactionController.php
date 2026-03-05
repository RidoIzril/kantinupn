<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\InvoiceMail;
use App\Models\Order;
use Illuminate\Http\Request;
use Exception;

class TransactionController extends Controller
{
    public function index()
    {
        $customerId = auth()->guard('customer')->id();

        // Mengambil data transaksi dengan relasi oder dan payment
        $transactions = Transaction::with(['order', 'payment'])
            ->whereHas('order', function($query) use ($customerId) {
                $query->where('customer_id', $customerId);
            })
            ->orderBy('transaction_id', 'desc')
            ->get();
    
        return view('customer.transactions.list_transaction', compact('transactions'));
    }

    public function accept($id)
    {
        // Mengambil data transaksi berdasrkan ID dengan relasi oder
        $transaction = Transaction::with('order')->where('transaction_id', $id)->firstOrFail();

        // Pastikan hanya bisa terima jika delivery_status = delivered
        if ($transaction->delivery_status != 'delivered') {
            return redirect()->back()->with('error', 'Pesanan belum sampai.');
        }        

        // Update status
        $transaction->delivery_status = 'done';
        $transaction->save();

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil diterima.'
        ]);
    }

    public function cancel($id)
    {
        // Mencari transaksi berdasarkan ID dengan relasi order, items, dan produk
        try {
            $transaction = Transaction::with(['order.items.product'])->findOrFail($id);

            // Kembalikan stok produk
            foreach ($transaction->order->items as $item) {
                if ($item->product) {
                    $item->product->increment('product_stock', $item->quantity);
                }
            }

            // Update status
            $transaction->status = 'failed';
            $transaction->delivery_status = 'failed';
            $transaction->save();

            return response()->json(['success' => true, 'message' => 'Transaksi berhasil dibatalkan dan stok produk telah dikembalikan.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat membatalkan transaksi: ' . $e->getMessage()]);
        }
    }


    public function show($id)
    {
        // Mencari transaksi berdasarkan ID
        $transaction = Transaction::with(['order.customers','order.items.product', 'payment'])
            ->where('transaction_id', $id)
            ->firstOrFail();

        return response()->json($transaction);
    }

    public function downloadPdf($id)
    {
        // Mencari transaksi berdasarkan ID
        $transaction = Transaction::with(['order.customers','order.items.product', 'payment'])
            ->where('transaction_id', $id)
            ->firstOrFail();

        // Validadi hanya status success dan done
        if (!($transaction->status == 'success' && $transaction->delivery_status == 'done')) {
            abort(403, 'Transaksi belum selesai.');
        }
        // format tanggal
        $tanggal_transaksi = \Carbon\Carbon::parse($transaction->transaction_date)->format('d-m-Y');
        // generate pdf
        $pdf = Pdf::loadView('customer.transactions.invoice_pdf', compact('transaction'));

        return $pdf->download('Invoice_' . $transaction->custom_code_transaction . '_' . $tanggal_transaksi . '.pdf');
    }

    public function sendInvoice($id)
    {
        $transaction = Transaction::with(['order.customers','order.items.product', 'payment'])
            ->where('transaction_id', $id)
            ->firstOrFail();

        if (!($transaction->status == 'success' && $transaction->delivery_status == 'done')) {
            abort(403, 'Transaksi belum selesai.');
        }

        // Generate PDF
        $pdf = Pdf::loadView('customer.transactions.invoice_pdf', compact('transaction'));

        // Kirim email ke customer
        Mail::to($transaction->order->customers->customer_email)
            ->send(new InvoiceMail($transaction, $pdf->output()));

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil dikirim ke email.'
        ]);
    }
    
}
