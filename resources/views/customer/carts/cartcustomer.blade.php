@extends('layouts.app')

@section('content')

<div class="flex min-h-screen bg-gray-100">

@include('customer.sidebarcus')

<div class="flex-1 p-6">

<h1 class="text-xl font-bold mb-6">
Konfirmasi Pesanan
</h1>
@php
$total = 0;
@endphp
@foreach($cartItems as $item)

@php

$product = $item->product;

$price = $product->product_price;

if($item->variant){
    $price += $item->variant->variant_price;
}

$subtotal = $price * $item->quantity;

$total += $subtotal;

@endphp


<div class="flex items-center justify-between border-b py-4">

<div class="flex items-center gap-4">

<img
src="{{ asset('storage/'.$product->product_image) }}"
class="w-16 h-16 rounded object-cover">

<div>

<p class="font-semibold">
{{ $product->product_name }}
</p>

@if($item->variant)
<p class="text-xs text-gray-500">
{{ $item->variant->variant_name }}
</p>
@endif

<p class="text-sm text-gray-500">
Rp {{ number_format($price,0,',','.') }}
</p>

</div>

</div>


<div class="flex items-center gap-2">

<form action="{{ route('cart.update') }}" method="POST">
@csrf
@method('PATCH')

<input type="hidden" name="cart_id" value="{{ $item->cart_id }}">
<input type="hidden" name="quantity" value="{{ $item->quantity - 1 }}">

<button class="px-3 py-1 border rounded">
-
</button>

</form>


<span class="w-6 text-center">
{{ $item->quantity }}
</span>


<form action="{{ route('cart.update') }}" method="POST">
@csrf
@method('PATCH')

<input type="hidden" name="cart_id" value="{{ $item->cart_id }}">
<input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">

<button class="px-3 py-1 border rounded">
+
</button>

</form>

</div>


<div class="font-semibold text-red-500">
Rp {{ number_format($subtotal,0,',','.') }}
</div>

</div>

@endforeach