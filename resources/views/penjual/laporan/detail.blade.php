@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-10">

    <h2 class="text-2xl font-bold mb-6 text-green-800">Detail Transaksi</h2>

    <!-- INFO TRANSAKSI -->
    <div class="bg-white p-5 rounded shadow mb-6">
        <p><b>Customer:</b> {{ $transaksi->order->customer->nama_lengkap ?? '-' }}</p>
        <p><b>Tipe Pesanan:</b> {{ ucfirst($transaksi->order->order_type ?? '-') }}</p>
        <p><b>Metode:</b> {{ strtoupper($transaksi->metode_pembayaran) }}</p>
        <p><b>Status:</b> {{ $transaksi->status_pembayaran }}</p>
        <p><b>Waktu Bayar:</b> {{ $transaksi->waktu_bayar }}</p>
        <p><b>Total:</b> Rp {{ number_format($transaksi->jumlah_bayar,0,',','.') }}</p>
    </div>

    <!-- DETAIL PRODUK -->
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="p-3">Produk</th>
                    <th>Variant</th>
                    <th class="p-3">Qty</th>
                    <th class="p-3">Harga</th>
                    <th class="p-3">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaksi->order->details as $item)
                <tr class="border-b">
                    <td class="p-3 text-center">{{ $item->produk->nama ?? '-' }}</td>
                    <td class="p-3 text-center">
                        {{ $item->variant->nama ?? '-' }}
                    </td>
                    <td class="p-3 text-center">{{ $item->jumlah }}</td>
                    <td class="p-3 text-center">
                        Rp {{ number_format($item->produk->harga,0,',','.') }}
                    </td>
                    <td class="p-3 text-center">
                        Rp {{ number_format($item->total_harga,0,',','.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection