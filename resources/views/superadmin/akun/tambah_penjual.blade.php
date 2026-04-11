@extends('layouts.app')
@section('title','Tambah Penjual')

@section('content')
@include('superadmin.sidebarsuperadmin')

<h1 class="text-xl font-bold mb-6">Tambah Akun Penjual</h1>

@if ($errors->any())
<div class="bg-red-100 text-red-700 p-3 rounded mb-4">
    <ul class="list-disc ml-5">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST"
      action="{{ route('superadmin.penjual.store') }}"
      enctype="multipart/form-data"
      class="space-y-4 max-w-xl">
    @csrf

    {{-- USERNAME --}}
    <input name="username"
           value="{{ old('username') }}"
           class="w-full border p-2 rounded"
           placeholder="Username"
           required>

    {{-- PASSWORD --}}
    <input type="password"
           name="password"
           class="w-full border p-2 rounded"
           placeholder="Password"
           required>

    {{-- NAMA LENGKAP --}}
    <input name="nama_lengkap"
           value="{{ old('nama_lengkap') }}"
           class="w-full border p-2 rounded"
           placeholder="Nama Lengkap"
           required>

    {{-- KONTAK --}}
    <input name="kontak"
           value="{{ old('kontak') }}"
           class="w-full border p-2 rounded"
           placeholder="No HP"
           required>

    {{-- GENDER --}}
    <select name="gender" class="w-full border p-2 rounded" required>
        <option value="">-- Pilih Gender --</option>
        <option value="Laki-Laki" {{ old('gender') == 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
        <option value="Perempuan" {{ old('gender') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
    </select>

    {{-- STATUS --}}
    <select name="status" class="w-full border p-2 rounded" required>
        <option value="aktif" {{ old('status', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
        <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
    </select>

    {{-- NAMA TENANT --}}
    <input name="tenant_name"
           value="{{ old('tenant_name') }}"
           class="w-full border p-2 rounded"
           placeholder="Nama Tenant">

    {{-- NO TENANT --}}
    <input name="no_tenant"
           value="{{ old('no_tenant') }}"
           class="w-full border p-2 rounded"
           placeholder="No Tenant">

    {{-- FOTO TENANT --}}
    <div>
        <label class="block text-sm font-medium mb-1">Foto Tenant</label>
        <input type="file" name="foto_tenant" class="w-full border p-2 rounded">
        <p class="text-xs text-gray-500 mt-1">Upload jpg/png max 2MB</p>
    </div>

    <div class="flex gap-3 pt-2">
        <button class="bg-green-600 text-white px-4 py-2 rounded">Simpan</button>
        <a href="{{ route('superadmin.penjual.index') }}"
           class="bg-gray-300 px-4 py-2 rounded">Kembali</a>
    </div>
</form>
@endsection