@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-100">

    <div class="flex-1 p-4 md:p-6 lg:p-8">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-2xl font-bold text-slate-800 mb-5">Konfirmasi Pesanan</h1>

            @if(session('success'))
                <div class="mb-4 bg-emerald-100 text-emerald-700 px-4 py-3 rounded-xl text-sm border border-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-100 text-red-700 px-4 py-3 rounded-xl text-sm border border-red-200">
                    <ul class="list-disc ml-5">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- LEFT --}}
                <div class="lg:col-span-2 space-y-4">
                    @php $total = 0; @endphp

                    @forelse($cartItems as $item)
                        @php
                            $product = $item->produk;
                            $price = $item->harga_per_item ?? 0;
                            $subtotal = $item->subtotal ?? ($price * ($item->jumlah ?? 0));
                            $total += $subtotal;
                        @endphp

                        <div class="bg-white rounded-2xl border border-slate-200 p-4 md:p-5 shadow-sm">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <img src="{{ !empty($product?->foto_produk) ? asset('storage/'.$product->foto_produk) : 'https://via.placeholder.com/88x88?text=No+Image' }}"
                                         class="w-20 h-20 md:w-24 md:h-24 rounded-xl object-cover border border-slate-200"
                                         alt="Produk">

                                    <div>
                                        <p class="font-semibold text-slate-800 text-lg leading-tight">
                                            {{ $product->nama ?? '-' }}
                                        </p>

                                        @if($item->variant)
                                            <p class="text-sm text-slate-500 mt-1">
                                                + {{ $item->variant->nama_variant ?? 'Variant' }}
                                            </p>
                                        @endif

                                        <p class="text-slate-500 mt-1">
                                            Rp {{ number_format($price,0,',','.') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 md:gap-4">
                                    <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden">
                                        <form action="{{ route('cart.update', ['token' => request('token')]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                            <input type="hidden" name="quantity" value="{{ max(1, ($item->jumlah ?? 1) - 1) }}">
                                            <button class="w-10 h-10 text-slate-600 hover:bg-slate-100 transition">−</button>
                                        </form>

                                        <span class="w-10 h-10 flex items-center justify-center text-slate-700 font-medium border-x border-slate-200">
                                            {{ $item->jumlah ?? 0 }}
                                        </span>

                                        <form action="{{ route('cart.update', ['token' => request('token')]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                            <input type="hidden" name="quantity" value="{{ ($item->jumlah ?? 0) + 1 }}">
                                            <button class="w-10 h-10 text-slate-600 hover:bg-slate-100 transition">+</button>
                                        </form>
                                    </div>

                                    <form action="{{ route('cart.remove', ['token' => request('token')]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                        <button class="px-3 h-10 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition">
                                            Hapus
                                        </button>
                                    </form>

                                    <div class="min-w-[120px] text-right font-semibold text-red-500 text-lg">
                                        Rp {{ number_format($subtotal,0,',','.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-2xl p-8 text-center text-slate-500 border border-slate-200">
                            Keranjang kosong.
                        </div>
                    @endforelse
                </div>

                {{-- RIGHT --}}
                <div class="lg:col-span-1">
                    @if($cartItems->count())
                        <div class="lg:sticky lg:top-6 space-y-4">
                            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                                <h2 class="font-semibold text-slate-800 mb-4">Ringkasan Belanja</h2>
                                <div class="flex items-center justify-between text-slate-600">
                                    <span>Total</span>
                                    <span class="text-2xl font-bold text-emerald-600">
                                        Rp {{ number_format($total,0,',','.') }}
                                    </span>
                                </div>
                            </div>

                            <form action="{{ route('cart.checkout', ['token' => request('token')]) }}"
                                  method="POST"
                                  class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                                @csrf

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Order Type</label>
                                    <select name="order_type" id="order_type"
                                            class="w-full border border-slate-300 rounded-lg px-3 py-2"
                                            required>
                                        <option value="">Pilih</option>
                                        <option value="Dine In" {{ old('order_type')=='Dine In'?'selected':'' }}>Dine In</option>
                                        <option value="Takeaway" {{ old('order_type')=='Takeaway'?'selected':'' }}>Takeaway</option>
                                        <option value="Delivery" {{ old('order_type')=='Delivery'?'selected':'' }}>Delivery</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Metode Pembayaran</label>
                                    <select name="metode_pembayaran" class="w-full border border-slate-300 rounded-lg px-3 py-2" required>
                                        <option value="">Pilih</option>
                                        <option value="cash" {{ old('metode_pembayaran')=='cash'?'selected':'' }}>Cash</option>
                                        <option value="qris" {{ old('metode_pembayaran')=='qris'?'selected':'' }}>QRIS</option>
                                    </select>
                                </div>

                                <div id="delivery-fields" class="space-y-4 {{ old('order_type')==='Delivery' ? '' : 'hidden' }}">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">
                                            Alamat Pengantaran (wajib untuk Delivery)
                                        </label>
                                        <textarea name="alamat" rows="3"
                                                  class="w-full border border-slate-300 rounded-lg px-3 py-2">{{ old('alamat') }}</textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                                        <textarea name="catatan" rows="2"
                                                  class="w-full border border-slate-300 rounded-lg px-3 py-2">{{ old('catatan') }}</textarea>
                                    </div>
                                </div>

                                <button type="submit"
                                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-xl transition">
                                    Checkout Sekarang
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const orderType = document.getElementById('order_type');
    const deliveryFields = document.getElementById('delivery-fields');

    function toggleDeliveryFields() {
        if (!orderType || !deliveryFields) return;
        if (orderType.value === 'Delivery') {
            deliveryFields.classList.remove('hidden');
        } else {
            deliveryFields.classList.add('hidden');
        }
    }

    if (orderType) {
        orderType.addEventListener('change', toggleDeliveryFields);
        toggleDeliveryFields();
    }
});
</script>
@endsection