<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class CartController extends Controller
{
    public function index()
    {
        if (auth()->guard('customer')->check()) {
            // Ambil item keranjang dengan relasi product untuk customer yang login
            $cartItems = Cart::with('product')
                ->where('customer_id', auth()->guard('customer')->id())
                ->get();
            
            // Hitung total harga keranjang
            $total = $this->calculateDBCartTotal($cartItems);
            // Ambil semua metode pembayaran
            $paymentMethods = Payment::all();
            
            return view('customer.carts.cartcustomer', compact('cartItems', 'total', 'paymentMethods'));
        }
    }

    public function add(Request $request)
    {
        // Cari produk berdasarkan ID
        $product = Product::with('category')->findOrFail($request->product_id);
        
        if (auth()->guard('customer')->check()) {
            // Simpan ke database untuk user yang login
            $cartItem = Cart::updateOrCreate(
                [
                    'customer_id' => auth()->guard('customer')->id(),
                    'product_id' => $product->product_id
                ],
                [
                    'quantity' => 1,
                    'price' => $product->product_price
                ]
            ); 
            return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
        }
    }

    public function update(Request $request)
    {
        if($request->id && $request->quantity){
            if (auth()->guard('customer')->check()) {
                // Cari item keranjang berdasarkan customer dan product ID
                $cartItem = Cart::where('customer_id', auth()->guard('customer')->id())
                    ->where('product_id', $request->id)
                    ->first();
                
                if ($cartItem) {
                    // Validasi stok sebelum update
                    if ($request->quantity > $cartItem->product->product_stock) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Quantity melebihi stok yang tersedia',
                        ]);
                    }
                    // Update quantity
                    $cartItem->update(['quantity' => $request->quantity]);
                    
                    // Ambil semua item keranjang untuk hitung total
                    $cartItems = Cart::with('product')
                        ->where('customer_id', auth()->guard('customer')->id())
                        ->get();
                    
                    // Hitung subtotal item dan total keranjang
                    $subtotal = $cartItem->price * $cartItem->quantity;
                    $total = $this->calculateDBCartTotal($cartItems);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Jumlah berhasil diupdate',
                        'subtotal' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
                        'total' => 'Rp ' . number_format($total, 0, ',', '.')
                    ]);
                }
            } 
        }
    }

    public function remove(Request $request)
    {
        if($request->id) {
            if (auth()->guard('customer')->check()) {
                // Hapus item keranjang berdasarkan customer dan product ID
                $deleted = Cart::where('customer_id', auth()->guard('customer')->id())
                    ->where('product_id', $request->id)
                    ->delete();
                
                if ($deleted) {
                    // Ambil sisa item keranjang
                    $cartItems = Cart::with('product')
                        ->where('customer_id', auth()->guard('customer')->id())
                        ->get();
                    
                    // Hitung total keranjang setelah penghapusan
                    $total = $this->calculateDBCartTotal($cartItems);
                    // Return response JSON dengan data terupdate
                    return response()->json([
                        'success' => true,
                        'message' => 'Produk berhasil dihapus',
                        'total' => 'Rp ' . number_format($total, 0, ',', '.'),
                        'remaining_items' => $cartItems->count()
                    ]);
                }
            }
        }
    }

    public function checkout(Request $request)
    {
        $customerId = auth()->guard('customer')->id();
        // Ambil semua item keranjang
        $cartItems = Cart::with('product')
            ->where('customer_id', $customerId)
            ->get();

        // Cek keranjang tidak kosong
        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('error', 'Keranjang belanja kosong.');
        }

        // Validasi stok produk sebelum checkout
        foreach ($cartItems as $item) {
            $product = $item->product;
            if ($product->product_stock < $item->quantity) {
                return redirect()->back()
                    ->with('error', 'Stok produk "' . $product->product_name . '" tidak mencukupi. Stok tersedia: ' . $product->product_stock);
            }
        }

        // Validasi metode pembayaran
        $request->validate([
            'payment_method' => 'required|exists:payments,payment_id'
        ]);

        // Mulai transaksi database
        DB::beginTransaction();
        try {
            // Buat order baru
            $total = $this->calculateDBCartTotal($cartItems);
            $order = Order::create([
                'customer_id' => $customerId,
                'order_date' => now(),
                'total_amount' => $cartItems->sum('quantity'),
                'total_price' => $total,
                'status' => 'pending'
            ]);

            // Buat order items dan kurangi stok produk
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->order_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price_per_unit' => $item->price,
                    'subtotal' => $item->price * $item->quantity
                ]);

                // Kurangi stok produk
                $product = $item->product;
                $product->product_stock -= $item->quantity;
                $product->save();
            }

            // Buat kode transaksi unik
            do {
                $randomNumber = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                $customTransactionCode = 'TAPK-' . $randomNumber;
            } while (Transaction::where('custom_code_transaction', $customTransactionCode)->exists());
            
            // Buat transaksi
            $transaction = Transaction::create([
                'order_id' => $order->order_id,
                'payment_id' => $request->payment_method,
                'transaction_date' => now(),
                'status' => 'pending',
                'delivery_status' => 'processing',
                'delivery_address' => auth()->guard('customer')->user()->customer_address,
                'custom_code_transaction' => $customTransactionCode
            ]);

            // Kosongkan keranjang belanja
            Cart::where('customer_id', $customerId)->delete();

            DB::commit();

            return redirect()->route('transactions.list_transaction')
            ->with([
                'success' => 'Checkout berhasil! Pesanan Anda sedang diproses.',
                'transaction_code' => $customTransactionCode
            ]);


        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat checkout: ' . $e->getMessage());
        }
    }

    private function calculateDBCartTotal($cartItems)
    {
        return $cartItems->sum(function($item) {
            return $item->price * $item->quantity;
        });
    }
    

}