<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Kategori::all();
        return view('superadmin.kategori.list_kategori', compact('categories'));
    }

    public function store(Request $request)
    {
        $validateData = $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        Kategori::create($validateData);

        return redirect()
            ->route('superadmin.kategori.list_kategori')
            ->with('success', 'Data kategori berhasil ditambahkan!');
    }

    public function update(Request $request, string $id)
    {
        $validateData = $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        Kategori::findOrFail($id)->update($validateData);

        return redirect()
            ->route('superadmin.kategori.list_kategori')
            ->with('success', 'Data kategori berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        Kategori::findOrFail($id)->delete();

        return redirect()
            ->route('superadmin.kategori.list_kategori')
            ->with('success', 'Data kategori berhasil dihapus!');
    }
}