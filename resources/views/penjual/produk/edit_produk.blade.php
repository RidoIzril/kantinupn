@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-100">
    @include('penjual.sidebarpenjual')

    <div class="flex-1 p-6 md:p-10">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Edit Menu</h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui informasi menu kantin</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 max-w-5xl">
            <div class="px-8 py-5 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-700">Informasi Menu</h2>
            </div>

            <form action="{{ route('produk.update', ['id' => $product->id, 'token' => request('token')]) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="px-8 py-6 space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="token" value="{{ request('token') }}">

                <div>
                    <label class="block text-sm font-medium mb-1">Nama Menu</label>
                    <input type="text" name="nama" value="{{ old('nama', $product->nama) }}" required
                           class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Kategori</label>
                    <select name="kategoris_id" required class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                        @foreach($categories as $item)
                            <option value="{{ $item->id }}" {{ (int)$item->id === (int)$product->kategoris_id ? 'selected' : '' }}>
                                {{ $item->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm mb-1">Harga</label>
                        <input type="number" name="harga" value="{{ old('harga', $product->harga) }}" required
                               class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Stok</label>
                        <input type="number" name="stok" value="{{ old('stok', $product->stok) }}" required
                               class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                    </div>
                </div>

                <div>
                    <label class="block text-sm mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="4" required
                              class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50">{{ old('deskripsi', $product->deskripsi) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-3">Variant Menu</label>

                    <div id="variantContainer" class="space-y-3">
                        {{-- Prioritas: old() saat validasi gagal --}}
                        @if(old('nama_variant'))
                            @foreach(old('nama_variant') as $i => $namaVar)
                                <div class="flex gap-3 variant-row">
                                    <input type="text" name="nama_variant[]" value="{{ $namaVar }}"
                                           placeholder="Nama variant" class="flex-1 h-11 px-3 border rounded">
                                    <input type="number" name="harga_variant[]" value="{{ old('harga_variant.'.$i) }}"
                                           placeholder="Harga tambahan" class="w-40 h-11 px-3 border rounded">
                                    <select name="status_variant[]" class="h-11 border rounded px-2">
                                        <option value="1" {{ $variant->status_variant ? 'selected' : '' }}>Aktif</option>
                                        <option value="0" {{ !$variant->status_variant ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                    <button type="button" onclick="removeVariant(this)" class="bg-red-500 text-white px-3 rounded">X</button>
                                </div>
                            @endforeach
                        @elseif($product->variants && $product->variants->count())
                            {{-- Ambil dari relasi variants --}}
                            @foreach($product->variants as $variant)
                                <div class="flex gap-3 variant-row">
                                    <input type="text" name="nama_variant[]" value="{{ $variant->nama_variant }}"
                                           placeholder="Nama variant" class="flex-1 h-11 px-3 border rounded">
                                    <input type="number" name="harga_variant[]" value="{{ $variant->harga_variant }}"
                                           placeholder="Harga tambahan" class="w-40 h-11 px-3 border rounded">
                                    <select name="status_variant[]" class="h-11 border rounded px-2">
                                        <option value="1" {{ $variant->status_variant ? 'selected' : '' }}>Aktif</option>
                                        <option value="0" {{ !$variant->status_variant ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                    <button type="button" onclick="removeVariant(this)" class="bg-red-500 text-white px-3 rounded">X</button>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <button type="button" onclick="addVariant()" class="mt-3 bg-blue-500 text-white px-4 py-2 rounded">
                        + Tambah Variant
                    </button>
                </div>

                <div>
                    <label class="block text-sm mb-2">Gambar Menu</label>
                    <input type="file" name="foto_produk" accept="image/*" id="foto_produk_input">

                    @if($product->foto_produk)
                        <div class="mt-3">
                            <img src="{{ asset('storage/' . $product->foto_produk) }}"
                                 id="previewImage" class="w-40 rounded border">
                        </div>
                    @else
                        <img id="previewImage" class="hidden w-40 rounded border mt-3">
                    @endif
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t">
                    <a href="{{ route('produk.list_produk', ['token' => request('token')]) }}"
                       class="px-5 py-2 bg-gray-200 rounded">
                        Batal
                    </a>

                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addVariant() {
    const container = document.getElementById('variantContainer');
    container.insertAdjacentHTML('beforeend', `
        <div class="flex gap-3 variant-row">
            <input type="text" name="nama_variant[]" placeholder="Nama variant" class="flex-1 h-11 px-3 border rounded">
            <input type="number" name="harga_variant[]" placeholder="Harga tambahan" class="w-40 h-11 px-3 border rounded">
            <button type="button" onclick="removeVariant(this)" class="bg-red-500 text-white px-3 rounded">X</button>
        </div>
    `);
}
function removeVariant(btn) {
    btn.closest('.variant-row').remove();
}

document.getElementById('foto_produk_input')?.addEventListener('change', function(e){
    const file = e.target.files[0];
    if(!file) return;
    const reader = new FileReader();
    reader.onload = function(ev){
        const img = document.getElementById('previewImage');
        img.src = ev.target.result;
        img.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
});
</script>
@endsection