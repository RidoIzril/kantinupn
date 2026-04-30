@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gray-100">
    {{-- sidebar sudah dari layouts.app, jangan include lagi di sini --}}
    <div class="flex-1 p-6">
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                {{ $errors->first() }}
            </div>
        @endif

            <div class="bg-white rounded-xl shadow p-5 mb-6 mt-6 md:mt-8 flex items-center gap-4">

    <!-- FOTO (FIX GA KEPOTONG + TENGAH) -->
        <div class="w-24 h-24 flex items-center justify-center bg-gray-100 rounded-xl overflow-hidden">
            <img
                src="{{ !empty($penjual->tenant?->foto_tenant) ? asset('storage/'.$penjual->tenant->foto_tenant) : asset('images/default-store.png') }}"
                class="max-w-full max-h-full object-contain"
                alt="Foto Tenant"
            >
        </div>

        <!-- CONTENT -->
        <div class="flex-1">

            <h1 class="text-2xl font-bold text-gray-800">
                {{ $penjual->tenant?->tenant_name ?? 'Tenant' }}
            </h1>

            <!-- CHAT -->
          <a href="#"
   onclick="goToChat('{{ $penjual->users_id }}')"
   class="mt-2 inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 rounded-md">
    <i class="bi bi-chat-dots text-sm"></i>
    Chat
</a>

            <!-- INFO -->
            <div class="flex flex-wrap gap-2 mt-2 text-xs text-gray-600">
                <span class="bg-gray-100 px-2 py-0.5 rounded">
                    No Tenant: {{ $penjual->tenant?->no_tenant ?? '-' }}
                </span>

                <span class="bg-gray-100 px-2 py-0.5 rounded">
                    Kantin:
                    {{
                        $penjual->tenant?->kantin == '1' ? 'Kantin 1' : (
                            $penjual->tenant?->kantin == '2' ? 'Kantin 2' : '-'
                        )
                    }}
                </span>
            </div>

            <!-- DESKRIPSI -->
            <p class="text-xs -500 mt-2">
                {{ $penjual->tenant?->desk_tenant ?? '-' }}
            </p>

        </div>

    </div>

        <h2 class="text-lg font-semibold mb-4">Menu</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            @forelse(($penjual->tenant->produks ?? collect()) as $product)
                @php 
                    $stok = $product->stok ?? 0;
                    $isStockHabis = $stok <= 0;
                @endphp
                @php $isStockHabis = ($product->stok ?? 0) <= 0; @endphp

                {{-- ✅ PERUBAHAN DI SINI (CARD JADI ABU-ABU) --}}
                <div class="rounded-xl shadow p-4 flex flex-col 
                    {{ $isStockHabis ? 'bg-gray-100 opacity-70' : 'bg-white' }}">

                    <div class="relative h-36 w-full mb-3">
                        <img
                            src="{{ !empty($product->foto_produk) ? asset('storage/'.$product->foto_produk) : asset('images/default-product.png') }}"
                            class="h-36 w-full object-cover rounded-lg 
                                {{ $isStockHabis ? 'grayscale' : '' }}"
                            alt="{{ $product->nama }}"
                        >
                        @if($isStockHabis)
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center rounded-lg">
                                <span class="text-white font-bold text-sm">Stok Habis</span>
                            </div>
                        @endif
                    </div>

                    <h3 class="font-semibold text-sm">{{ $product->nama }}</h3>
                    <p class="text-xs text-gray-500">{{ $product->kategoris->nama_kategori ?? '-' }}</p>

                    <div class="flex items-center justify-between mt-2">
                        <p class="text-green-600 font-bold">
                            Rp {{ number_format($product->harga, 0, ',', '.') }}
                        </p>

                        @if($stok <= 0)
                            <span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded whitespace-nowrap">
                                Habis
                            </span>
                        @elseif($stok <= 5)
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded whitespace-nowrap">
                                Stok {{ $stok }}
                            </span>
                        @else
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded whitespace-nowrap">
                                Stok {{ $stok }}
                            </span>
                        @endif
                    </div>

                    @if($isStockHabis)
                        <button
                            type="button"
                            disabled
                            class="w-full mt-3 bg-gray-300 text-gray-400 cursor-not-allowed py-2 rounded-lg font-semibold"
                        >
                            Stok Habis
                        </button>
                    @else
                        <button
                        type="button"
                        onclick='openMenuModal(
                                {{ $product->id }},
                                @json($product->nama),
                                {{ (int) $product->harga }},
                                @json(!empty($product->foto_produk) ? asset("storage/".$product->foto_produk) : asset("images/default-product.png")),
                                @json($product->variants->where("status_variant", 1)->values() ?? []),
                                {{ (int) $product->stok ?? 0 }}
                            )'
                            class="w-full mt-3 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg"
                        >
                            + Tambah ke Keranjang
                        </button>
                    @endif
                </div>

            @empty
                <div class="col-span-full text-center text-slate-500 bg-white p-6 rounded-xl shadow">
                    Belum ada menu tersedia.
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ⚠️ SEMUA BAGIAN MODAL & JS TIDAK DIUBAH --}}

