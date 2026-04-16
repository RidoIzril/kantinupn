@extends('layouts.app')
@section('title','Kelola Penjual')
@include('superadmin.sidebarsuperadmin')

@section('content')
<h1 class="text-xl font-bold mb-4">Kelola Akun Penjual</h1>

@if(session('success'))
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
        <ul class="list-disc ml-5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ $keyword ?? '' }}"
               placeholder="Cari username / tenant / no HP"
               class="border px-3 py-2 rounded w-64">
        <button class="bg-blue-600 text-white px-4 rounded">Cari</button>
    </form>

    <a href="{{ route('superadmin.penjual.create') }}" class="bg-green-600 text-white px-4 py-2 rounded">
        + Tambah Penjual
    </a>
</div>

<table class="w-full bg-white rounded shadow text-sm">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-3 text-left">Username</th>
            <th class="p-3 text-left">Nama Lengkap</th>
            <th class="p-3 text-left">Nama Tenant</th>
            <th class="p-3 text-left">No Tenant</th>
            <th class="p-3 text-left">Kantin</th>
            <th class="p-3 text-left">Deskripsi Tenant</th>
            <th class="p-3 text-left">Kontak</th>
            <th class="p-3 text-left">Status</th>
            <th class="p-3 text-center">Aksi</th>
        </tr>
    </thead>
    <tbody>
    @forelse($penjuals as $p)
        <tr class="border-b">
            <td class="p-3">{{ $p->username ?? '-' }}</td>
            <td class="p-3">{{ $p->nama_lengkap ?? '-' }}</td>
            <td class="p-3">{{ $p->tenant_name ?? '-' }}</td>
            <td class="p-3 text-center">{{ $p->no_tenant ?? '-' }}</td>
            {{-- KANTIN --}}
            <td class="p-3 text-center">
                {{ $p->kantin == '1' ? '1' : ($p->kantin == '2' ? '2' : '-') }}
            </td>
            {{-- DESKRIPSI TENANT --}}
            <td class="p-3">
                {{ !empty($p->desk_tenant) ? $p->desk_tenant : '-' }}
            </td>
            <td class="p-3">{{ $p->kontak ?? '-' }}</td>
            <td class="p-3">
                <span class="px-2 py-1 rounded text-xs {{ ($p->status ?? 'nonaktif') === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ ucfirst($p->status ?? 'nonaktif') }}
                </span>
            </td>
            <td class="p-3">
                <div class="flex items-center justify-center gap-2">
                    {{-- STATUS --}}
                    <form method="POST" action="{{ route('superadmin.penjual.update_status', $p->id) }}" class="m-0">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="{{ ($p->status ?? 'nonaktif') === 'aktif' ? 'nonaktif' : 'aktif' }}">
                        <button type="submit"
                            class="min-w-[90px] h-8 px-3 inline-flex items-center justify-center leading-none rounded-md text-white text-sm font-medium
                                   {{ ($p->status ?? 'nonaktif') === 'aktif' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                            {{ ($p->status ?? 'nonaktif') === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>

                    {{-- EDIT --}}
                    <a href="{{ route('superadmin.penjual.edit', $p->id) }}"
                       class="min-w-[50px] h-8 px-3 inline-flex items-center justify-center leading-none rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
                        Edit
                    </a>

                    {{-- DELETE --}}
                    <form method="POST" action="{{ route('superadmin.penjual.destroy', $p->id) }}" class="m-0"
                          onsubmit="return confirm('Yakin hapus akun penjual ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="min-w-[50px] h-8 px-3 inline-flex items-center justify-center leading-none rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">
                            Hapus
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="9" class="p-4 text-center text-gray-500">Data penjual tidak ditemukan</td></tr>
    @endforelse
    </tbody>
</table>
@endsection