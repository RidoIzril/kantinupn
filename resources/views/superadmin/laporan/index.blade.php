@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto mt-10">

    <h2 class="text-2xl font-bold mb-6 text-green-800">Laporan Penjualan (Superadmin)</h2>

    <!-- FILTER -->
    <div class="mb-6">

        <!-- DESKTOP -->
        <div class="hidden md:flex justify-between items-center">

            <form method="GET" class="flex gap-3 items-center">

                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="border rounded-lg px-3 py-2">

                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="border rounded-lg px-3 py-2">

                <select name="tenant_id" class="border rounded-lg px-3 py-2">
                    <option value="">Semua Tenant</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}"
                            {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>
                            {{ $tenant->tenant_name }}
                        </option>
                    @endforeach
                </select>

                <button class="bg-green-600 text-white px-5 py-2 rounded-lg">
                    Filter
                </button>

            </form>

            <a href="{{ route('superadmin.laporan.pdf', request()->all()) }}"
                class="inline-flex items-center gap-2 bg-red-500 text-white px-4 py-2 rounded-lg shadow">

                📄 Export PDF
            </a>

        </div>

        <!-- MOBILE -->
        <div class="md:hidden space-y-3">

            <form method="GET" class="grid grid-cols-1 gap-3">

                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="border rounded-lg px-3 py-2">

                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="border rounded-lg px-3 py-2">

            <<select name="tenant_id" class="border p-2 rounded">
                <option value="">Semua Tenant</option>

                @foreach($tenants as $t)
                    <option value="{{ $t->id }}"
                        {{ request('tenant_id') == $t->id ? 'selected' : '' }}>
                        {{ $t->tenant_name }}
                    </option>
                @endforeach

            </select>

                <button class="bg-green-600 text-white py-2 rounded-lg">
                    Filter
                </button>

            </form>

            <a href="{{ route('superadmin.laporan.pdf', request()->all()) }}"
                class="flex justify-center bg-red-500 text-white py-3 rounded-lg shadow">
                📄 Export PDF
            </a>

        </div>

    </div>

    <!-- SUMMARY -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
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
        <table class="min-w-[800px] w-full">
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="p-3">No</th>
                    <th class="p-3">Tenant</th>
                    <th class="p-3">Customer</th>
                    <th class="p-3">Tanggal</th>
                    <th class="p-3">Metode</th>
                    <th class="p-3">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksis as $t)
                <tr class="border-b">
                    <td class="p-3 text-center">{{ $loop->iteration }}</td>

                    <!-- TENANT -->
                    <td class="p-3">
                        @foreach($t->order->details as $d)
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">
                                {{ $d->produk->tenant->tenant_name ?? '-' }}
                            </span>
                        @endforeach
                    </td>

                    <td class="p-3">{{ $t->order->customer->nama_lengkap ?? '-' }}</td>

                    <td class="p-3 whitespace-nowrap text-center">
                        {{ $t->order->order_tanggal }}
                    </td>

                    <td class="p-3 text-center">{{ strtoupper($t->metode_pembayaran) }}</td>

                    <td class="p-3 text-right">
                        Rp {{ number_format($t->jumlah_bayar,0,',','.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center p-5 text-gray-400">
                        Belum ada data
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection