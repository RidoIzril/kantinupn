@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-100">

    {{-- Sidebar --}}
    @include('penjual.sidebarpenjual')

    {{-- Main --}}
    <div class="flex-1 p-6 md:p-10">

        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Edit Menu</h1>
            <p class="text-sm text-slate-500 mt-1">
                Perbarui informasi menu kantin
            </p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 max-w-5xl">

            {{-- Card Header --}}
            <div class="px-8 py-5 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-700">
                    Informasi Menu
                </h2>
            </div>

            {{-- Form --}}
            <form action="{{ route('produk.update', $product->product_id) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="px-8 py-6 space-y-6">
                @csrf
                @method('PUT')

                {{-- Nama Menu --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Nama Menu
                    </label>
                    <input type="text"
                           name="product_name"
                           value="{{ old('product_name', $product->product_name) }}"
                           required
                           class="w-full h-12 px-4 rounded-xl
                                  border border-slate-300
                                  bg-slate-50 text-slate-800
                                  placeholder-slate-400
                                  focus:outline-none focus:ring-0 focus:border-green-600">
                </div>

                {{-- Kategori --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Kategori Menu
                    </label>
                    <select name="category_id"
                            required
                            class="w-full h-12 px-4 rounded-xl
                                   border border-slate-300
                                   bg-slate-50 text-slate-800
                                   focus:outline-none focus:ring-0 focus:border-green-600">
                        @foreach ($categories as $item)
                            <option value="{{ $item->category_id }}"
                                {{ $item->category_id == $product->category_id ? 'selected' : '' }}>
                                {{ $item->category_code }} — {{ $item->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Harga & Stok --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Harga Menu
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 text-sm">
                                Rp
                            </span>
                            <input type="number"
                                   name="product_price"
                                   value="{{ old('product_price', $product->product_price) }}"
                                   required
                                   class="w-full h-12 pl-10 pr-4 rounded-xl
                                          border border-slate-300
                                          bg-slate-50 text-slate-800
                                          focus:outline-none focus:ring-0 focus:border-green-600">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Stok
                        </label>
                        <input type="number"
                               name="product_stock"
                               value="{{ old('product_stock', $product->product_stock) }}"
                               required
                               class="w-full h-12 px-4 rounded-xl
                                      border border-slate-300
                                      bg-slate-50 text-slate-800
                                      focus:outline-none focus:ring-0 focus:border-green-600">
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Deskripsi Menu
                    </label>
                    <textarea name="product_description"
                              rows="4"
                              required
                              class="w-full px-4 py-3 rounded-xl
                                     border border-slate-300
                                     bg-slate-50 text-slate-800
                                     focus:outline-none focus:ring-0 focus:border-green-600">{{ old('product_description', $product->product_description) }}</textarea>
                </div>

                {{-- Gambar --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Gambar Menu
                    </label>

                    <div class="flex items-center gap-4">
                        <label class="cursor-pointer inline-flex items-center gap-2
                                      px-4 py-2 rounded-xl
                                      bg-green-600 text-white text-sm font-medium
                                      hover:bg-green-700 transition">
                            <i class="bi bi-upload"></i>
                            Ganti Gambar
                            <input type="file"
                                   name="product_image"
                                   accept="image/*"
                                   class="hidden">
                        </label>
                        <span class="text-sm text-slate-400">
                            JPG / PNG, max 2MB
                        </span>
                    </div>

                    @if ($product->product_image)
                        <div class="mt-4">
                            <img src="{{ asset('storage/' . $product->product_image) }}"
                                 id="previewImage"
                                 class="w-40 h-40 object-cover rounded-xl border border-slate-200">
                        </div>
                    @endif
                </div>

                {{-- Action --}}
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-200">
                    <a href="{{ route('produk.list_produk') }}"
                       class="px-5 py-2.5 rounded-xl
                              bg-slate-200 text-slate-700
                              hover:bg-slate-300 transition
                              text-sm font-medium">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl
                                   bg-green-600 text-white
                                   hover:bg-green-700 transition
                                   text-sm font-semibold">
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- Preview Image --}}
<script>
document.querySelector('input[name="product_image"]').addEventListener('change', function (e) {
    const preview = document.getElementById('previewImage')
    const file = e.target.files[0]

    if (file) {
        const reader = new FileReader()
        reader.onload = function (event) {
            preview.src = event.target.result
        }
        reader.readAsDataURL(file)
    }
})
</script>
@endsection
