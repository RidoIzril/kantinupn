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

 <input name="category_code"
        class="border px-3 py-2 rounded w-32"
        placeholder="Kode"
        maxlength="5"
        required>

 <input name="category_name"
        class="border px-3 py-2 rounded w-64"
        placeholder="Nama kategori"
        required>

 <button class="bg-green-600 text-white px-4 rounded">
     Tambah
 </button>
</form>

{{-- TABLE --}}
<table class="w-full bg-white rounded shadow">
 <thead class="bg-gray-100">
  <tr>
   <th class="p-3 text-left">Kode</th>
   <th class="p-3 text-left">Nama</th>
   <th class="p-3 text-right">Aksi</th>
  </tr>
 </thead>
 <tbody>
 @foreach($categories as $k)
 <tr class="border-b">
  <td class="p-3">{{ $k->category_code }}</td>
  <td class="p-3">{{ $k->category_name }}</td>
  <td class="p-3 text-right">
   <form method="POST"
      action="{{ route('superadmin.kategori.destroy', $k->category_id) }}"
      onsubmit="return confirm('Yakin hapus kategori ini?')">
    @csrf
    @method('DELETE')

    <button class="text-red-600 hover:underline">
        Hapus
    </button>
</form>

  </td>
 </tr>
 @endforeach

 @if($categories->isEmpty())
 <tr>
  <td colspan="3" class="p-4 text-center text-gray-500">
      Belum ada kategori
  </td>
 </tr>
 @endif
 </tbody>
</table>
@endsection
