@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-10">

    <h2 class="text-2xl font-bold mb-6 text-green-800">Laporan Penjualan</h2>

    <!-- FILTER -->
    <div class="mb-6">

    <!-- DESKTOP -->
    <div class="hidden md:flex justify-between items-center">

        <!-- FILTER -->
        <form method="GET" class="flex gap-3 items-center">

            <input type="date" name="start_date" value="{{ request('start_date') }}"
                class="border rounded-lg px-3 py-2">

            <input type="date" name="end_date" value="{{ request('end_date') }}"
                class="border rounded-lg px-3 py-2">

            <button class="bg-green-600 text-white px-5 py-2 rounded-lg">
                Filter
            </button>

        </form>

        <!-- EXPORT -->
        <a href="{{ route('penjual.laporan.pdf', request()->all()) }}"
            class="inline-flex items-center gap-2 bg-red-500 text-white px-4 py-2 rounded-lg shadow">

            <svg xmlns="http://www.w3.org/2000/svg" 
                 class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6z"/>
            </svg>

            Export PDF
        </a>

    </div>

    <!-- MOBILE -->
    <div class="md:hidden space-y-3">

        <form method="GET" class="grid grid-cols-1 gap-3">

            <input type="date" name="start_date" value="{{ request('start_date') }}"
                class="border rounded-lg px-3 py-2 w-full">

            <input type="date" name="end_date" value="{{ request('end_date') }}"
                class="border rounded-lg px-3 py-2 w-full">

            <button class="bg-green-600 text-white py-2 rounded-lg w-full">
                Filter
            </button>

        </form>

        <a href="{{ route('penjual.laporan.pdf', request()->all()) }}"
            class="flex items-center justify-center gap-2 bg-red-500 text-white py-3 rounded-lg shadow w-full">

            <svg xmlns="http://www.w3.org/2000/svg" 
                 class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M6 2a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6z"/>
            </svg>

            Export PDF
        </a>

    </div>

</div>

    <!-- SUMMARY -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white p-4 rounded shadow">
            <p>Total Pendapatan</p>
            <p class="text-xl font-bold text-green-700">
                Rp {{ number_format($totalPendapatan,0,',','.') }}
            </p>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <p>Total Transaksi</p>
            <p class="text-xl font-bold">{{ $totalTransaksi }}</p>
        </div>
    </div>

    <!-- TABEL -->
    <div class="bg-white rounded shadow overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="p-3">No</th>
                    <th class="p-3">Customer</th>
                    <th class="p-3">Tanggal</th>
                    <th class="p-3">Metode</th>
                    <th class="p-3">Total</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksis as $t)
                <tr class="border-b">
                    <td class="p-3 text-center">{{ $loop->iteration }}</td>
                    <td class="p-3">{{ $t->order->customer->nama_lengkap ?? '-' }}</td>
                    <td class="p-3 text-center">{{ $t->waktu_bayar }}</td>
                    <td class="p-3 text-center">{{ strtoupper($t->metode_pembayaran) }}</td>
                    <td class="p-3 text-right">
                        Rp {{ number_format($t->jumlah_bayar,0,',','.') }}
                    </td>
                    <td class="p-3 text-center">
    <a href="{{ route('penjual.laporan.detail', $t->id) }}"
       class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm">
        Detail
    </a>
</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center p-5 text-gray-400">
                        Belum ada data
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection