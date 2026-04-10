@extends('layouts.app')

<script>
const token = localStorage.getItem('token');
const role  = localStorage.getItem('role');

if (!token || role !== 'customer') {
    window.location.href = '/login';
}
</script>

@section('content')
<div class="flex min-h-screen bg-slate-100">

    {{-- SIDEBAR --}}
    @include('customer.sidebarcus')

    {{-- MAIN --}}
    <div class="flex-1 p-6 max-w-7xl mx-auto">

        {{-- SEARCH BAR + BUTTON --}}
        <form method="GET" class="mb-6 flex gap-3">
            <input
                type="text"
                name="keyword"
                value="{{ request('keyword') }}"
                placeholder="Cari menu atau penjual..."
                class="flex-1 h-12 px-5 rounded-full border border-slate-300 bg-white focus:outline-none focus:border-green-600"
            >

            <button
                type="submit"
                class="h-12 px-6 rounded-full bg-green-600 text-white font-semibold hover:bg-green-700 transition">
                Search
            </button> 
        </form>

        {{-- JUDUL --}}
        <h2 class="text-lg font-bold text-slate-800 mb-4">
            {{ $keyword ? 'Hasil Pencarian' : 'Rekomendasi Penjual' }}
        </h2>

        {{-- LIST PENJUAL --}}
        <div class="space-y-4">
            @forelse ($penjuals as $penjual)
                @php
                    $produkList = $penjual->tenant->produks ?? collect();
                @endphp

                <div class="bg-white rounded-xl shadow-sm p-4">

                    {{-- HEADER PENJUAL --}}
                    <div class="flex gap-4">
                        <img
                            src="{{ !empty($penjual->tenant?->foto_tenant) ? asset('storage/'.$penjual->tenant->foto_tenant) : asset('images/default-store.png') }}"
                            class="w-20 h-20 rounded-lg object-cover"
                            alt="Foto Tenant"
                        >

                        <div class="flex-1">
                            <h3 class="font-bold text-2xl text-slate-800">
                                {{ $penjual->tenant?->tenant_name ?? 'Tenant belum diisi' }}
                            </h3>

                            <p class="text-sm text-slate-500">
                                {{ $produkList->count() }} menu tersedia
                            </p>
                        </div>
                    </div>

                    {{-- MENU (MUNCUL SAAT SEARCH) --}}
                    @if ($keyword)
                        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @forelse ($produkList as $product)
                                <div class="text-center">
                                    <img
                                        src="{{ !empty($product->foto_produk) ? asset('storage/'.$product->foto_produk) : asset('images/default-product.png') }}"
                                        class="w-full h-24 object-cover rounded-lg mb-1"
                                        alt="{{ $product->nama }}"
                                    >

                                    <p class="text-sm font-medium line-clamp-1">
                                        {{ $product->nama }}
                                    </p>

                                    <p class="text-sm text-green-600 font-semibold">
                                        Rp {{ number_format($product->harga, 0, ',', '.') }}
                                    </p>
                                </div>
                            @empty
                                <div class="col-span-full text-sm text-slate-500">
                                    Tidak ada menu.
                                </div>
                            @endforelse
                        </div>
                    @endif

                    {{-- FOOTER --}}
                    <div class="mt-4 text-right">
                        @if(!empty($penjual->id))
                            <a href="{{ route('customer.menu.show', ['id' => $penjual->id]) }}"
                               class="text-green-600 text-sm font-semibold">
                                Lihat Penjual →
                            </a>
                        @else
                            <span class="text-slate-400 text-sm">ID penjual tidak tersedia</span>
                        @endif
                    </div>

                </div>
            @empty
                <div class="text-center text-slate-500 py-10">
                    Tidak ada penjual ditemukan
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection