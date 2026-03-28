@extends('layouts.app')

@section('content')

<div class="flex min-h-screen bg-gray-100">


@include('customer.sidebarcus')

<div class="flex-1 p-6">

    {{-- NOTIFIKASI --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif


    {{-- HEADER TENANT --}}
    <div class="bg-white rounded-xl shadow p-6 mb-6 flex gap-4">

        <img
            src="{{ $penjual->foto_tenant ? asset('storage/'.$penjual->foto_tenant) : asset('images/default-store.png') }}"
            class="w-24 h-24 rounded-xl object-cover"
        >

        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                {{ $penjual->penjual_tenantname }}
            </h1>

            <p class="text-sm text-gray-500">
                {{ $penjual->penjual_fullname }}
            </p>
        </div>

    </div>


    {{-- LIST MENU --}}
    <h2 class="text-lg font-semibold mb-4">
        Menu
    </h2>


    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">

        @foreach($penjual->products as $product)

            <div class="bg-white rounded-xl shadow p-4 flex flex-col">

                <img
                    src="{{ asset('storage/'.$product->product_image) }}"
                    class="h-36 w-full object-cover rounded-lg mb-3"
                >

                <h3 class="font-semibold text-sm">
                    {{ $product->product_name }}
                </h3>

                <p class="text-xs text-gray-500">
                    {{ $product->category->category_name }}
                </p>

                <p class="text-green-600 font-bold mt-2">
                    Rp {{ number_format($product->product_price,0,',','.') }}
                </p>

                <button
                    onclick='openMenuModal(
                        {{ $product->product_id }},
                        "{{ $product->product_name }}",
                        {{ $product->product_price }},
                        "{{ asset("storage/".$product->product_image) }}",
                        @json($product->variants)
                    )'
                    class="w-full mt-3 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg"
                >
                    + Tambah ke Keranjang
                </button>

            </div>

        @endforeach

    </div>

</div>


</div>

{{-- MODAL --}}

<div id="menuModal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden items-end justify-center z-50">


<div class="bg-white w-full max-w-md rounded-t-2xl p-6">

    <div class="flex justify-between items-center mb-4">

        <h2 class="text-lg font-bold">
            Tambahkan Menu
        </h2>

        <button onclick="closeModal()" class="text-gray-500 text-xl">
            ✕
        </button>

    </div>


    {{-- INFO PRODUK --}}
    <div class="flex gap-3 mb-4">

        <img id="modalImage"
             class="w-20 h-20 rounded object-cover">

        <div>
            <p id="modalName" class="font-semibold"></p>

            <p id="modalBasePrice"
               class="text-green-600 font-bold"></p>
        </div>

    </div>


    <form method="POST" action="{{ route('cart.add') }}">
        @csrf

        <input type="hidden" name="product_id" id="modalProductId">
        <input type="hidden" name="qty" id="modalQty" value="1">
        <input type="hidden" name="penjual_id" value="{{ $penjual->penjual_id }}">


        {{-- VARIANT --}}
        <div class="mb-4">

            <p class="font-semibold mb-2">
                Topping / Variant (Optional)
            </p>

            <div id="variantContainer" class="space-y-2"></div>

        </div>


        {{-- QTY --}}
        <div class="flex items-center gap-3 mb-4">

            <button type="button"
                    onclick="decreaseQty()"
                    class="px-3 py-1 border rounded">
                -
            </button>

            <input id="qty"
                   type="number"
                   value="1"
                   min="1"
                   class="w-14 text-center border rounded">

            <button type="button"
                    onclick="increaseQty()"
                    class="px-3 py-1 border rounded">
                +
            </button>

        </div>


        {{-- TOTAL --}}
        <div class="border-t pt-3 mb-4">

            <p class="text-sm text-gray-500">
                Total Harga
            </p>

            <p id="totalPrice"
               class="text-xl font-bold text-green-600">
                Rp 0
            </p>

        </div>


        <button type="submit"
                class="w-full bg-red-500 hover:bg-red-600 text-white py-3 rounded-lg">

            Masukkan ke Keranjang

        </button>

    </form>

</div>


</div>

<script>

let basePrice = 0;

function openMenuModal(id,name,price,image,variants)
{
    const modal = document.getElementById('menuModal');

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    basePrice = price;

    document.getElementById('modalProductId').value = id;

    document.getElementById('modalName').innerText = name;

    document.getElementById('modalBasePrice').innerText =
        "Rp " + new Intl.NumberFormat('id-ID').format(price);

    document.getElementById('modalImage').src = image;

    document.getElementById('qty').value = 1;

    renderVariants(variants);

    calculateTotal();
}


function renderVariants(variants)
{
    let container = document.getElementById("variantContainer");

    container.innerHTML = "";

    variants.forEach(v => {

        container.innerHTML += `
        <label class="flex justify-between border-b py-2">

            <span>${v.variant_name}</span>

            <span class="text-sm text-gray-500">
                + Rp ${new Intl.NumberFormat('id-ID').format(v.variant_price)}
            </span>

            <input 
                type="checkbox"
                class="variantCheckbox"
                value="${v.variant_price}"
                onchange="calculateTotal()"
            >

        </label>
        `;

    });
}


function increaseQty()
{
    let qty = document.getElementById('qty');

    qty.value = parseInt(qty.value) + 1;

    document.getElementById('modalQty').value = qty.value;

    calculateTotal();
}


function decreaseQty()
{
    let qty = document.getElementById('qty');

    if(qty.value > 1)
    {
        qty.value = parseInt(qty.value) - 1;
    }

    document.getElementById('modalQty').value = qty.value;

    calculateTotal();
}


function calculateTotal()
{
    let qty = document.getElementById("qty").value;

    let variantTotal = 0;

    document.querySelectorAll(".variantCheckbox:checked").forEach(v => {

        variantTotal += parseInt(v.value);

    });

    let total = (basePrice + variantTotal) * qty;

    document.getElementById("totalPrice").innerText =
        "Rp " + new Intl.NumberFormat('id-ID').format(total);
}


function closeModal()
{
    const modal = document.getElementById('menuModal');

    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

</script>

@endsection
