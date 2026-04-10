@extends('layouts.app')
@section('title','Edit Penjual')

@section('content')
<h1 class="text-xl font-bold mb-6">Edit Akun Penjual</h1>

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
      action="{{ route('superadmin.penjual.update', $penjual->id) }}"
      enctype="multipart/form-data"
      class="space-y-4 max-w-xl">
    @csrf
    @method('PUT')

    {{-- USERNAME --}}
    <input name="username"
           value="{{ old('username', $penjual->username) }}"
           class="w-full border p-2 rounded"
           placeholder="Username"
           required>

    {{-- PASSWORD --}}
    <input type="password"
           name="password"
           class="w-full border p-2 rounded"
           placeholder="Password baru (kosongkan jika tidak diubah)">

    {{-- NAMA LENGKAP --}}
    <input name="nama_lengkap"
           value="{{ old('nama_lengkap', $penjual->nama_lengkap) }}"
           class="w-full border p-2 rounded"
           placeholder="Nama Lengkap"
           required>

    {{-- KONTAK --}}
    <input name="kontak"
           value="{{ old('kontak', $penjual->kontak) }}"
           class="w-full border p-2 rounded"
           placeholder="No HP / Kontak"
           required>

    {{-- GENDER --}}
    <select name="gender" class="w-full border p-2 rounded" required>
        <option value="">Pilih Gender</option>
        <option value="Laki-Laki" {{ old('gender', $penjual->gender) == 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
        <option value="Perempuan" {{ old('gender', $penjual->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
    </select>

    {{-- STATUS --}}
    <select name="status" class="w-full border p-2 rounded" required>
        <option value="aktif" {{ old('status', $penjual->status) == 'aktif' ? 'selected' : '' }}>Aktif</option>
        <option value="nonaktif" {{ old('status', $penjual->status) == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
    </select>

    {{-- NAMA TENANT --}}
    <input name="tenant_name"
           value="{{ old('tenant_name', $penjual->tenant_name) }}"
           class="w-full border p-2 rounded"
           placeholder="Nama Tenant">

    {{-- NO TENANT --}}
    <input name="no_tenant"
           value="{{ old('no_tenant', $penjual->no_tenant) }}"
           class="w-full border p-2 rounded"
           placeholder="No Tenant">

    {{-- FOTO TENANT --}}
    <input type="file"
           name="foto_tenant"
           class="w-full border p-2 rounded">

    @if(!empty($penjual->foto_tenant))
        <p class="text-sm text-gray-600">
            Foto saat ini:
            <a href="{{ asset('storage/' . $penjual->foto_tenant) }}" target="_blank" class="text-blue-600 underline">Lihat</a>
        </p>
    @endif

    <div class="flex gap-3">
        <button class="bg-blue-600 text-white px-4 py-2 rounded">
            Update
        </button>
        <a href="{{ route('superadmin.penjual.index') }}"
           class="bg-gray-300 px-4 py-2 rounded">
            Kembali
        </a>
    </div>
</form>
@endsection