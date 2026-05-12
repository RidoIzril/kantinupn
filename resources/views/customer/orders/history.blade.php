@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 pt-16 md:pt-8">
    <h2 class="text-2xl font-bold mb-5 text-green-800">Riwayat Pesanan Anda</h2>
    
    {{-- Desktop TABLE view --}}
    <div class="bg-white shadow rounded-lg border hidden md:block">
        <table class="w-full min-w-full table-auto">
            <thead class="bg-green-700 text-white text-left">
                <tr>
                    <th class="py-3 px-4 font-medium">#</th>
                    <th class="py-3 px-4 font-medium">Tanggal</th>
                    <th class="py-3 px-4 font-medium">Total</th>
                    <th class="py-3 px-4 font-medium">Status</th>
                    <th class="py-3 px-4"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr class="border-b hover:bg-green-50 transition">
                    <td class="py-2 px-4">{{ $order->id }}</td>
                    <td class="py-2 px-4">{{ $order->order_tanggal ?? $order->created_at }}</td>
                    <td class="py-2 px-4">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>

                    <td class="py-2 px-4">

                        @php
                            $status = strtolower($order->order_status);

                            $color = match($status) {
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'diproses' => 'bg-blue-100 text-blue-800',
                                'siap' => 'bg-pink-100 text-purple-800',
                                'selesai' => 'bg-green-100 text-green-800',
                                'batal' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        @endphp

                        <span class="inline-block px-3 py-1 text-xs rounded-full {{ $color }}">
                            {{ ucfirst($order->order_status) }}
                        </span>

                    </td>

                    <td class="py-2 px-4">
                        <a href="{{ route('orders.history.show', [$order->id, 'token' => request('token')]) }}"
                           class="inline-block px-4 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow transition">
                            Lihat Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-6 text-gray-400">
                        Belum ada riwayat pesanan.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile CARD view --}}
    <div class="space-y-5 md:hidden">

        @forelse($orders as $order)

            @php
                $status = strtolower($order->order_status);

                $color = match($status) {
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'diproses' => 'bg-blue-100 text-blue-800',
                    'siap' => 'bg-pink-100 text-purple-800',
                    'selesai' => 'bg-green-100 text-green-800',
                    'batal' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800'
                };
            @endphp

            <div class="bg-white shadow rounded-2xl border px-5 py-4 flex flex-col gap-2">

                <div class="flex justify-between items-center">

                    <strong class="text-slate-700">
                        #{{ $order->id }}
                    </strong>

                    <span class="inline-block px-3 py-1 text-xs rounded-full {{ $color }}">
                        {{ ucfirst($order->order_status) }}
                    </span>

                </div>

                <div>
                    <span class="text-gray-500 text-xs">Tanggal:</span><br>

                    <span class="font-semibold">
                        {{ $order->order_tanggal ?? $order->created_at }}
                    </span>
                </div>

                <div>
                    <span class="text-gray-500 text-xs">Total:</span><br>

                    <span class="font-semibold text-green-700">
                        Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                    </span>
                </div>

                <div>
                    <a href="{{ route('orders.history.show', [$order->id, 'token' => request('token')]) }}"
                       class="inline-block w-full mt-3 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold text-center shadow transition">
                        Lihat Detail
                    </a>
                </div>

            </div>

        @empty

            <div class="bg-white rounded-2xl p-8 text-center text-gray-400 border border-slate-200">
                Belum ada riwayat pesanan.
            </div>

        @endforelse

    </div>
</div>
@endsection