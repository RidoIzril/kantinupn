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
            {{ request('keyword') ? 'Hasil Pencarian' : 'Rekomendasi Penjual' }}
        </h2>

        {{-- LIST --}}
        <div class="space-y-6">
            @forelse ($penjuals as $penjual)
                @php
                    $produkList = $penjual->tenant->produks ?? collect();
                @endphp

                <div class="group bg-white rounded-2xl border border-slate-200 shadow-md p-4 md:p-5 transition-all duration-300 hover:scale-[1.025] hover:shadow-xl animate-fade-up relative overflow-hidden">
                    <span class="absolute top-0 right-0 px-4 py-1 bg-emerald-500 text-white rounded-bl-2xl text-xs shadow -mt-px font-semibold animate-fade-in-down z-10">
                        {{ $produkList->count() }} menu
                    </span>
                    <div class="flex items-start gap-5">
                        <img
                            src="{{ !empty($penjual->tenant?->foto_tenant) ? asset('storage/'.$penjual->tenant->foto_tenant) : asset('images/default-store.png') }}"
                            class="w-20 h-20 rounded-xl object-cover border border-slate-200 shadow group-hover:shadow-lg transition duration-200"
                            alt="Foto Tenant"
                        >
                        <div class="flex-1">
                            <h3 class="font-extrabold text-xl text-green-700 group-hover:text-emerald-600 transition duration-150">
                                {{ $penjual->tenant?->tenant_name ?? 'Tenant belum diisi' }}
                            </h3>
                            <p class="text-xs text-slate-500 mb-2 italic animate-fade-in">{{ $produkList->count() ? 'Menu tersedia' : 'Belum ada menu' }}</p>
                            @if (request('keyword'))
                                <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                    @forelse ($produkList as $product)
                                        <div class="text-center bg-slate-50 rounded-xl p-2 shadow-sm hover:shadow-md transition-all animate-fade-in-up delay-50">
                                            <img
                                                src="{{ !empty($product->foto_produk) ? asset('storage/'.$product->foto_produk) : asset('images/default-product.png') }}"
                                                class="w-full h-20 object-cover rounded-lg mb-1 border border-slate-200"
                                                alt="{{ $product->nama }}"
                                            >
                                            <p class="text-sm font-medium line-clamp-1">{{ $product->nama }}</p>
                                            <p class="text-xs text-emerald-700 font-bold">
                                                Rp {{ number_format($product->harga,0,',','.') }}
                                            </p>
                                        </div>
                                    @empty
                                        <div class="col-span-full text-sm text-slate-500">Tidak ada menu.</div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                        <div>
                            <a id="btn-lihat-{{ $penjual->id }}"
                               href="{{ route('customer.menu.show', ['id' => $penjual->id]) }}"
                               class="bg-green-500 hover:bg-emerald-600 transition-colors font-semibold px-4 py-2 rounded-lg text-white shadow hover:scale-105 hover:shadow-lg active:scale-95 outline-none focus:ring-2 focus:ring-emerald-400 animate-fade-in-right">
                                Lihat Penjual →
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-slate-500 py-10 animate-fade-in">
                    Tidak ada penjual ditemukan
                </div>
            @endforelse
        </div>
    </main>
</div>

{{-- Animasi CSS --}}
<style>
@keyframes fade-in {
  from{ opacity:0; transform: translateY(40px);}
  to{ opacity:1; transform: translateY(0);}
}
@keyframes fade-in-up {
  from{ opacity:0; transform: translateY(24px);}
  to{ opacity:1; transform: translateY(0);}
}
@keyframes fade-in-right {
  from{ opacity:0; transform: translateX(24px);}
  to{ opacity:1; transform: translateX(0);}
}
@keyframes fade-in-down {
  from{ opacity:0; transform: translateY(-20px);}
  to{ opacity:1; transform: translateY(0);}
}
@keyframes bounce-in {
  0%   { opacity: 0; transform: scale(.9);}
  60%  { opacity: 1; transform: scale(1.04);}
  80%  { transform: scale(0.98);}
  100% { transform: scale(1);}
}
.animate-fade-in { animation: fade-in .6s cubic-bezier(.32,.72,.48,.99) both;}
.animate-fade-up { animation: fade-in-up .6s both;}
.animate-fade-in-up { animation: fade-in-up .8s both;}
.animate-fade-in-right{ animation: fade-in-right .6s both;}
.animate-fade-in-down{ animation: fade-in-down .6s both;}
.animate-bounce-in { animation: bounce-in .66s;}
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
        // tempel token ke semua link "Lihat Penjual"
        document.querySelectorAll('a[id^="btn-lihat-"]').forEach((el) => {
            const url = new URL(el.href, window.location.origin);
            url.searchParams.set('token', token);
            el.href = url.toString();
        });
    }
})();
</script>
@endsection