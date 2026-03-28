@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-100">

    @include('penjual.sidebarpenjual')

    <div class="flex-1 p-6 md:p-10">

        {{-- HEADER --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">
                Edit Menu
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Perbarui informasi menu kantin
            </p>
        </div>


        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 max-w-5xl">

            <div class="px-8 py-5 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-700">
                    Informasi Menu
                </h2>
            </div>


            <form action="{{ route('produk.update',$product->product_id) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="px-8 py-6 space-y-6">

                @csrf
                @method('PUT')


                {{-- NAMA --}}
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Nama Menu
                    </label>

                    <input type="text"
                           name="product_name"
                           value="{{ old('product_name',$product->product_name) }}"
                           required
                           class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                </div>


                {{-- KATEGORI --}}
                <div>

                    <label class="block text-sm font-medium mb-1">
                        Kategori
                    </label>

                    <select name="category_id"
                            required
                            class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">

                        @foreach($categories as $item)

                        <option value="{{ $item->category_id }}"
                        {{ $item->category_id == $product->category_id ? 'selected':'' }}>

                        {{ $item->category_code }} — {{ $item->category_name }}

                        </option>

                        @endforeach

                    </select>

                </div>



                {{-- HARGA + STOK --}}
                <div class="grid grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm mb-1">
                            Harga
                        </label>

                        <input type="number"
                               name="product_price"
                               value="{{ old('product_price',$product->product_price) }}"
                               required
                               class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                    </div>

                    <div>
                        <label class="block text-sm mb-1">
                            Stok
                        </label>

                        <input type="number"
                               name="product_stock"
                               value="{{ old('product_stock',$product->product_stock) }}"
                               required
                               class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                    </div>

                </div>



                {{-- DESKRIPSI --}}
                <div>

                    <label class="block text-sm mb-1">
                        Deskripsi
                    </label>

                    <textarea name="product_description"
                              rows="4"
                              class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50"
                              required>{{ old('product_description',$product->product_description) }}</textarea>

                </div>



                {{-- VARIANT --}}
                <div>

                    <label class="block text-sm font-semibold mb-3">
                        Variant Menu
                    </label>

                    <div id="variantContainer" class="space-y-3">

                        @foreach($product->variants as $variant)

                        <div class="flex gap-3">

                            <input type="text"
                                   name="variant_name[]"
                                   value="{{ $variant->variant_name }}"
                                   placeholder="Nama variant"
                                   class="flex-1 h-11 px-3 border rounded">

                            <input type="number"
                                   name="variant_price[]"
                                   value="{{ $variant->variant_price }}"
                                   placeholder="Harga tambahan"
                                   class="w-40 h-11 px-3 border rounded">

                            <button type="button"
                                    onclick="removeVariant(this)"
                                    class="bg-red-500 text-white px-3 rounded">

                                X

                            </button>

                        </div>

                        @endforeach

                    </div>


                    <button type="button"
                            onclick="addVariant()"
                            class="mt-3 bg-blue-500 text-white px-4 py-2 rounded">

                        + Tambah Variant

                    </button>

                </div>



                {{-- GAMBAR --}}
                <div>

                    <label class="block text-sm mb-2">
                        Gambar Menu
                    </label>

                    <input type="file"
                           name="product_image"
                           accept="image/*">

                    @if($product->product_image)

                    <div class="mt-3">

                        <img src="{{ asset('storage/'.$product->product_image) }}"
                             id="previewImage"
                             class="w-40 rounded">

                    </div>

                    @endif

                </div>



                {{-- BUTTON --}}
                <div class="flex justify-end gap-3 pt-6 border-t">

                    <a href="{{ route('produk.list_produk') }}"
                       class="px-5 py-2 bg-gray-200 rounded">

                        Batal

                    </a>

                    <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white rounded">

                        Simpan Perubahan

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



<script>

function addVariant()
{

    const container = document.getElementById('variantContainer');

    const html = `
    <div class="flex gap-3">

        <input type="text"
               name="variant_name[]"
               placeholder="Nama variant"
               class="flex-1 h-11 px-3 border rounded">

        <input type="number"
               name="variant_price[]"
               placeholder="Harga tambahan"
               class="w-40 h-11 px-3 border rounded">

        <button type="button"
                onclick="removeVariant(this)"
                class="bg-red-500 text-white px-3 rounded">

            X

        </button>

    </div>
    `;

    container.insertAdjacentHTML('beforeend',html);

}



function removeVariant(btn)
{
    btn.parentElement.remove();
}

</script>

@endsection