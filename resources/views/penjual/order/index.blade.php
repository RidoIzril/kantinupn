@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-10">
    <h2 class="text-3xl font-bold mb-8 text-green-900 text-center tracking-wide">Daftar Pesanan Masuk</h2>

    @if(session('success'))
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-2 mb-4 rounded shadow">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-xl rounded-lg overflow-x-auto border mb-8">
        <table class="min-w-full table-auto">
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="py-3 px-4 font-semibold text-center">No</th>
                    <th class="py-3 px-4 font-semibold text-left">Customer</th>
                    <th class="py-3 px-4 font-semibold text-center">Tanggal</th>
                    <th class="py-3 px-4 font-semibold text-right">Total</th>
                    <th class="py-3 px-4 font-semibold text-center">Status</th>
                    <th class="py-3 px-4 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr class="border-b hover:bg-green-50 transition group">
                    <td class="py-3 px-4 text-center">{{ $loop->iteration }}</td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                                {{ $order->customer->nama_lengkap ?? '-' }}
                                @if(isset($order->customer->telepon))
                                    <div class="text-xs text-slate-400">{{ $order->customer->telepon }}</div>
                                @endif
                        </div>
                    </td>
                    <td class="py-3 px-4 text-center">{{ $order->order_tanggal ?? $order->created_at }}</td>
                    <td class="py-3 px-4 text-right font-semibold text-green-700">
                        Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                    </td>
                    <td class="py-3 px-4 text-center">
                        @php
                            $status = strtolower($order->order_status);
                            $colors = [
                                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                'diproses' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'selesai' => 'bg-green-100 text-green-800 border-green-200',
                                'batal'   => 'bg-red-100 text-red-800 border-red-200',
                                'default' => 'bg-gray-100 text-gray-800 border-gray-200',
                            ];
                            $color = $colors[$status] ?? $colors['default'];
                        @endphp
                        <span class="inline-block px-3 py-1 rounded-full border text-xs font-medium {{$color}} shadow-sm capitalize">
                            {{ $order->order_status }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <a href="{{ route('penjual.order.show', ['id' => $order->id, 'token' => request('token')]) }}"
                           class="inline-block px-3 py-1 rounded-md bg-green-600 text-white hover:bg-green-700 transition font-medium shadow
                                  group-hover:scale-105 active:scale-95">
                           Lihat
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-400">Belum ada pesanan masuk.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection