@extends('layouts.app')

<script>
const token = localStorage.getItem('token');
const role  = localStorage.getItem('role');

if(!token || role !== 'customer'){
    window.location.href = '/login';
}
</script>
@section('content')
<div class="flex min-h-screen bg-slate-100">

    {{-- SIDEBAR --}}
    @include('customer.sidebarcus')

    {{-- MAIN --}}
    <div class="flex-1 p-6 max-w-7xl mx-auto">

        {{-- SEARCH BAR --}}
        <form method="GET" class="mb-6">
            <input
                type="text"
                name="keyword"
                value="{{ request('keyword') }}"
                placeholder="Cari menu atau penjual..."
                class="w-full h-12 px-5 rounded-full
                       border border-slate-300
                       bg-white
                       focus:outline-none focus:border-green-600">
        </form>

        {{-- JUDUL --}}
        <h2 class="text-lg font-bold text-slate-800 mb-4">
            {{ $keyword ? 'Hasil Pencarian' : 'Rekomendasi Penjual' }}
        </h2>

        {{-- LIST PENJUAL --}}
        <div class="space-y-4">
            @forelse ($penjuals as $penjual)
            <div class="bg-white rounded-xl shadow-sm p-4">

                {{-- HEADER PENJUAL --}}
                <div class="flex gap-4">
                    <img src="{{ asset('storage/'.$penjual->foto_tenant) }}"
                         class="w-20 h-20 rounded-lg object-cover">

                    <div class="flex-1">
                        <h3 class="font-bold text-2xl text-slate-800">
                            {{ $penjual->penjual_tenantname }}
                        </h3>

                        <p class="text-sm text-slate-500">
                            {{ $penjual->products_count ?? $penjual->products->count() }} menu tersedia
                        </p>
                    </div>
                </div>

                {{-- MENU (MUNCUL HANYA SAAT SEARCH) --}}
                @if ($keyword)
                <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach ($penjual->products as $product)
                    <div class="text-center">
                        <img src="{{ asset('storage/'.$product->product_image) }}"
                             class="w-full h-24 object-cover rounded-lg mb-1">

                        <p class="text-sm font-medium line-clamp-1">
                            {{ $product->product_name }}
                        </p>

                        <p class="text-sm text-green-600 font-semibold">
                            Rp {{ number_format($product->product_price,0,',','.') }}
                        </p>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- FOOTER --}}
                <div class="mt-4 text-right">
                    <a href="{{ route('customer.menu.show', $penjual->penjual_id) }}"
                    class="text-green-600 text-sm font-semibold">
                    Lihat Penjual →
                    </a>
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
