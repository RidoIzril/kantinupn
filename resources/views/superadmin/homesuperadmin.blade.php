@extends('layouts.app')

<script>
const token = localStorage.getItem('token');
const role  = localStorage.getItem('role');

if(!token){
    window.location.href = '/login';
}

if(role !== 'superadmin'){
    window.location.href = '/login';
}
</script>

@section('title', 'Dashboard Superadmin')

@section('content')
<div class="pt-20 md:pt-4 md:ml-64 md:pl-8 px-4 md:px-8 w-full max-w-none mx-0"></div>

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

{{-- GRAFIK --}}
<div class="bg-white rounded-xl shadow p-5 mt-8">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Penjualan per Tenant (Mingguan)</h2>
            <p class="text-sm text-gray-500">Filter berdasarkan tanggal order (order_tanggal)</p>
        </div>

        {{-- FILTER TANGGAL --}}
        <form id="filterGrafik" class="flex flex-col sm:flex-row gap-3 sm:items-end">
            <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1">Tanggal Mulai</label>
                <input
                    type="date"
                    id="start_date"
                    name="start_date"
                    class="border rounded-lg px-3 py-2 text-sm"
                />
            </div>

            <div class="flex flex-col">
                <label class="text-xs text-gray-500 mb-1">Tanggal Akhir</label>
                <input
                    type="date"
                    id="end_date"
                    name="end_date"
                    class="border rounded-lg px-3 py-2 text-sm"
                />
            </div>

            <div class="flex gap-2">
                <button
                    type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-4 py-2 text-sm"
                >
                    Terapkan
                </button>

                <button
                    type="button"
                    id="btnReset"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg px-4 py-2 text-sm border"
                >
                    Reset
                </button>
            </div>
        </form>
    </div>

    {{-- info kecil --}}
    <div class="flex items-center justify-between mb-3">
        <p id="rangeInfo" class="text-xs text-gray-500"></p>
        <p id="loadingInfo" class="text-xs text-gray-500 hidden">Memuat data grafik...</p>
    </div>

    <div style="height: 380px;">
        <canvas id="chartPenjualanMingguan"></canvas>
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
(function () {
    const endpoint = "/superadmin/chart/penjualan-mingguan";

    const startInput = document.getElementById('start_date');
    const endInput   = document.getElementById('end_date');
    const form       = document.getElementById('filterGrafik');
    const btnReset   = document.getElementById('btnReset');

    const rangeInfo   = document.getElementById('rangeInfo');
    const loadingInfo = document.getElementById('loadingInfo');

    let chartInstance = null;

    function toYYYYMMDD(dateObj) {
        const yyyy = dateObj.getFullYear();
        const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
        const dd = String(dateObj.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    function setDefaultDates() {
        // default: 8 minggu terakhir
        const end = new Date();
        const start = new Date();
        start.setDate(end.getDate() - (8 * 7));

        startInput.value = toYYYYMMDD(start);
        endInput.value   = toYYYYMMDD(end);

        rangeInfo.textContent = `Range: ${startInput.value} s/d ${endInput.value}`;
    }

    function buildUrlWithParams(start_date, end_date) {
        const url = new URL(endpoint, window.location.origin);
        if (start_date) url.searchParams.set('start_date', start_date);
        if (end_date)   url.searchParams.set('end_date', end_date);
        return url.toString();
    }

    function buildDatasets(datasets) {
        const colors = [
            '#4f46e5','#0ea5e9','#f59e0b','#10b981','#ef4444','#8b5cf6','#14b8a6','#f97316',
            '#22c55e','#3b82f6','#a855f7','#eab308'
        ];

        return (datasets || []).map((ds, i) => ({
            label: ds.label,
            data: ds.data,
            borderColor: colors[i % colors.length],
            backgroundColor: colors[i % colors.length],
            tension: 0.3,
            fill: false,
            pointRadius: 2,
            borderWidth: 2,
        }));
    }

    async function loadChart() {
        const start_date = startInput.value;
        const end_date   = endInput.value;

        rangeInfo.textContent = `Range: ${start_date} s/d ${end_date}`;

        const url = buildUrlWithParams(start_date, end_date);

        loadingInfo.classList.remove('hidden');

        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();

        loadingInfo.classList.add('hidden');

        const ctx = document.getElementById('chartPenjualanMingguan').getContext('2d');

        // destroy chart lama
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        const datasets = buildDatasets(json.datasets);

        chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: json.labels || [],
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const v = context.parsed.y || 0;
                                return `${context.dataset.label}: Rp ${Number(v).toLocaleString('id-ID')}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => 'Rp ' + Number(value).toLocaleString('id-ID')
                        }
                    }
                }
            }
        });
    }

    // init
    setDefaultDates();
    loadChart();

    // submit filter
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // validasi sederhana
        if (startInput.value && endInput.value && startInput.value > endInput.value) {
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
            return;
        }

        loadChart();
    });

    // reset
    btnReset.addEventListener('click', function () {
        setDefaultDates();
        loadChart();
    });
})();
</script>
@endsection