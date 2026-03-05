<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * ===============================
     * LIST PRODUK PENJUAL LOGIN
     * ===============================
     */
    public function index()
    {
        $penjualId = Auth::guard('penjual')->id();

        $products = Product::with('category')
            ->where('penjual_id', $penjualId)
            ->latest()
            ->get();

        return view('penjual.produk.list_produk', compact('products'));
    }

    /**
     * ===============================
     * FORM TAMBAH PRODUK
     * ===============================
     */
    public function create()
    {
        $categories = Category::all();

        return view('penjual.produk.tambah_produk', compact('categories'));
    }

    /**
     * ===============================
     * STORE PRODUK BARU
     * ===============================
     */
    public function store(Request $request)
    {
        $penjualId = Auth::guard('penjual')->id();

        if (!$penjualId) {
            abort(403, 'Penjual tidak login');
        }

        $validated = $request->validate([
            'product_name'        => 'required|string|max:255',
            'category_id'         => 'required|exists:categories,category_id',
            'product_price'       => 'required|numeric|min:0',
            'product_stock'       => 'required|integer|min:0',
            'product_description' => 'required|string',
            'product_image'       => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        /**
         * ===============================
         * GENERATE PRODUCT CODE
         * ===============================
         */
        $category = Category::findOrFail($validated['category_id']);
        $categoryCode = $category->category_code;

        $lastProduct = Product::where('product_code', 'like', "{$categoryCode}-%")
            ->orderBy('product_code', 'desc')
            ->first();

        $nextNumber = $lastProduct
            ? ((int) substr($lastProduct->product_code, strlen($categoryCode) + 1)) + 1
            : 1;

        $productCode = $categoryCode . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        /**
         * ===============================
         * UPLOAD IMAGE
         * ===============================
         */
        $imagePath = $request->file('product_image')
            ->store('products', 'public');

        /**
         * ===============================
         * SAVE PRODUCT
         * ===============================
         */
        Product::create([
            'penjual_id'          => $penjualId,
            'product_code'        => $productCode,
            'product_name'        => $validated['product_name'],
            'category_id'         => $validated['category_id'],
            'product_price'       => $validated['product_price'],
            'product_stock'       => $validated['product_stock'],
            'product_description' => $validated['product_description'],
            'product_image'       => $imagePath,
        ]);

        return redirect()
            ->route('produk.list_produk')
            ->with('success', 'Produk berhasil ditambahkan');
    }

    /**
     * ===============================
     * FORM EDIT PRODUK
     * ===============================
     */
    public function edit($id)
    {
        $penjualId = Auth::guard('penjual')->id();

        $product = Product::where('penjual_id', $penjualId)
            ->findOrFail($id);

        $categories = Category::all();

        return view('penjual.produk.edit_produk', compact('product', 'categories'));
    }

    /**
     * ===============================
     * UPDATE PRODUK
     * ===============================
     */
    public function update(Request $request, $id)
    {
        $penjualId = Auth::guard('penjual')->id();

        $product = Product::where('penjual_id', $penjualId)
            ->findOrFail($id);

        $validated = $request->validate([
            'product_name'        => 'required|string|max:255',
            'category_id'         => 'required|exists:categories,category_id',
            'product_price'       => 'required|numeric|min:0',
            'product_stock'       => 'required|integer|min:0',
            'product_description' => 'required|string',
            'product_image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        /**
         * ===============================
         * UPDATE IMAGE
         * ===============================
         */
        if ($request->hasFile('product_image')) {

            // Hapus image lama
            if ($product->product_image && Storage::disk('public')->exists($product->product_image)) {
                Storage::disk('public')->delete($product->product_image);
            }

            // Upload baru
            $product->product_image = $request->file('product_image')
                ->store('products', 'public');
        }

        /**
         * ===============================
         * UPDATE DATA
         * ===============================
         */
        $product->update([
            'product_name'        => $validated['product_name'],
            'category_id'         => $validated['category_id'],
            'product_price'       => $validated['product_price'],
            'product_stock'       => $validated['product_stock'],
            'product_description' => $validated['product_description'],
        ]);

        return redirect()
            ->route('produk.list_produk')
            ->with('success', 'Produk berhasil diperbarui');
    }

    /**
     * ===============================
     * DELETE PRODUK
     * ===============================
     */
    public function destroy($id)
    {
        $penjualId = Auth::guard('penjual')->id();

        $product = Product::where('penjual_id', $penjualId)
            ->findOrFail($id);

        /**
         * DELETE IMAGE
         */
        if ($product->product_image && Storage::disk('public')->exists($product->product_image)) {
            Storage::disk('public')->delete($product->product_image);
        }

        $product->delete();

        return redirect()
            ->route('produk.list_produk')
            ->with('success', 'Produk berhasil dihapus');
    }
}
