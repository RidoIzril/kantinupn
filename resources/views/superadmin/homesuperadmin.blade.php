@extends('layouts.app')

@section('title', 'Dashboard Superadmin')

@section('content')

{{-- HEADER --}}
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">
            Dashboard Superadmin
        </h1>
        <p class="text-sm text-gray-500">
            Selamat datang,
            <span class="font-semibold">
                {{ auth()->guard('superadmin')->user()->username ?? 'Superadmin' }}
            </span>
        </p>
    </div>

    <img src="{{ asset('template/dist/assets/compiled/jpg/1.jpg') }}"
         class="w-10 h-10 rounded-full object-cover">
</div>

{{-- STATISTIK --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

    {{-- JUMLAH PENJUAL --}}
    <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
        <div class="bg-indigo-600 text-white p-3 rounded-lg">
            <i class="bi bi-shop text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Jumlah Penjual</p>
            <p class="text-2xl font-bold">
                {{ $jumlahPenjual ?? 0 }}
            </p>
        </div>
    </div>

    {{-- JUMLAH CUSTOMER --}}
    <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
        <div class="bg-blue-600 text-white p-3 rounded-lg">
            <i class="bi bi-people text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Jumlah Customer</p>
            <p class="text-2xl font-bold">
                {{ $jumlahCustomer ?? 0 }}
            </p>
        </div>
    </div>

    {{-- JUMLAH KATEGORI --}}
    <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
        <div class="bg-yellow-500 text-white p-3 rounded-lg">
            <i class="bi bi-tags text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Kategori</p>
            <p class="text-2xl font-bold">
                {{ $jumlahKategori ?? 0 }}
            </p>
        </div>
    </div>

    {{-- TOTAL TRANSAKSI --}}
    <div class="bg-white rounded-xl shadow p-5 flex items-center gap-4">
        <div class="bg-emerald-600 text-white p-3 rounded-lg">
            <i class="bi bi-receipt text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Transaksi</p>
            <p class="text-2xl font-bold">
                {{ $jumlahTransaksi ?? 0 }}
            </p>
        </div>
    </div>

</div>
@endsection
