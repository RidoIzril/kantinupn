@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gray-100">
    @include('customer.sidebarcus')

    {{-- MAIN --}}
    <div class="flex-1 p-6">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800">Keranjang</h1>

            <a href="{{ route('customer.homecustomer') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg text-sm
                      hover:bg-gray-100 transition">
                ← Lanjut Belanja
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- CART LIST --}}
            <div class="{{ ((auth()->guard('customer')->check() && $cartItems->count() > 0) || (!auth()->guard('customer')->check() && session('cart') && count(session('cart')) > 0)) ? 'lg:col-span-8' : 'lg:col-span-12' }}">

                <div class="bg-white rounded-xl shadow">

                    <div class="px-6 py-4 border-b flex items-center gap-2">
                        <span class="text-lg">🛒</span>
                        <h2 class="font-semibold">Keranjang Produk</h2>
                    </div>

                    <div class="p-6">

                        @php $total = 0; @endphp

                        @if ((auth()->guard('customer')->check() && $cartItems->count() > 0) || (!auth()->guard('customer')->check() && session('cart') && count(session('cart')) > 0))

                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="border-b text-gray-500">
                                        <tr>
                                            <th class="text-left py-2">Produk</th>
                                            <th>Harga</th>
                                            <th>Jumlah</th>
                                            <th>Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y">

                                    {{-- LOGIN CUSTOMER --}}
                                    @if(auth()->guard('customer')->check())
                                        @foreach ($cartItems as $item)
                                            @php
                                                $subtotal = $item->price * $item->quantity;
                                                $total += $subtotal;
                                                $product = $item->product;
                                            @endphp

                                            <tr data-id="{{ $product->product_id }}">
                                                <td class="py-3">
                                                    <div class="flex items-center gap-3">
                                                        <img src="{{ asset('storage/'.$product->product_image) }}"
                                                             class="w-16 h-16 rounded object-cover">
                                                        <div>
                                                            <p class="font-medium">{{ $product->product_name }}</p>
                                                            <p class="text-xs text-gray-500">
                                                                {{ $product->category->category_name }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="text-center">
                                                    Rp {{ number_format($item->price,0,',','.') }}
                                                </td>

                                                <td class="text-center">
                                                    <input type="number"
                                                           value="{{ $item->quantity }}"
                                                           min="1"
                                                           class="update-cart w-16 text-center border rounded py-1">
                                                </td>

                                                <td class="text-center font-semibold">
                                                    Rp {{ number_format($subtotal,0,',','.') }}
                                                </td>

                                                <td class="text-center">
                                                    <button class="remove-from-cart text-red-500 hover:text-red-700">
                                                        🗑️
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach

                                    {{-- GUEST --}}
                                    @else
                                        @foreach(session('cart') as $id => $details)
                                            @php
                                                $subtotal = $details['price'] * $details['quantity'];
                                                $total += $subtotal;
                                            @endphp

                                            <tr data-id="{{ $id }}">
                                                <td class="py-3">
                                                    <div class="flex items-center gap-3">
                                                        <img src="{{ asset('storage/'.$details['image']) }}"
                                                             class="w-16 h-16 rounded object-cover">
                                                        <div>
                                                            <p class="font-medium">{{ $details['product_name'] }}</p>
                                                            <p class="text-xs text-gray-500">{{ $details['category'] }}</p>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="text-center">
                                                    Rp {{ number_format($details['price'],0,',','.') }}
                                                </td>

                                                <td class="text-center">
                                                    <input type="number"
                                                           value="{{ $details['quantity'] }}"
                                                           min="1"
                                                           class="update-cart w-16 text-center border rounded py-1">
                                                </td>

                                                <td class="text-center font-semibold">
                                                    Rp {{ number_format($subtotal,0,',','.') }}
                                                </td>

                                                <td class="text-center">
                                                    <button class="remove-from-cart text-red-500 hover:text-red-700">
                                                        🗑️
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>

                                    <tfoot>
                                        <tr class="border-t">
                                            <td colspan="3" class="text-right font-semibold py-3">
                                                Total
                                            </td>
                                            <td colspan="2" class="font-bold text-green-700">
                                                Rp {{ number_format($total,0,',','.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                        @else
                            <div class="text-center py-12">
                                <p class="text-4xl">🛍️</p>
                                <p class="mt-3 text-gray-500">Keranjang belanja kosong</p>
                                <a href="{{ route('customer.homecustomer') }}"
                                   class="inline-block mt-4 px-6 py-2 bg-green-700 text-white rounded-lg">
                                    Belanja Sekarang
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- CHECKOUT --}}
            @if($total > 0)
            <div class="lg:col-span-4">
                <div class="bg-white rounded-xl shadow p-6">

                    <h3 class="font-semibold mb-4">Checkout</h3>

                    {{-- PAYMENT --}}
                    <div class="space-y-3">
                        @foreach($paymentMethods as $payment)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio"
                                   name="payment_method"
                                   value="{{ $payment->payment_id }}"
                                   {{ $loop->first ? 'checked' : '' }}>
                            <img src="{{ asset('storage/'.$payment->payment_image) }}"
                                 class="w-8 h-8 object-contain">
                            <span>Bank {{ $payment->payment_name }}</span>
                        </label>
                        @endforeach
                    </div>

                    <hr class="my-4">

                    <div class="flex justify-between font-bold">
                        <span>Total</span>
                        <span>Rp {{ number_format($total,0,',','.') }}</span>
                    </div>

                    <form id="checkout-form" action="{{ route('cart.checkout') }}" method="POST">
                        @csrf
                        <input type="hidden" name="payment_method" id="selected-payment-method">

                        <button type="submit"
                                class="w-full mt-4 bg-green-700 hover:bg-green-800
                                       text-white py-2 rounded-lg transition">
                            Checkout Sekarang
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
