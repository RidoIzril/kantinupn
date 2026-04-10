<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Variant;
use App\Models\Penjual;
use App\Models\Tenants;

class ProductController extends Controller
{
    private function resolveUser(Request $request)
    {
        $user = $request->user() ?? auth()->user();
        if ($user) return $user;

        $plainTextToken = $request->query('token')
            ?? $request->input('token')
            ?? $request->bearerToken();

        if (!$plainTextToken) return null;

        $accessToken = PersonalAccessToken::findToken($plainTextToken);
        return $accessToken?->tokenable;
    }

    private function getTenantOrRedirect(Request $request): Tenants|RedirectResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Silakan login terlebih dahulu.']);
        }

        $penjual = Penjual::where('users_id', $user->id)->first();
        if (!$penjual) {
            return redirect()->route('penjual.homepenjual')
                ->withErrors(['error' => 'Akun penjual belum terhubung ke data penjual.']);
        }

        $tenant = Tenants::where('penjuals_id', $penjual->id)->first();
        if (!$tenant) {
            return redirect()->route('penjual.homepenjual')
                ->withErrors(['error' => 'Tenant belum dibuat. Hubungi superadmin.']);
        }

        return $tenant;
    }

    public function index(Request $request)
    {
        $tenant = $this->getTenantOrRedirect($request);
        if ($tenant instanceof RedirectResponse) return $tenant;

        $products = Produk::with(['kategoris', 'variants'])
            ->where('tenants_id', $tenant->id)
            ->latest()
            ->get();

        return view('penjual.produk.list_produk', compact('products'));
    }

    public function create(Request $request)
    {
        $tenant = $this->getTenantOrRedirect($request);
        if ($tenant instanceof RedirectResponse) return $tenant;

        $categories = Kategori::all();
        return view('penjual.produk.tambah_produk', compact('categories'));
    }

    public function store(Request $request)
    {
        $tenant = $this->getTenantOrRedirect($request);
        if ($tenant instanceof RedirectResponse) return $tenant;

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
            'token'           => 'nullable|string',
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
                    ]);
                }
            }
        }

        return redirect()->route('produk.list_produk', [
            'token' => $request->input('token') ?? $request->query('token')
        ])->with('success', 'Produk berhasil ditambahkan');
    }

    public function edit(Request $request, $id)
    {
        $tenant = $this->getTenantOrRedirect($request);
        if ($tenant instanceof RedirectResponse) return $tenant;

        $product = Produk::with('variants')
            ->where('tenants_id', $tenant->id)
            ->findOrFail($id);

        $categories = Kategori::all();

        return view('penjual.produk.edit_produk', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $tenant = $this->getTenantOrRedirect($request);
        if ($tenant instanceof RedirectResponse) return $tenant;

        $product = Produk::where('tenants_id', $tenant->id)->findOrFail($id);

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
            'token'           => 'nullable|string',
        ]);

        if ($request->hasFile('foto_produk')) {
            if ($product->foto_produk) {
                Storage::disk('public')->delete($product->foto_produk);
            }
            $product->foto_produk = $request->file('foto_produk')->store('produk', 'public');
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
                    ]);
                }
            }
        }

        return redirect()->route('produk.list_produk', [
            'token' => $request->input('token') ?? $request->query('token')
        ])->with('success', 'Produk berhasil diupdate');
    }

    public function destroy(Request $request, $id)
    {
        $tenant = $this->getTenantOrRedirect($request);
        if ($tenant instanceof RedirectResponse) return $tenant;

        $product = Produk::where('tenants_id', $tenant->id)->findOrFail($id);

        if ($product->foto_produk) {
            Storage::disk('public')->delete($product->foto_produk);
        }

        Variant::where('produks_id', $product->id)->delete();
        $product->delete();

        return redirect()->route('produk.list_produk', [
            'token' => $request->input('token') ?? $request->query('token')
        ])->with('success', 'Produk berhasil dihapus');
    }
}