@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-100">

    @include('penjual.sidebarpenjual')

    <div class="flex-1 p-6 md:p-10">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Daftar Menu</h1>
                <p class="text-sm text-slate-500 mt-1">
                    Daftar menu yang ditampilkan di kantin
                </p>
            </div>

            <a href="{{ route('produk.tambah_produk', ['token' => request('token')]) }}"
   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition">
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
            <div class="overflow-x-auto">
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

                    <tbody class="divide-y">
                        @forelse ($products as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-center">{{ $loop->iteration }}</td>

                            <td class="px-4 py-3 text-center">
                                @if ($item->foto_produk)
                                    <img src="{{ asset('storage/' . $item->foto_produk) }}"
                                         class="w-14 h-14 object-cover rounded-lg border mx-auto"
                                         alt="gambar produk">
                                @else
                                    <div class="w-14 h-14 flex items-center justify-center bg-slate-100 text-slate-400 rounded-lg mx-auto text-xs">
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