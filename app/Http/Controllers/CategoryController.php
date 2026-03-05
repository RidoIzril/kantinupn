<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('superadmin.kategori.list_kategori', compact('categories'));
    }

    public function store(Request $request)
    {
        $validateData = $request->validate([
            'category_code' => 'required|string|max:5',
            'category_name' => 'required|string|max:255',
        ]);

        Category::create($validateData);

        return redirect()
            ->route('superadmin.kategori.list_kategori')
            ->with('success', 'Data kategori berhasil ditambahkan!');
    }

    public function update(Request $request, string $id)
    {
        $validateData = $request->validate([
            'category_code' => 'required|string|max:5',
            'category_name' => 'required|string|max:255',
        ]);

        Category::findOrFail($id)->update($validateData);

        return redirect()
            ->route('superadmin.kategori.list_kategori')
            ->with('success', 'Data kategori berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        Category::findOrFail($id)->delete();

        return redirect()
            ->route('superadmin.kategori.list_kategori')
            ->with('success', 'Data kategori berhasil dihapus!');
    }
}
