@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-100">
    <div class="text-center">
        <h1 class="text-4xl font-bold mb-3">Selamat Datang di Kantin NKRI</h1>
        <p class="text-slate-600 mb-6">Pesan makanan favoritmu dengan cepat.</p>

        <div class="flex justify-center gap-3">
            <a href="{{ route('customer.homecustomer') }}" class="px-5 py-2 rounded bg-green-600 text-white">
                Lihat Menu
            </a>
            <a href="{{ route('login') }}" class="px-5 py-2 rounded bg-slate-200">
                Login
            </a>
        </div>
    </div>
</div>
@endsection