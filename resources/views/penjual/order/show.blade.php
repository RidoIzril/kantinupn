@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-10">
    <h2 class="text-2xl font-bold mb-5 text-green-900">Detail Pesanan #{{ $order->id }}</h2>
    <div class="bg-white rounded-lg shadow p-6 mb-6 border">
        <div class="mb-3">
            <b class="text-green-900 block mb-1">Customer:</b>
            @if ($order->customer && $order->customer->nama_lengkap)
                <span class="inline-flex items-center gap-2">
                    <span class="text-green-900 font-semibold">{{ $order->customer->nama_lengkap }}</span>
                </span>
                @if ($order->customer->kontak)
                <div class="text-slate-400 text-sm">Telp: {{ $order->customer->kontak }}</div>
                @endif
            @else
                <span class="text-gray-400">-</span>
            @endif
        </div>
        <div class="mb-3">
            <b class="text-green-900">Tanggal:</b>
            <span class="text-gray-700">{{ $order->order_tanggal ?? $order->created_at }}</span>
        </div>

        {{-- Rincian Ekstra Berdasar Order Type --}}
        @if(strtolower($order->order_type) === 'dine in' || strtolower($order->order_type) === 'dine_in')
        <div class="mb-3">
            <b class="text-green-900">Tipe Pesanan:</b>
            <span class="text-blue-900 font-semibold">Dine In</span>
        </div>
        <div class="mb-3">
            <b class="text-green-900">Nomor Meja:</b>
            <span class="text-slate-700">{{ $order->nomor_meja ?? '-' }}</span>
        </div>
        @elseif(strtolower($order->order_type) === 'delivery')
        <div class="mb-3">
            <b class="text-green-900">Tipe Pesanan:</b>
            <span class="text-blue-900 font-semibold">Delivery</span>
        </div>
        @if($order->delivery)
        <div class="mb-3">
            <b class="text-green-900">Alamat Pengiriman:</b>
            <span class="text-slate-700">{{ $order->delivery->alamat }}</span><br>
            <b class="text-green-900">Catatan:</b>
            <span class="text-slate-700">{{ $order->delivery->catatan }}</span><br>
            <b class="text-green-900">Status Pengiriman:</b>
            <span class="text-slate-700">{{ ucfirst($order->delivery->status_delivery) }}</span>
        </div>
        @else
        <div class="mb-3 text-yellow-700">Rincian delivery belum tersedia.</div>
        @endif
        @endif

        <div class="mb-3">
    <b class="text-green-900">Metode Pembayaran:</b>
    <span class="text-gray-700">
        {{ strtoupper($order->transaksi->metode_pembayaran ?? '-') }}
    </span>
</div>

<div class="mb-3">
    <b class="text-green-900">Status Pembayaran:</b>
    @php
        $payStatus = strtolower($order->transaksi->status_pembayaran ?? '');
        $payColor = match($payStatus) {
            'paid'    => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'failed'  => 'bg-red-100 text-red-800',
            default   => 'bg-gray-100 text-gray-800'
        };
    @endphp

    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium {{ $payColor }} capitalize">
        {{ $order->transaksi->status_pembayaran ?? '-' }}
    </span>
</div>
        <div class="mb-3">
            <b class="text-green-900">Status Pesanan:</b>
            @php
            $status = strtolower($order->order_status);
                $color = match($status) {
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'diproses' => 'bg-blue-100 text-blue-800',
                    'siap' => 'bg-cyan-100 text-cyan-800',
                    'selesai' => 'bg-green-100 text-green-800',
                    'batal' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800'
                };
            @endphp
            <span class="inline-block px-3 py-1 rounded-full text-xs font-medium {{$color}} capitalize">
                {{ $order->order_status }}
            </span>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6 mb-6 border">
        <h3 class="font-bold mb-3 text-green-800">Daftar Item</h3>
        <table class="w-full text-sm rounded border">
            <thead class="bg-green-600 text-white">
                <tr>
                    <th class="py-2 px-3">Menu</th>
                    <th class="py-2 px-3 text-center">Jumlah</th>
                    <th class="py-2 px-3 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
            @foreach($order->details as $detail)
                <tr class="border-b hover:bg-slate-50 transition">
                    <td class="py-2 px-3">{{ $detail->produk->nama ?? '-' }}</td>
                    <td class="py-2 px-3 text-center">{{ $detail->jumlah }}</td>
                    <td class="py-2 px-3 text-right">Rp {{ number_format($detail->total_harga, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="text-right text-lg mt-4">
            <b>Total: <span class="text-green-700">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span></b>
        </div>
    </div>
    <div class="flex gap-2 justify-end flex-wrap">

    {{-- STATUS SELESAI / BATAL --}}
    @if(
        strtolower($order->order_status) == 'selesai'
        || strtolower($order->order_status) == 'batal'
    )

        <a href="{{ route('penjual.order.index', ['token' => request('token')]) }}"
           class="inline-block bg-gray-500 hover:bg-gray-600 px-5 py-2 rounded text-white font-semibold shadow transition">
            Kembali
        </a>

    {{-- STATUS PENDING --}}
    @elseif(strtolower($order->order_status) == 'pending')

        {{-- BUTTON DIPROSES --}}
        <form method="POST"
              action="{{ route('penjual.order.process', $order->id) }}"
              class="inline">

            @csrf

            <button type="submit"
                    class="inline-block bg-yellow-500 hover:bg-yellow-600 px-5 py-2 rounded text-white font-semibold shadow transition">
                Diproses
            </button>

        </form>

        {{-- BUTTON BATAL --}}
        <form method="POST"
              action="{{ route('penjual.order.cancel', $order->id) }}"
              class="inline"
              onsubmit="return confirm('Batalkan pesanan ini?')">

            @csrf

            <button type="submit"
                    class="inline-block bg-red-600 hover:bg-red-700 px-5 py-2 rounded text-white font-semibold shadow transition">
                Batal
            </button>

        </form>

    {{-- STATUS DIPROSES --}}
    @elseif(strtolower($order->order_status) == 'diproses')

        {{-- BUTTON SIAP --}}
        <form method="POST"
              action="{{ route('penjual.order.ready', $order->id) }}"
              class="inline">

            @csrf

            <button type="submit"
                    class="inline-block bg-blue-600 hover:bg-blue-700 px-5 py-2 rounded text-white font-semibold shadow transition">
                Siap
            </button>

        </form>

    {{-- STATUS SIAP --}}
    @elseif(strtolower($order->order_status) == 'siap')

        {{-- BUTTON SELESAI --}}
        <form method="POST"
              action="{{ route('penjual.order.complete', $order->id) }}"
              class="inline">

            @csrf

            <button type="submit"
                    class="inline-block bg-green-600 hover:bg-green-700 px-5 py-2 rounded text-white font-semibold shadow transition">
                Selesai
            </button>

        </form>

    @endif

</div>
</div>
@endsection