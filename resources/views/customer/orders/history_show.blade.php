@extends('layouts.app')

@section('content')
<div class="py-8 pt-16 md:pt-8 px-6">
    <div class="max-w-6xl">
        <div class="bg-white rounded shadow px-6 py-6 border">
            <h2 class="text-xl font-bold mb-4 text-green-700">Detail Pesanan #{{ $order->id }}</h2>
            <dl>
                <dt class="font-semibold text-slate-700">Tanggal Pesanan</dt>
                <dd class="mb-2">{{ $order->order_tanggal ?? $order->created_at }}</dd>

                <dt class="font-semibold text-slate-700">Status</dt>
                <dd class="mb-2">
                    <span class="inline-block px-3 py-1 text-xs rounded-full 
                        @if($order->order_status=='pending') bg-yellow-100 text-yellow-800
                        @elseif($order->order_status=='diproses') bg-blue-100 text-blue-800
                        @elseif($order->order_status=='selesai') bg-green-100 text-green-800
                        @elseif($order->order_status=='batal') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($order->order_status) }}
                    </span>
                </dd>

                <dt class="font-semibold text-slate-700">Status Pembayaran</dt>
                <dd class="mb-4">
                    <span class="inline-block px-3 py-1 text-xs rounded-full 
                        @if(($order->transaksi->status_pembayaran ?? '')=='pending') bg-yellow-100 text-yellow-800
                        @elseif(($order->transaksi->status_pembayaran ?? '')=='paid') bg-green-100 text-green-800
                        @elseif(($order->transaksi->status_pembayaran ?? '')=='expired') bg-red-100 text-red-800
                        @elseif(($order->transaksi->status_pembayaran ?? '')=='failed') bg-red-100 text-red-800
                        @elseif(($order->transaksi->status_pembayaran ?? '')=='canceled') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($order->transaksi->status_pembayaran ?? '-') }}
                    </span>
                </dd>

                @if(
                    strtolower($order->order_status) === 'pending' &&
                    strtolower($order->transaksi->metode_pembayaran ?? '') === 'cash'
                )
                    <div id="countdown-box" class="mb-3 bg-yellow-50 border border-yellow-300 text-yellow-900 rounded px-4 py-3 flex items-center gap-2">
                        <span class="font-medium">Selesaikan pembayaran dalam:</span>
                        <span id="countdown" class="font-bold"></span>
                    </div>
                @endif

                <dt class="font-semibold text-slate-700">Metode Pembayaran</dt>
                <dd class="mb-4">{{ $order->transaksi->metode_pembayaran ?? '-' }}</dd>
            </dl>

            <div class="mb-4 bg-slate-50 rounded p-3">
                <span class="font-semibold text-slate-700 block mb-2">Rincian Menu</span>
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="py-2 text-left">Menu</th>
                            <th class="py-2 text-center">Qty</th>
                            <th class="py-2 text-left">Varian</th>
                            <th class="py-2 text-left">Catatan</th>
                            <th class="py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($order->details as $item)
                        <tr>
                            <td class="py-2">{{ $item->produk->nama ?? '-' }}</td>
                            <td class="py-2 text-center">{{ $item->jumlah }}</td>
                            <td class="py-2">{{ $item->variant->nama_variant ?? '-' }}</td>
                            <td class="py-2">{{ $item->catatan_menu ?? '-' }}</td>
                            <td class="py-2 text-right">Rp {{ number_format($item->total_harga,0,',','.') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-end gap-3">
                <span class="font-semibold">Total :</span>
                <span class="font-bold text-green-700 text-lg">
                    Rp {{ number_format($order->total_harga,0,',','.') }}
                </span>
            </div>

            <div class="mt-6">
                <a href="{{ route('orders.history', ['token' => request('token')]) }}"
                   class="inline-block bg-green-700 hover:bg-green-800 text-white font-semibold px-4 py-2 rounded">
                    &larr; Kembali ke Riwayat
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
<script>
// Misal: batas waktu pembayaran cash adalah 15 menit setelah order dibuat
const orderTime = "{{ \Carbon\Carbon::parse($order->order_tanggal ?? $order->created_at)->format('Y-m-d H:i:s') }}";
const deadlineMinutes = 15;
const deadline = new Date(new Date(orderTime).getTime() + deadlineMinutes*60000);

function pad(n) { return n < 10 ? '0' + n : n; }

function countdownTick() {
    const now = new Date();
    let diff = Math.floor((deadline - now) / 1000);
    if (diff < 0) diff = 0;

    const mm = pad(Math.floor(diff / 60));
    const ss = pad(diff % 60);
    document.getElementById('countdown').textContent = mm + ':' + ss;

    // Optional: Jika waktu habis, tampilkan pesan/Otomatis disable sesuatu
    if (diff <= 0) {
        document.getElementById('countdown-box').innerHTML = 
          '<span class="text-red-700 font-bold">Waktu pembayaran telah habis. Silakan hubungi kasir!</span>';
        clearInterval(window._countdownInterval);
    }
}
window._countdownInterval = setInterval(countdownTick, 1000);
countdownTick();
</script>