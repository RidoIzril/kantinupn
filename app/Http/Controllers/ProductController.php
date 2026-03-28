<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Variant;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

    /*
    | LIST PRODUK
    */
    public function index()
    {
        $products = Produk::with(['kategori','variants'])
            ->latest()
            ->get();

        return view('penjual.produk.list_produk', compact('products'));
    }


    /*
    | FORM TAMBAH
    */
    public function create()
    {
        $categories = Kategori::all();

        return view('penjual.produk.tambah_produk', compact('categories'));
    }


    /*
    | STORE
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required',
            'kategoris_id' => 'required|exists:kategoris,id',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
            'deskripsi' => 'required',
            'foto_produk' => 'required|image'
        ]);

        // upload
        $imagePath = $request->file('foto_produk')
            ->store('produk','public');

        $product = Produk::create([
            'nama' => $validated['nama'],
            'kategoris_id' => $validated['kategoris_id'],
            'harga' => $validated['harga'],
            'stok' => $validated['stok'],
            'deskripsi' => $validated['deskripsi'],
            'foto_produk' => $imagePath,
        ]);

        // VARIANT
        if ($request->variant_name) {
            foreach ($request->variant_name as $i => $name) {
                if($name){
                    Variant::create([
                        'produk_id' => $product->produk_id,
                        'variant_name' => $name,
                        'variant_price' => $request->variant_price[$i] ?? 0
                    ]);
                }
            }
        }

        return redirect()->route('produk.list_produk')
            ->with('success','Produk berhasil ditambahkan');
    }


    /*
    | EDIT
    */
    public function edit($id)
    {
        $product = Produk::with('variants')->findOrFail($id);
        $categories = Kategori::all();

        return view('penjual.produk.edit_produk', compact('product','categories'));
    }


    /*
    | UPDATE
    */
    public function update(Request $request, $id)
    {
        $product = Produk::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required',
            'kategoris_id' => 'required|exists:kategoris,id',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
            'deskripsi' => 'required',
            'foto_produk' => 'nullable|image'
        ]);

        // update image
        if ($request->hasFile('foto_produk')) {

            if ($product->foto_produk) {
                Storage::disk('public')->delete($product->foto_produk);
            }

            $product->foto_produk = $request->file('foto_produk')
                ->store('produk','public');
        }

        $product->update([
            'nama' => $validated['nama'],
            'kategoris_id' => $validated['kategoris_id'],
            'harga' => $validated['harga'],
            'stok' => $validated['stok'],
            'deskripsi' => $validated['deskripsi'],
        ]);

        // reset variant
        Variant::where('produk_id',$product->produk_id)->delete();

        if ($request->variant_name) {
            foreach ($request->variant_name as $i => $name) {
                if($name){
                    Variant::create([
                        'produk_id' => $product->produk_id,
                        'variant_name' => $name,
                        'variant_price' => $request->variant_price[$i] ?? 0
                    ]);
                }
            }
        }

        return redirect()->route('produk.list_produk')
            ->with('success','Produk berhasil diupdate');
    }


    /*
    | DELETE
    */
    public function destroy($id)
    {
        $product = Produk::findOrFail($id);

        if ($product->foto_produk) {
            Storage::disk('public')->delete($product->foto_produk);
        }

        Variant::where('produk_id',$product->produk_id)->delete();

        $product->delete();

        return redirect()->route('produk.list_produk')
            ->with('success','Produk berhasil dihapus');
    }
}