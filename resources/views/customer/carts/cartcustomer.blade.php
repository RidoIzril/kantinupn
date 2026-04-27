@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-100 py-10">
    <div class="flex-1 px-2 md:px-6 lg:px-8">
        <div class="max-w-xl mx-auto">
            <h1 class="text-3xl font-extrabold text-slate-800 mb-8 text-center">Konfirmasi Pesanan</h1>
            @if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="m-0 px-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
            @if($cartItems->count())
                @php $total = 0; @endphp

                {{-- Loop produk: setiap card produk berdiri sendiri, PATCH/DELETE form di luarnya --}}
                @foreach($cartItems as $item)
                    @php
                        $product = $item->produk;
                        $price = $item->harga_per_item ?? 0;
                        $subtotal = $item->subtotal ?? ($price * ($item->jumlah ?? 0));
                        $total += $subtotal;
                    @endphp
                    <div class="bg-white rounded-2xl shadow border border-slate-200 p-4 mb-2 flex flex-col md:flex-row md:items-center gap-4">
                        <img src="{{ !empty($product?->foto_produk) ? asset('storage/' . $product->foto_produk) : 'https://via.placeholder.com/88x88?text=No+Image' }}"
                             class="w-20 h-20 rounded-xl border object-cover mx-auto md:mx-0" alt="Produk">
                        <div class="flex-1 w-full">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between md:gap-5">
                                <div>
                                    <div class="font-bold text-lg text-slate-800">{{ $product->nama ?? '-' }}</div>
                                    <div class="text-slate-500 mt-1">Rp {{ number_format($price, 0, ',', '.') }}</div>
                                </div>
                                @if($item->variant)
                                    <div class="text-slate-500 text-sm font-medium mt-1">
                                        {{ $item->variant->nama_variant ?? 'Varian' }}
                                    </div>
                                @endif
                                <div class="flex items-center gap-2 mt-3 md:mt-0">
                                    <form action="{{ route('cart.update', ['token' => request('token')]) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                        <input type="hidden" name="quantity" value="{{ max(1, ($item->jumlah ?? 1) - 1) }}">
                                        <button type="submit" class="w-8 h-8 rounded-lg border text-lg text-slate-600 bg-slate-50 hover:bg-slate-200 transition">-</button>
                                    </form>
                                    <span class="w-8 h-8 flex items-center justify-center text-slate-800 font-bold rounded bg-slate-50 border">{{ $item->jumlah ?? 0 }}</span>
                                    <form action="{{ route('cart.update', ['token' => request('token')]) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                        <input type="hidden" name="quantity" value="{{ ($item->jumlah ?? 0) + 1 }}">
                                        <button type="submit" class="w-8 h-8 rounded-lg border text-lg text-slate-600 bg-slate-50 hover:bg-slate-200 transition">+</button>
                                    </form>
                                    <form action="{{ route('cart.remove', ['token' => request('token')]) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                        <button type="submit" class="w-8 h-8 rounded-lg border text-slate-600 bg-slate-50 hover:bg-red-200 transition">🗑</button>
                                    </form>
                                </div>
                                <div class="md:text-right text-emerald-600 font-extrabold text-lg w-28 hidden md:block">
                                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="mt-3 md:mt-3 flex flex-col md:flex-row md:items-center md:gap-4">
                                <textarea
    name="catatan_menu[{{ $item->id }}]"
    rows="1"
    class="w-full md:w-3/4 border rounded-md px-3 py-2 text-sm bg-slate-50 focus:bg-white focus:outline-emerald-600 transition resize-none"
    form="checkoutform"
    placeholder="Catatan menu untuk {{ $item->produk->nama }}"
