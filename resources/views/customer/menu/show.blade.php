@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gray-100">

    @include('customer.sidebarcus')

    <div class="flex-1 p-6">

        {{-- HEADER PENJUAL --}}
        <div class="bg-white rounded-xl shadow p-6 mb-6 flex gap-4">
            <img src="{{ $penjual->foto_tenant
                ? asset('storage/'.$penjual->foto_tenant)
                : asset('images/default-store.png') }}"
                class="w-24 h-24 rounded-xl object-cover">

            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    {{ $penjual->penjual_tenantname }}
                </h1>
                <p class="text-sm text-gray-500">
                    {{ $penjual->penjual_fullname }}
                </p>
            </div>
        </div>

        {{-- LIST MENU --}}
        <h2 class="text-lg font-semibold mb-4">Menu</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($penjual->products as $product)
                <div class="bg-white rounded-xl shadow p-4 flex flex-col">
                    <img src="{{ asset('storage/'.$product->product_image) }}"
                         class="h-36 w-full object-cover rounded-lg mb-3">

                    <h3 class="font-semibold text-sm line-clamp-1">
                        {{ $product->product_name }}
                    </h3>

                    <p class="text-xs text-gray-500">
                        {{ $product->category->category_name }}
                    </p>

                    <p class="text-green-600 font-bold mt-2">
                        Rp {{ number_format($product->product_price,0,',','.') }}
                    </p>
                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
