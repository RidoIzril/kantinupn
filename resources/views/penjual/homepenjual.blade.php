@extends('layouts.app')

@section('title', 'Dashboard Penjual')
@include('penjual.sidebarpenjual')
@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Dashboard Penjual</h1>
        <p class="text-sm text-gray-500">
            Selamat datang,
            <span id="namaPenjual" class="font-semibold">...</span>
        </p>
    </div>

    <img src="{{ asset('template/dist/assets/compiled/jpg/1.jpg') }}"
         class="w-10 h-10 rounded-full object-cover">
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Jumlah Produk</p>
        <p id="jumlahProduk" class="text-2xl font-bold">0</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Total Pendapatan</p>
        <p id="totalPendapatan" class="text-xl font-bold">Rp 0</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Transaksi Pending</p>
        <p id="transaksiPending" class="text-2xl font-bold">0</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Transaksi Selesai</p>
        <p id="transaksiSelesai" class="text-2xl font-bold">0</p>
    </div>
</div>

<script>
(async function () {
    const token = localStorage.getItem('token');
    const role = localStorage.getItem('role');

    if (!token || role !== 'penjual') {
        window.location.href = '/login';
        return;
    }

    // validasi token + role
    const meRes = await fetch('/api/me', {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    });

    if (!meRes.ok) {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        window.location.href = '/login';
        return;
    }

    const meJson = await meRes.json();
    if ((meJson.user?.role || '').toLowerCase() !== 'penjual') {
        window.location.href = '/login';
        return;
    }

    // ambil dashboard
    const dashRes = await fetch('/api/penjual/dashboard', {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    });

    if (!dashRes.ok) {
        alert('Unauthorized / gagal ambil dashboard');
        return;
    }

    const json = await dashRes.json();
    const s = json.data?.statistik || {};
    const p = json.data?.penjual || {};

    document.getElementById('namaPenjual').innerText = p.username ?? meJson.user.username ?? '-';
    document.getElementById('jumlahProduk').innerText = s.jumlah_produk ?? 0;
    document.getElementById('transaksiPending').innerText = s.transaksi_pending ?? 0;
    document.getElementById('transaksiSelesai').innerText = s.transaksi_selesai ?? 0;
    document.getElementById('totalPendapatan').innerText =
        'Rp ' + Number(s.total_pendapatan ?? 0).toLocaleString('id-ID');
})();
</script>
@endsection