@extends('layouts.app')

@section('title', 'Dashboard Penjual')
<script>
const token = localStorage.getItem('token');
const role  = localStorage.getItem('role');

if(!token){
    window.location.href = '/login';
}

if(role !== 'penjual'){
    window.location.href = '/login';
}
</script>
@section('content')

{{-- HEADER --}}
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            Dashboard Penjual
        </h1>
        <p class="text-sm text-gray-500">
            Selamat datang,
            <span class="font-semibold">
                {{ auth()->guard('penjual')->user()->penjual_username }}
            </span>
        </p>
    </div>

    <img src="{{ asset('template/dist/assets/compiled/jpg/1.jpg') }}"
         class="w-10 h-10 rounded-full object-cover">
</div>

{{-- STATISTIK --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

    <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
        <div class="bg-blue-600 text-white p-3 rounded-lg">
            <i class="bi bi-box-seam text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Jumlah Produk</p>
            <p class="text-2xl font-bold">{{ $jumlahProduk }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
        <div class="bg-green-600 text-white p-3 rounded-lg">
            <i class="bi bi-currency-dollar text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Pendapatan</p>
            <p class="text-xl font-bold">
                Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
        <div class="bg-yellow-500 text-white p-3 rounded-lg">
            <i class="bi bi-clock-history text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Transaksi Pending</p>
            <p class="text-2xl font-bold">{{ $transaksiPending }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
        <div class="bg-emerald-600 text-white p-3 rounded-lg">
            <i class="bi bi-check-circle text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Transaksi Selesai</p>
            <p class="text-2xl font-bold">{{ $transaksiSelesai }}</p>
        </div>
    </div>

</div>
@endsection
