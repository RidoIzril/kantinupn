<?php

namespace App\Http\Controllers;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class PaymentController extends Controller
{
    
    public function index()
    {
        $payments = Payment::all();
        return view('penjual.payment.list_payment', compact('payments'));
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'payment_name' => 'required|string|max:255',
            'payment_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Simpan gambar ke folder storage/public/payments
        $path = $request->file('payment_image')->store('payments', 'public');

        // Simpan data ke database
        $payment = new Payment();
        $payment->payment_name = $request->payment_name;
        $payment->payment_image = $path;
        $payment->save();

        return redirect()->back()->with('success', 'Metode pembayaran berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'payment_name' => 'required|string|max:255',
            'payment_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $payment = Payment::findOrFail($id);
        $payment->payment_name = $request->payment_name;

        if ($request->hasFile('payment_image')) {
            // Hapus gambar lama kalau ada
            if ($payment->payment_image && Storage::exists('public/' . $payment->payment_image)) {
                Storage::delete('public/' . $payment->payment_image);
            }

            $path = $request->file('payment_image')->store('payments', 'public');
            $payment->payment_image = $path;
        }

        $payment->save();

        return redirect()->back()->with('success', 'Data pembayaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);

        // Hapus gambar dari storage
        if ($payment->payment_image && Storage::exists('public/' . $payment->payment_image)) {
            Storage::delete('public/' . $payment->payment_image);
        }

        // Hapus data dari database
        $payment->delete();

        return redirect()->back()->with('success', 'Data pembayaran berhasil dihapus.');
    }

}
