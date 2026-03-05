@extends('layouts.app')
@section('title','Edit Penjual')

@section('content')
<h1 class="text-xl font-bold mb-6">Edit Akun Penjual</h1>

{{-- ERROR --}}
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
      action="{{ route('superadmin.penjual.update', $penjual->penjual_id) }}"
      class="space-y-4 max-w-xl">
@csrf
@method('PUT')

{{-- NAMA --}}
<input name="penjual_fullname"
       value="{{ old('penjual_fullname', $penjual->penjual_fullname) }}"
       class="w-full border p-2 rounded"
       placeholder="Nama Lengkap"
       required>

{{-- NO TENANT --}}
<input name="penjual_notenant"
       value="{{ old('penjual_notenant', $penjual->penjual_notenant) }}"
       class="w-full border p-2 rounded"
       placeholder="No Tenant"
       required>

{{-- NAMA TENANT --}}
<input name="penjual_tenantname"
       value="{{ old('penjual_tenantname', $penjual->penjual_tenantname) }}"
       class="w-full border p-2 rounded"
       placeholder="Nama Tenant"
       required>

{{-- NO HP --}}
<input name="penjual_nohp"
       value="{{ old('penjual_nohp', $penjual->penjual_nohp) }}"
       class="w-full border p-2 rounded"
       placeholder="No HP"
       required>

{{-- GENDER --}}
<select name="penjual_gender" class="w-full border p-2 rounded" required>
    <option value="Laki-laki" {{ $penjual->penjual_gender == 'Laki-laki' ? 'selected' : '' }}>
        Laki-laki
    </option>
    <option value="Perempuan" {{ $penjual->penjual_gender == 'Perempuan' ? 'selected' : '' }}>
        Perempuan
    </option>
</select>

{{-- STATUS (INI YANG BARU) --}}
<div>
    <label class="block text-sm font-medium mb-1">Status Akun</label>
    <select name="penjual_status"
            class="w-full border p-2 rounded"
            required>
        <option value="aktif" {{ $penjual->penjual_status == 'aktif' ? 'selected' : '' }}>
            Aktif
        </option>
        <option value="nonaktif" {{ $penjual->penjual_status == 'nonaktif' ? 'selected' : '' }}>
            Nonaktif
        </option>
    </select>
</div>

{{-- USERNAME --}}
<input name="penjual_username"
       value="{{ old('penjual_username', $penjual->penjual_username) }}"
       class="w-full border p-2 rounded"
       placeholder="Username"
       required>

{{-- PASSWORD --}}
<input type="password"
       name="penjual_password"
       class="w-full border p-2 rounded"
       placeholder="Password baru (kosongkan jika tidak diubah)">

{{-- ACTION --}}
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
