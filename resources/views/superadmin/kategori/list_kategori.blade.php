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

                            <button type="submit"
                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 border border-red-200 text-red-700 text-xs font-semibold rounded hover:bg-red-600 hover:text-white hover:border-red-600 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M10 3h4a2 2 0 012 2v2H8V5a2 2 0 012-2z" />
                                </svg>
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