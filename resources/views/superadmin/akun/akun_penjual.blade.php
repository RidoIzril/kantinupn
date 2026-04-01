@extends('layouts.app')
@section('title','Kelola Penjual')
@include('superadmin.sidebarsuperadmin')

@section('content')
<h1 class="text-xl font-bold mb-4">Kelola Akun Penjual</h1>

{{-- ALERT --}}
@if(session('success'))
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

{{-- TOP ACTION --}}
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">

    {{-- SEARCH --}}
    <form method="GET" class="flex gap-2">
        <input type="text"
               name="search"
               value="{{ $keyword ?? '' }}"
               placeholder="Cari username / tenant / no HP"
               class="border px-3 py-2 rounded w-64">
        <button class="bg-blue-600 text-white px-4 rounded">
            Cari
        </button>
    </form>

    {{-- TAMBAH --}}
    <a href="{{ route('superadmin.penjual.create') }}"
       class="bg-green-600 text-white px-4 py-2 rounded">
        + Tambah Penjual
    </a>
</div>

<table class="w-full bg-white rounded shadow text-sm">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-3 text-left">Username</th>
            <th class="p-3 text-left">Nama Tenant</th>
            <th class="p-3 text-left">No Tenant</th>
            <th class="p-3 text-left">No HP</th>
            <th class="p-3 text-left">Status</th>
            <th class="p-3 text-center">Aksi</th>
        </tr>
    </thead>
    <tbody>
    @forelse($penjuals as $p)
        <tr class="border-b">
            <td class="p-3">{{ $p->penjual_username }}</td>
            <td class="p-3">{{ $p->penjual_tenantname }}</td>
            <td class="p-3">{{ $p->penjual_notenant }}</td>
            <td class="p-3">{{ $p->penjual_nohp }}</td>
            <td class="p-3">
                <span class="px-2 py-1 rounded text-xs
                    {{ $p->penjual_status === 'aktif'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-red-100 text-red-700' }}">
                    {{ ucfirst($p->penjual_status) }}
                </span>
            </td>
            <td class="p-3 text-center flex justify-center gap-2">

                {{-- TOGGLE STATUS --}}
                <form method="POST"
                      action="{{ route('superadmin.penjual.update_status', $p->penjual_id) }}">
                    @csrf
                    @method('PUT')
                   <input type="hidden" name="penjual_status"
       value="{{ $p->penjual_status === 'aktif' ? 'nonaktif' : 'aktif' }}">
                    <button class="px-3 py-1 rounded text-white text-xs
                        {{ $p->penjual_status === 'aktif'
                            ? 'bg-yellow-500'
                            : 'bg-green-600' }}">
                        {{ $p->penjual_status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>

                {{-- DELETE --}}
                <form method="POST"
                      action="{{ route('superadmin.penjual.destroy', $p->penjual_id) }}"
                      onsubmit="return confirm('Yakin hapus akun penjual ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-1 rounded bg-red-600 text-white text-xs">
                        Hapus
                    </button>
                </form>

                <a href="{{ route('superadmin.penjual.edit', $p->penjual_id) }}"
   class="px-3 py-1 rounded bg-blue-500 text-white text-xs">
   Edit
</a>


            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="p-4 text-center text-gray-500">
                Data penjual tidak ditemukan
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
@endsection
