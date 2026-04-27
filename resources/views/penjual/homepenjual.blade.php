@extends('layouts.app')

@section('title', 'Dashboard Penjual')

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
        <p class="text-sm text-gray-500">Order Pending</p>
        <p id="orderPending" class="text-2xl font-bold">0</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Transaksi Selesai</p>
        <p id="transaksiSelesai" class="text-2xl font-bold">0</p>
    </div>
</div>

{{-- ALERT NOTIF --}}
<div id="notifStokContainer"></div>

{{-- ALERT PESANAN MASUK BARU --}}
<div id="alertPesananMasuk" class="hidden mb-5 p-4 rounded-lg bg-green-100 border border-green-300 text-green-900 font-semibold shadow"></div>

@endsection

@push('scripts')
<script>
let lastOrderPending = null;

async function fetchDashboardData(headers, isFirst = false) {
    const API_DASH = "{{ url('/api/penjual/dashboard') }}";
    const dashRes = await fetch(API_DASH, { method: 'GET', headers });
    const raw = await dashRes.text();

    let dashJson = {};
    try { dashJson = JSON.parse(raw); } catch (_) {}

    if (!dashRes.ok) return;

    const s = dashJson.data?.statistik || {};
    const p = dashJson.data?.penjual || {};

    document.getElementById('namaPenjual').innerText = p.username ?? '-';
    document.getElementById('jumlahProduk').innerText = s.jumlah_produk ?? 0;
    document.getElementById('orderPending').innerText = s.order_pending ?? 0;
    document.getElementById('transaksiSelesai').innerText = s.transaksi_selesai ?? 0;
    document.getElementById('totalPendapatan').innerText =
        'Rp ' + Number(s.total_pendapatan ?? 0).toLocaleString('id-ID');

    const pending = Number(s.order_pending ?? 0);

    if (lastOrderPending !== null && pending > lastOrderPending) {
        showAlertPesananMasuk(pending - lastOrderPending);
    }
    lastOrderPending = pending;

    if(isFirst) lastOrderPending = pending;
}

function showAlertPesananMasuk(jumlahBaru) {
    const el = document.getElementById('alertPesananMasuk');
    el.innerText = jumlahBaru + ' pesanan baru masuk!';
    el.classList.remove('hidden');
    el.classList.add('animate-pulse');

    setTimeout(() => {
        el.classList.add('hidden');
        el.classList.remove('animate-pulse');
    }, 5000);
}

{{-- FETCH NOTIFICATIONS --}}
async function fetchNotifications(headers) {
    const res = await fetch("{{ url('/api/penjual/notifications') }}", {
        method: 'GET',
        headers
    });

    if (!res.ok) return;

    const data = await res.json();
    const container = document.getElementById('notifStokContainer');

    container.innerHTML = '';

    data.notifications.forEach(notif => {

        if (notif.data.type === 'order') {
            container.innerHTML += `
                <div class="mb-3 p-3 rounded-lg bg-green-100 border border-green-300 text-green-900 shadow">
                    🛒 ${notif.data.message}
                    <br>
                    <span class="text-sm">
                        Order ID: <b>#${notif.data.order_id}</b> 
                        (Rp ${Number(notif.data.total || 0).toLocaleString('id-ID')})
                    </span>
                </div>
            `;
        } else {
            container.innerHTML += `
                <div class="mb-3 p-3 rounded-lg bg-yellow-100 border border-yellow-300 text-yellow-900 shadow">
                    ⚠️ ${notif.data.message}
                    <br>
                    <span class="text-sm">
                        Produk: <b>${notif.data.nama_produk ?? '-'}</b> 
                        (Stok: ${notif.data.stok ?? 0})
                    </span>
                </div>
            `;
        }

    });
}

document.addEventListener('DOMContentLoaded', async function () {
    const token = localStorage.getItem('token');
    const role  = localStorage.getItem('role');

    if (!token || role !== 'penjual') {
        window.location.replace("{{ url('/login') }}");
        return;
    }

    const headers = {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`
    };

    const API_ME   = "{{ url('/api/me') }}";

    try {
        const meRes = await fetch(API_ME, { method: 'GET', headers });
        if (!meRes.ok) {
            localStorage.removeItem('token');
            localStorage.removeItem('role');
            window.location.replace("{{ url('/login') }}");
            return;
        }

        // LOAD AWAL
        await fetchDashboardData(headers, true);
        await fetchNotifications(headers);

        // POLLING
        setInterval(() => {
            fetchDashboardData(headers, false);
            fetchNotifications(headers);
        }, 10000);

    } catch (err) {
        console.error('Fetch exception:', err);
    }
});
</script>
@endpush