>{{ old('catatan_menu.' . $item->id, $item->catatan_menu ?? '') }}</textarea>
                                <div class="md:hidden text-right text-emerald-600 font-extrabold text-lg mt-1">
                                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                @php
                    // Cek: bolehkan delivery untuk seluruh tenant produk di cart
                    $deliveryAllowed = $cartItems->pluck('produk.tenant.status_delivery')->contains(1);
                @endphp
                {{-- FORM CHECKOUT (HANYA SATU, DI BAWAH) --}}
                <form action="{{ route('cart.checkout', ['token' => request('token')]) }}" method="POST" class="space-y-6 mt-5" id="checkoutform">
                    @csrf
                    @foreach($cartItems as $item)
                        {{-- pastikan hidden item id dikirim, perlu untuk relasi catatan menu --}}
                        <input type="hidden" name="produk_id[{{ $item->id }}]" value="{{ $item->produk->id ?? $item->id }}">
                    @endforeach
                    <div class="bg-white rounded-2xl shadow border p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <h2 class="font-extrabold text-slate-700 text-xl mb-3 md:mb-0">Ringkasan Belanja</h2>
                            <div class="text-2xl font-extrabold text-emerald-700 mb-3 md:mb-0">Rp {{ number_format($total,0,',','.') }}</div>
                        </div>
                        <div class="my-2"></div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Order Type</label>
                                <select name="order_type" id="order_type"
                                    class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:outline-emerald-500"
                                    required>
                                    <option value="" disabled {{ old('order_type', '') == '' ? 'selected' : '' }} hidden>Pilih</option>
                                    <option value="Dine In"  {{ old('order_type')=='Dine In'?'selected':'' }}>Dine In</option>
                                    <option value="Takeaway" {{ old('order_type')=='Takeaway'?'selected':'' }}>Takeaway</option>
                                    @if($deliveryAllowed)
                                        <option value="Delivery" {{ old('order_type')=='Delivery'?'selected':'' }}>Delivery</option>
                                    @endif
                                </select>
                            </div>
                            <div id="dinein-fields" class="{{ old('order_type') === 'Dine In' ? '' : 'hidden' }}">
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Nomor Meja <span class="text-xs text-orange-600">(wajib untuk Dine In)</span>
                                </label>
                                <input type="text" name="nomor_meja"
                                       value="{{ old('nomor_meja') }}"
                                       class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:outline-emerald-500"
                                       placeholder="Nomor Meja">
                            </div>
                            <div id="delivery-fields" class="space-y-4 {{ old('order_type')==='Delivery' ? '' : 'hidden' }}">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">
                                        Alamat Pengantaran <span class="text-xs text-orange-600">(wajib untuk Delivery)</span>
                                    </label>
                                    <textarea name="alamat" rows="2"
                                        class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:outline-emerald-500">{{ old('alamat') }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                                    <textarea name="catatan" rows="1"
                                        class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:outline-emerald-500">{{ old('catatan') }}</textarea>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Metode Pembayaran</label>
                                <select name="metode_pembayaran"
                                    class="w-full border rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:outline-emerald-500"
                                    required>
                                <option value="" disabled {{ old('metode_pembayaran', '') == '' ? 'selected' : '' }} hidden>Pilih</option>
                                <option value="cash" {{ old('metode_pembayaran')=='cash'?'selected':'' }}>Cash</option>
                                <option value="qris" {{ old('metode_pembayaran')=='qris'?'selected':'' }}>QRIS</option>
                            </select>
                            </div>
                        </div>
                        <button type="submit"
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl py-3 mt-7 text-lg transition">
                            Checkout Sekarang
                        </button>
                    </div>
                </form>
            @else
                <div class="bg-white rounded-2xl p-8 text-center text-slate-500 border border-slate-200">
                    Keranjang kosong.
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const orderType = document.getElementById('order_type');
    const deliveryFields = document.getElementById('delivery-fields');
    const dineinFields = document.getElementById('dinein-fields');

    function toggleFields() {
        if (orderType && deliveryFields && dineinFields) {
            if (orderType.value === 'Delivery') {
                deliveryFields.classList.remove('hidden');
                dineinFields.classList.add('hidden');
            } else if (orderType.value === 'Dine In') {
                deliveryFields.classList.add('hidden');
                dineinFields.classList.remove('hidden');
            } else {
                deliveryFields.classList.add('hidden');
                dineinFields.classList.add('hidden');
            }
        }
    }

    if (orderType) {
        orderType.addEventListener('change', toggleFields);
        toggleFields();
    }
});
</script>
@endsection