{{-- MODAL DAN JS DIBAWAH DITAMBAH VALIDASI STOCK --}}
<div id="menuModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-end justify-center z-50">
    <div class="bg-white w-full max-w-md rounded-t-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold">Tambahkan Menu</h2>
            <button type="button" onclick="closeModal()" class="text-gray-500 text-xl">✕</button>
        </div>

        <div class="flex gap-3 mb-4 items-center justify-between">
            <div class="flex items-center gap-3">
                <img id="modalImage" class="w-20 h-20 rounded object-cover" alt="Produk">
                <div>
                    <p id="modalName" class="font-semibold"></p>
                    <p id="modalBasePrice" class="text-green-600 font-bold"></p>
                </div>
            </div>
            <div class="flex items-center">
                <button
                    type="button"
                    onclick="decreaseQty()"
                    id="qtyMinusBtn"
                    class="w-8 h-8 border border-gray-300 rounded text-lg font-bold text-gray-700 flex items-center justify-center 
                        hover:border-[#ee4d2d] hover:bg-[#fff1ee] focus:outline-none disabled:border-gray-200 disabled:text-gray-300 disabled:bg-gray-100 transition"
                    aria-label="Kurangi jumlah"
                >-</button>
                <input
                    id="qty"
                    type="number"
                    value="1"
                    min="1"
                    class="mx-1 w-10 h-8 text-center border-0 rounded bg-transparent focus:outline-[#ee4d2d] font-semibold"
                    style="appearance: textfield"
                    readonly
                >
                <button
                    type="button"
                    onclick="increaseQty()"
                    id="qtyPlusBtn"
                    class="w-8 h-8 border border-gray-300 rounded text-lg font-bold text-gray-700 flex items-center justify-center 
                        hover:border-[#ee4d2d] hover:bg-[#fff1ee] focus:outline-none disabled:border-gray-200 disabled:text-gray-300 disabled:bg-gray-100 transition"
                    aria-label="Tambah jumlah"
                >+</button>
            </div>
        </div>
        
        <form id="addToCartForm" method="POST" action="{{ route('cart.add') }}">
            @csrf
            {{-- dipertahankan untuk kompatibilitas backend --}}
            <input type="hidden" name="token" id="modalToken" value="{{ request('token') }}">
            <input type="hidden" name="product_id" id="modalProductId">
            <input type="hidden" name="qty" id="modalQty" value="1">

            <div class="mb-4">
                <p class="font-semibold mb-2">Topping / Variant (Optional)</p>
                <div id="variantContainer" class="space-y-2"></div>
            </div>

            <div class="mb-4">
                <label for="modalCatatanMenu" class="block font-semibold mb-1">Catatan Menu (Opsional)</label>
                <textarea name="catatan_menu" id="modalCatatanMenu"
                        class="w-full border rounded px-3 py-2 text-sm"
                        placeholder="Catatan khusus untuk menu ini (boleh dikosongkan)"></textarea>
            </div>

            <div class="border-t pt-3 mb-4">
                <div class="flex flex-col items-end">
                    <p class="text-sm text-gray-500">Total Harga</p>
                    <p id="totalPrice" class="text-xl font-bold text-green-600">Rp 0</p>
                </div>
            </div>

            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-3 rounded-lg">
                Masukkan ke Keranjang
            </button>
        </form>
    </div>
</div>
<script>
let basePrice = 0;
let maxStock = 0; // tambahkan limit stock

// fallback token dari localStorage (untuk user customer yang login via localStorage)
(function setTokenFallback() {
    const hidden = document.getElementById('modalToken');
    if (hidden && !hidden.value) hidden.value = localStorage.getItem('token') || '';
})();

function openMenuModal(id, name, price, image, variants, stok) {
    const modal = document.getElementById('menuModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    basePrice = Number(price) || 0;
    maxStock  = parseInt(stok || 0); // set maxStock dari argumen

    document.getElementById('modalProductId').value = id;
    document.getElementById('modalName').innerText = name;
    document.getElementById('modalBasePrice').innerText =
        "Rp " + new Intl.NumberFormat('id-ID').format(basePrice);

    document.getElementById('modalImage').src = image;
    document.getElementById('qty').value = 1;
    document.getElementById('modalQty').value = 1;

    renderVariants(Array.isArray(variants) ? variants : []);
    calculateTotal();
}

function renderVariants(variants) {
    const container = document.getElementById('variantContainer');
    container.innerHTML = "";

    if (!variants.length) {
        container.innerHTML = `<p class="text-sm text-gray-500">Tidak ada variant.</p>`;
        return;
    }

    variants.forEach(v => {
        const variantId    = v.id ?? '';
        const variantName  = v.nama_variant ?? v.variant_name ?? 'Variant';
        const variantPrice = Number(v.harga_variant ?? v.variant_price ?? 0);

        container.innerHTML += `
            <label class="flex justify-between items-center border-b py-2 gap-2">
                <div>
                    <p>${variantName}</p>
                    <p class="text-xs text-gray-500">+ Rp ${new Intl.NumberFormat('id-ID').format(variantPrice)}</p>
                </div>
                <input type="checkbox"
                       name="variant_ids[]"
                       value="${variantId}"
                       data-price="${variantPrice}"
                       class="variantCheckbox"
                       onchange="calculateTotal()">
            </label>
        `;
    });
}

function increaseQty() {
    const qty = document.getElementById('qty');
    let nextQty = parseInt(qty.value || 1) + 1;

    if (maxStock > 0 && nextQty > maxStock) {
        alert('Jumlah pesanan melebihi stok tersedia!');
        return;
    }

    qty.value = nextQty;
    document.getElementById('modalQty').value = qty.value;
    calculateTotal();
}

function decreaseQty() {
    const qty = document.getElementById('qty');
    qty.value = Math.max(1, parseInt(qty.value || 1) - 1);
    document.getElementById('modalQty').value = qty.value;
    calculateTotal();
}

function calculateTotal() {
    const qty = parseInt(document.getElementById("qty").value || 1);
    let variantTotal = 0;
    document.querySelectorAll(".variantCheckbox:checked").forEach(el => {
        variantTotal += parseInt(el.dataset.price || 0);
    });
    const total = (basePrice + variantTotal) * qty;

    document.getElementById("totalPrice").innerText =
        "Rp " + new Intl.NumberFormat('id-ID').format(total);
}

function closeModal() {
    const modal = document.getElementById('menuModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}
function goToChat(penjualId) {
    const token = localStorage.getItem('token');
    const role  = localStorage.getItem('role');

    if (!token || role !== 'customer') {
        window.location.href = '/login';
        return;
    }

    window.location.href = `/customer/customer/chat/${penjualId}?token=${token}`;
}

(function guardAddToCartSubmit() {
    const form = document.getElementById('addToCartForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const token = localStorage.getItem('token');
        const role  = localStorage.getItem('role');
        const isCustomer = !!token && role === 'customer';

        // Cek stok sebelum submit
        const qty = parseInt(document.getElementById('qty').value || "1");
        if (maxStock > 0 && qty > maxStock) {
            e.preventDefault();
            alert('Jumlah pesanan melebihi stok tersedia!');
            return;
        }

        if (!isCustomer) {
            e.preventDefault();
            window.location.href = '/login';
            return;
        }

        // pastikan hidden token terisi untuk flow backend lama
        const hidden = document.getElementById('modalToken');
        if (hidden && !hidden.value) hidden.value = token;
    });
})();
</script>
@endsection