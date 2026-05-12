<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Variant;
use App\Models\Penjual;
use App\Models\Tenants;

class ProductApiController extends Controller
{
    /**
     * Resolve user dari token Sanctum
     */
    private function resolveUser(Request $request)
    {
        $user = $request->user();

        if ($user) {
            return $user;
        }

        $plainTextToken =
            $request->bearerToken()
            ?? $request->query('token')
            ?? $request->input('token');

        if (!$plainTextToken) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($plainTextToken);

        return $accessToken?->tokenable;
    }

    /**
     * Ambil tenant berdasarkan user login
     */
    private function getTenant(Request $request)
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return null;
        }

        $penjual = Penjual::where('users_id', $user->id)->first();

        if (!$penjual) {
            return null;
        }

        return Tenants::where('penjuals_id', $penjual->id)->first();
    }

    /**
     * GET LIST PRODUK
     */
    public function index(Request $request)
    {
        $tenant = $this->getTenant($request);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized / Tenant tidak ditemukan'
            ], 401);
        }

        $products = Produk::with(['kategoris', 'variants'])
            ->where('tenants_id', $tenant->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'List produk berhasil diambil',
            'data' => $products
        ]);
    }

    /**
     * DETAIL PRODUK
     */
    public function show(Request $request, $id)
    {
        $tenant = $this->getTenant($request);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized / Tenant tidak ditemukan'
            ], 401);
        }

        $product = Produk::with(['kategoris', 'variants'])
            ->where('tenants_id', $tenant->id)
            ->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail produk berhasil diambil',
            'data' => $product
        ]);
    }

    /**
     * TAMBAH PRODUK
     */
    public function store(Request $request)
    {
        $tenant = $this->getTenant($request);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized / Tenant tidak ditemukan'
            ], 401);
        }

        $validated = $request->validate([
            'nama'            => 'required|string|max:255',
            'kategoris_id'    => 'required|exists:kategoris,id',
            'harga'           => 'required|numeric|min:0',
            'stok'            => 'required|integer|min:0',
            'deskripsi'       => 'required|string',
            'foto_produk'     => 'required|image|max:2048',
            'nama_variant'    => 'nullable|array',
            'nama_variant.*'  => 'nullable|string|max:255',
            'harga_variant'   => 'nullable|array',
            'harga_variant.*' => 'nullable|numeric|min:0',
        ]);

        $imagePath = $request->file('foto_produk')->store('produk', 'public');

        $product = Produk::create([
            'kategoris_id' => $validated['kategoris_id'],
            'tenants_id'   => $tenant->id,
            'nama'         => $validated['nama'],
            'deskripsi'    => $validated['deskripsi'],
            'harga'        => $validated['harga'],
            'stok'         => $validated['stok'],
            'foto_produk'  => $imagePath,
        ]);

        if ($request->filled('nama_variant')) {
            foreach ($request->nama_variant as $i => $namaVariant) {

                if (!empty($namaVariant)) {

                    Variant::create([
                        'produks_id'    => $product->id,
                        'nama_variant'  => $namaVariant,
                        'harga_variant' => $request->harga_variant[$i] ?? 0,
                        'status_variant'=> isset($request->status_variant[$i])
                            ? (bool)$request->status_variant[$i]
                            : true,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product->load('variants')
        ], 201);
    }

    /**
     * UPDATE PRODUK
     */
    public function update(Request $request, $id)
    {
        $tenant = $this->getTenant($request);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized / Tenant tidak ditemukan'
            ], 401);
        }

        $product = Produk::where('tenants_id', $tenant->id)->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'nama'            => 'required|string|max:255',
            'kategoris_id'    => 'required|exists:kategoris,id',
            'harga'           => 'required|numeric|min:0',
            'stok'            => 'required|integer|min:0',
            'deskripsi'       => 'required|string',
            'foto_produk'     => 'nullable|image|max:2048',
            'nama_variant'    => 'nullable|array',
            'nama_variant.*'  => 'nullable|string|max:255',
            'harga_variant'   => 'nullable|array',
            'harga_variant.*' => 'nullable|numeric|min:0',
        ]);

        if ($request->hasFile('foto_produk')) {

            if ($product->foto_produk) {
                Storage::disk('public')->delete($product->foto_produk);
            }

            $product->foto_produk = $request->file('foto_produk')
                ->store('produk', 'public');
        }

        $product->update([
            'kategoris_id' => $validated['kategoris_id'],
            'nama'         => $validated['nama'],
            'deskripsi'    => $validated['deskripsi'],
            'harga'        => $validated['harga'],
            'stok'         => $validated['stok'],
            'foto_produk'  => $product->foto_produk,
        ]);

        Variant::where('produks_id', $product->id)->delete();

        if ($request->filled('nama_variant')) {

            foreach ($request->nama_variant as $i => $namaVariant) {

                if (!empty($namaVariant)) {

                    Variant::create([
                        'produks_id'    => $product->id,
                        'nama_variant'  => $namaVariant,
                        'harga_variant' => $request->harga_variant[$i] ?? 0,
                        'status_variant'=> isset($request->status_variant[$i])
                            ? (bool)$request->status_variant[$i]
                            : true,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diupdate',
            'data' => $product->load('variants')
        ]);
    }

    /**
     * HAPUS PRODUK
     */
    public function destroy(Request $request, $id)
    {
        $tenant = $this->getTenant($request);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized / Tenant tidak ditemukan'
            ], 401);
        }

        $product = Produk::where('tenants_id', $tenant->id)->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        if ($product->foto_produk) {
            Storage::disk('public')->delete($product->foto_produk);
        }

        Variant::where('produks_id', $product->id)->delete();

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus'
        ]);
    }
}