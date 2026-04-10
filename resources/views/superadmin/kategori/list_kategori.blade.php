@extends('layouts.app')
@section('title','Kategori')

@section('content')
<h1 class="text-xl font-bold mb-4">Kelola Kategori</h1>

{{-- ALERT SUCCESS --}}
@if(session('success'))
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

{{-- ALERT ERROR --}}
@if ($errors->any())
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- FORM TAMBAH --}}
<form method="POST"
      action="{{ route('superadmin.kategori.store') }}"
      class="flex gap-3 mb-6">
    @csrf
    <input name="nama_kategori"
           class="border px-3 py-2 rounded w-64"
           placeholder="Nama kategori"
           value="{{ old('nama_kategori') }}"
           required>

    <button type="submit" class="bg-green-600 text-white px-4 rounded">
        Tambah
    </button>
</form>

{{-- TABLE (dibuat lebih kecil & rapat) --}}
<div class="inline-block bg-white rounded shadow border">
    <table class="w-auto inline-table border-collapse">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 text-left whitespace-nowrap">Nama Kategori</th>
                <th class="px-4 py-2 text-left whitespace-nowrap">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $k)
                <tr class="border-t">
                    <td class="px-4 py-2 whitespace-nowrap">{{ $k->nama_kategori }}</td>
                    <td class="px-4 py-2 whitespace-nowrap">
                        <form method="POST"
                              action="{{ route('superadmin.kategori.destroy', $k->id) }}"
                              onsubmit="return confirm('Yakin hapus kategori ini?')"
                              class="inline">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="text-red-600 hover:underline">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="px-4 py-3 text-center text-gray-500">
                        Belum ada kategori
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection