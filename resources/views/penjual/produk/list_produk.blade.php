@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-100">

    @include('penjual.sidebarpenjual')

    {{-- KONTEN --}}
    <div class="flex-1 pt-16 md:pt-0 px-4 py-4 md:p-10">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Daftar Menu</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Daftar menu yang ditampilkan di kantin
                </p>
            </div>

            <a href="{{ route('produk.tambah_produk', ['token' => request('token')]) }}"
               class="w-full sm:w-auto text-center inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition">
                + Tambah Menu
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-100 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- MOBILE (1 produk = 1 card full lebar) --}}
            <div class="md:hidden p-4 space-y-3">
                @forelse ($products as $item)
                    <div class="w-full rounded-2xl border border-slate-200 bg-white shadow-sm p-4">
                        <div class="flex gap-4">
                            {{-- FOTO --}}
                            <div class="shrink-0">
                                @if ($item->foto_produk)
                                    <img src="{{ asset('storage/' . $item->foto_produk) }}"
                                         class="w-20 h-20 object-cover rounded-xl border"
                                         alt="gambar produk">
                                @else
                                    <div class="w-20 h-20 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl text-xs border">
                                        N/A
                                    </div>
                                @endif
                            </div>

                            {{-- INFO --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-800 truncate">{{ $item->nama }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">
                                            {{ $item->kategoris->nama_kategori ?? '-' }}
                                        </p>
                                    </div>
                                    <span class="text-[13px] px-2 py-1 rounded-full bg-slate-100 text-slate-600">
                                        {{ $loop->iteration }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 mt-3">
                                    <div class="bg-slate-50 rounded-xl p-2">
                                        <p class="text-[11px] text-slate-500">Harga</p>
                                        <p class="text-sm font-semibold text-slate-800">
                                            Rp {{ number_format($item->harga, 0, ',', '.') }}
                                        </p>
                                    </div>

                                    <div class="bg-slate-50 rounded-xl p-2">
                                        <p class="text-[11px] text-slate-500">Stok</p>
                                        <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold
                                            {{ $item->stok > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $item->stok }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- AKSI --}}
                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('produk.edit_produk', ['id' => $item->id, 'token' => request('token')]) }}"
                               class="flex-1 text-center px-3 py-2 rounded-xl bg-yellow-400 text-white text-sm font-semibold hover:bg-yellow-500 transition">
                                Edit
                            </a>

                            <form class="flex-1"
                                  action="{{ route('produk.destroy', ['id' => $item->id, 'token' => request('token')]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus menu ini?')">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="token" value="{{ request('token') }}">
                                <button type="submit"
                                        class="w-full px-3 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-slate-500 py-10">
                        Belum ada menu
                    </div>
                @endforelse
            </div>

            {{-- DESKTOP TABLE --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 text-center w-24">Gambar</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Kategori</th>
                            <th class="px-4 py-3 text-center">Harga</th>
                            <th class="px-4 py-3 text-center">Stok</th>
                            <th class="px-4 py-3 text-center w-36">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($products as $item)
                        <tr class="border-t hover:bg-slate-50 transition">
                            <td class="px-4 py-3 text-center">{{ $loop->iteration }}</td>

                            <td class="px-4 py-3 text-center">
                                @if ($item->foto_produk)
                                    <img src="{{ asset('storage/' . $item->foto_produk) }}"
                                         class="w-14 h-14 object-cover rounded-lg border mx-auto"
                                         alt="gambar produk">
                                @else
                                    <div class="w-14 h-14 flex items-center justify-center bg-slate-100 text-slate-400 rounded-lg text-xs mx-auto border">
                                        N/A
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-3 font-medium text-slate-800">{{ $item->nama }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $item->kategoris->nama_kategori ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>

                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-lg text-xs font-medium {{ $item->stok > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $item->stok }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('produk.edit_produk', ['id' => $item->id, 'token' => request('token')]) }}"
                                       class="px-3 py-1.5 rounded-lg bg-yellow-400 text-white text-xs font-semibold hover:bg-yellow-500 transition">
                                        Edit
                                    </a>

                                    <form action="{{ route('produk.destroy', ['id' => $item->id, 'token' => request('token')]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus menu ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="token" value="{{ request('token') }}">
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-slate-500">
                                Belum ada menu
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>
@endsection