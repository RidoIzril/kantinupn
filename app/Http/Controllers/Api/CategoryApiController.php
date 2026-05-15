<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kategori;

class CategoryApiController extends Controller
{
    /**
     * GET /api/categories
     */
    public function index(Request $request)
    {
        $categories = Kategori::query()
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ], 200);
    }

    /**
     * POST /api/categories
     * Body JSON: { "nama_kategori": "Makanan" }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        $category = Kategori::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data kategori berhasil ditambahkan!',
            'data' => $category
        ], 201);
    }

    /**
     * PUT /api/categories/{id}
     * Body JSON: { "nama_kategori": "Minuman" }
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        $category = Kategori::findOrFail($id);
        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data kategori berhasil diperbarui!',
            'data' => $category->fresh()
        ], 200);
    }

    /**
     * DELETE /api/categories/{id}
     */
    public function destroy(string $id)
    {
        $category = Kategori::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data kategori berhasil dihapus!'
        ], 200);
    }
}