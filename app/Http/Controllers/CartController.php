<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | LIST KERANJANG
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $customerId = Auth::guard('customer')->id();

        $cartItems = Cart::with([
            'product.category',
            'variant'
        ])
        ->where('customer_id', $customerId)
        ->get();

        $paymentMethods = \App\Models\Payment::all();

        return view('customer.carts.cartcustomer', [
            'cartItems' => $cartItems,
            'paymentMethods' => $paymentMethods
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | TAMBAH KE KERANJANG
    |--------------------------------------------------------------------------
    */

    public function add(Request $request)
    {
        $customerId = Auth::guard('customer')->id();

        $product = Product::findOrFail($request->product_id);

        $variantId = $request->variant_id ?? null;

        $qty = $request->qty ?? 1;

        $penjualId = $product->penjual_id;


        $cart = Cart::where('customer_id', $customerId)
            ->where('product_id', $product->product_id)
            ->where('variant_id', $variantId)
            ->first();


        if ($cart) {

            $cart->quantity += $qty;
            $cart->save();

        } else {

            Cart::create([
                'customer_id' => $customerId,
                'product_id'  => $product->product_id,
                'variant_id'  => $variantId,
                'penjual_id'  => $penjualId,
                'quantity'    => $qty
            ]);

        }

        return back()->with('success','Menu berhasil ditambahkan ke keranjang');
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE QTY
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        $cart = Cart::findOrFail($request->cart_id);

        $cart->quantity = max(1, $request->quantity);

        $cart->save();

        return back();
    }


    /*
    |--------------------------------------------------------------------------
    | HAPUS ITEM
    |--------------------------------------------------------------------------
    */

    public function remove(Request $request)
    {
        $cart = Cart::findOrFail($request->cart_id);

        $cart->delete();

        return back();
    }

}