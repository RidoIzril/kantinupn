@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-100 via-green-50 to-slate-200">

    <main class="max-w-7xl mx-auto px-4 md:px-6 py-6">
        {{-- SEARCH --}}
        <form method="GET" class="mb-5 flex gap-3 animate-fade-in">
            <input
                type="text"
                name="keyword"
                value="{{ request('keyword') }}"
                placeholder="Cari menu atau penjual..."
                class="flex-1 h-12 px-5 rounded-full border border-slate-300 bg-white focus:outline-none focus:border-green-600 shadow-md transition"
            >
            <button
                type="submit"
                class="h-12 px-6 rounded-full bg-gradient-to-r from-green-600 to-emerald-500 text-white font-semibold hover:scale-105 hover:shadow-lg transition-all duration-200">
                🔍 Search
            </button>
        </form>

        {{-- GUEST INFO --}}
        <div id="guest-info" class="hidden mb-5 bg-blue-50 text-blue-700 px-4 py-3 rounded-lg text-sm border border-blue-200 animate-bounce-in">
            Kamu sedang melihat sebagai <b>Guest</b>. Untuk memesan menu, silakan
            <a href="{{ route('login') }}" class="underline font-semibold hover:text-blue-900 transition">Login</a>.
        </div>

        {{-- TITLE --}}
        <h2 class="text-2xl font-extrabold text-slate-800 mb-4 animate-fade-in">
            {{ request('keyword') ? 'Hasil Pencarian Menu' : 'Rekomendasi Penjual' }}
        </h2>

        {{-- LIST --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @forelse ($penjuals as $penjual)
                @php
                    $produkList = $penjual->tenant->produks ?? collect();

                    // FILTER MENU BERDASARKAN KEYWORD JIKA ADA
                    $filteredMenus = !request('keyword')
                        ? $produkList
                        : $produkList->filter(function($produk) {
                            return stripos($produk->nama, request('keyword')) !== false;
                        });
                @endphp

                <a href="{{ route('customer.menu.show', ['id' => $penjual->id]) }}"
                   class="group bg-white rounded-2xl border border-slate-200 shadow-md flex flex-col overflow-hidden transition-all duration-300 hover:scale-105 hover:shadow-xl min-h-[280px]">
                    
                    {{-- FOTO TENANT FULL --}}
                    <div class="w-full aspect-square bg-slate-100 border-b border-slate-200 flex items-center justify-center overflow-hidden">
                        <img
                            src="{{ !empty($penjual->tenant?->foto_tenant) ? asset('storage/'.$penjual->tenant->foto_tenant) : asset('images/default-store.png') }}"
                            class="object-cover w-full h-full"
                            alt="Foto Tenant"
                        >
                    </div>
                    
                    <div class="flex-1 p-3 flex flex-col">
                        <h3 class="font-extrabold text-base sm:text-lg text-green-700 group-hover:text-emerald-600 transition duration-150 line-clamp-2 mb-1 text-center">
                            {{ $penjual->tenant?->tenant_name ?? 'Tenant belum diisi' }}
                        </h3>
                        <span class="inline-block bg-emerald-500 text-white rounded-full text-xs px-3 py-1 shadow font-semibold my-1 text-center">
                            Lihat Menu Tenant
                        </span>
                        
                        {{-- JIKA SEARCH MODE, TAMPIL MENU YANG MATCH DI BAWAH FOTO TENANT --}}
                        @if(request('keyword'))
                            <div class="mt-2 space-y-2">
                                @forelse ($filteredMenus as $product)
                                    <div class="flex items-center bg-slate-50 rounded-lg px-2 py-2 gap-2 border border-slate-200">
                                        <img
                                            src="{{ !empty($product->foto_produk) ? asset('storage/'.$product->foto_produk) : asset('images/default-product.png') }}"
                                            class="object-cover w-10 h-10 rounded-md border"
                                            alt="{{ $product->nama }}"
                                        >
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium truncate">{{ $product->nama }}</p>
                                            <p class="text-xs text-emerald-700 font-bold">
                                                Rp {{ number_format($product->harga,0,',','.') }}
                                            </p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-[13px] text-gray-400 italic text-center">Tidak ada menu cocok.</div>
                                @endforelse
                            </div>
                        @endif

                    </div>
                </a>
            @empty
                <div class="col-span-full text-center text-slate-500 py-10 animate-fade-in">
                    Tidak ada penjual ditemukan
                </div>
            @endforelse
        </div>
    </main>
</div>

<style>
/* Animasi (tetap seperti animasi-mu sebelumnya) */
/* ... */
</style>

<script>
(function () {
    const token = localStorage.getItem('token');
    const role  = localStorage.getItem('role');
    const isCustomer = !!token && role === 'customer';

    const guestInfo = document.getElementById('guest-info');

    if (!isCustomer) {
        guestInfo?.classList.remove('hidden');
    } else {
        document.querySelectorAll('a[id^="btn-lihat-"]').forEach((el) => {
            const url = new URL(el.href, window.location.origin);
            url.searchParams.set('token', token);
            el.href = url.toString();
        });
    }
})();
</script>
@endsection