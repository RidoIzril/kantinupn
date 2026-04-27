@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-100">
    @include('penjual.sidebarpenjual')

    <div class="flex-1 p-3 sm:p-6 md:p-10 pt-10">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Tambah Menu</h1>
            <p class="text-sm text-slate-500 mt-1">
                Lengkapi daftar menu yang akan ditampilkan di menu kantin
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 max-w-2xl mx-auto">
            <div class="px-4 sm:px-8 py-5 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-700">Informasi Menu</h2>
            </div>

            <form action="{{ route('produk.store', ['token' => request('token')]) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="px-4 sm:px-8 py-6 space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ request('token') }}">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Menu</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required
                           placeholder="Contoh: Nasi Goreng Spesial"
                           class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kategori Menu</label>
                    <select name="kategoris_id" required class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                        <option value="" disabled {{ old('kategoris_id') ? '' : 'selected' }}>Pilih kategori</option>
                        @foreach ($categories as $item)
                            <option value="{{ $item->id }}" {{ old('kategoris_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Harga Menu</label>
                        <input type="number" name="harga" value="{{ old('harga') }}" required class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Stok Awal</label>
                        <input type="number" name="stok" value="{{ old('stok') }}" required class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi Menu</label>
                    <textarea name="deskripsi" rows="4" required class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50">{{ old('deskripsi') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Variant Menu</label>
                    <div id="variantContainer" class="space-y-3"></div>
                    <button 
                        type="button" 
                        onclick="addVariant()" 
                        class="mt-3 px-4 py-2 bg-blue-500 text-white rounded-lg text-sm w-full sm:w-auto">
                        + Tambah Variant
                    </button>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Gambar Menu</label>
                    <input type="file" name="foto_produk" accept="image/*" id="foto_produk_input" class="block w-full text-sm">
                    <div class="mt-4 flex justify-center">
                        <img id="previewImage" class="hidden w-32 h-32 object-cover rounded-xl border border-slate-200">
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 pt-6 border-t border-slate-200 sm:flex-row sm:justify-end">
                    <a href="{{ route('produk.list_produk', ['token' => request('token')]) }}"
                       class="px-5 py-2.5 rounded-xl bg-slate-200 text-slate-700 text-center">Batal</a>

                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-green-600 text-white text-center">Simpan Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('foto_produk_input').addEventListener('change', function (e) {
    const preview = document.getElementById('previewImage');
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (event) => {
        preview.src = event.target.result;
        preview.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
});

function addVariant() {
    const container = document.getElementById('variantContainer');
    container.insertAdjacentHTML('beforeend', `
        <div class="grid grid-cols-1 md:grid-cols-12 gap-2 items-center variant-item">
            <input type="text" name="nama_variant[]" placeholder="Nama Variant"
                class="w-full h-10 px-3 border rounded-lg md:col-span-6 mb-2 md:mb-0">
            <input type="number" name="harga_variant[]" placeholder="Harga Tambahan"
                class="w-full h-10 px-3 border rounded-lg md:col-span-5 mb-2 md:mb-0">
            <button type="button" onclick="removeVariant(this)" 
                class="h-10 rounded-lg bg-red-500 text-white w-full md:w-auto md:col-span-1">X</button>
        </div>
    `);
}
function removeVariant(btn) { btn.closest('.variant-item').remove(); }
</script>
@endsection