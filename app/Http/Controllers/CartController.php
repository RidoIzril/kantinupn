<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Produk;
use App\Models\Customers;
use App\Models\Order;
use App\Models\DetailOrder;
use App\Models\Transaksi;
use App\Models\Delivery;
use App\Models\Variant;

class CartController extends Controller
{
    /**
     * WEB FLOW:
     * 1) auth()->user()
     * 2) bearer token
     * 3) token dari input/query (karena form biasa dari blade)
     */
    private function resolveUser(Request $request)
    {
        $user = $request->user() ?? auth()->user();
        if ($user) return $user;

        $plainTextToken = $request->bearerToken()
            ?? $request->input('token')
            ?? $request->query('token');

        if (!$plainTextToken) return null;

        $accessToken = PersonalAccessToken::findToken($plainTextToken);
        return $accessToken?->tokenable;
    }

    private function resolveCustomer(Request $request): ?Customers
    {
        $user = $this->resolveUser($request);
        if (!$user || $user->role !== 'customer') return null;

        return Customers::firstOrCreate(
            ['users_id' => $user->id],
            ['users_id' => $user->id]
        );
    }

    private function redirectUnauthorized()
    {
        return redirect('/login')->withErrors([
            'auth' => 'Silakan login sebagai customer terlebih dahulu.'
        ]);
    }

    private function resolveCart(Customers $customer): Cart
    {
        return Cart::firstOrCreate(['customers_id' => $customer->id]);
    }

    public function index(Request $request)
    {
        $customer = $this->resolveCustomer($request);
        if (!$customer) return $this->redirectUnauthorized();

        $cart = Cart::where('customers_id', $customer->id)->first();

        $cartItems = collect();
        if ($cart) {
            $cartItems = CartItem::with(['produk.kategoris', 'variant'])
                ->where('carts_id', $cart->id)
                ->get();
        }

        return view('customer.carts.cartcustomer', compact('cartItems'));
    }

    public function add(Request $request)
    {
        $customer = $this->resolveCustomer($request);
        if (!$customer) return $this->redirectUnauthorized();

        $validated = $request->validate([
            'product_id'    => 'required|integer|exists:produks,id',
            'qty'           => 'nullable|integer|min:1',
            'variant_ids'   => 'nullable|array',
            'variant_ids.*' => 'integer|exists:variants,id',
            'token'         => 'nullable|string',
        ]);

        $produk = Produk::findOrFail($validated['product_id']);
        $qty = $validated['qty'] ?? 1;
        $variantIds = $validated['variant_ids'] ?? [];

        if (!empty($variantIds)) {
            $countValid = Variant::whereIn('id', $variantIds)
                ->where('produks_id', $produk->id)
                ->count();

            if ($countValid !== count($variantIds)) {
                return back()->withErrors(['variant_ids' => 'Variant tidak sesuai dengan produk.']);
            }
        }

        $cart = $this->resolveCart($customer);

        if (empty($variantIds)) {
            $item = CartItem::where('carts_id', $cart->id)
                ->where('produks_id', $produk->id)
                ->whereNull('variants_id')
                ->first();

            $harga = $produk->harga ?? 0;

            if ($item) {
                $item->jumlah += $qty;
                $item->subtotal = $item->jumlah * $item->harga_per_item;
                $item->save();
            } else {
                CartItem::create([
                    'carts_id'       => $cart->id,
                    'produks_id'     => $produk->id,
                    'variants_id'    => null,
                    'jumlah'         => $qty,
                    'harga_per_item' => $harga,
                    'subtotal'       => $harga * $qty,
                ]);
            }
        } else {
            foreach ($variantIds as $variantId) {
                $variant = Variant::findOrFail($variantId);
                $harga = ($produk->harga ?? 0) + ($variant->harga_variant ?? 0);

                $item = CartItem::where('carts_id', $cart->id)
                    ->where('produks_id', $produk->id)
                    ->where('variants_id', $variantId)
                    ->first();

                if ($item) {
                    $item->jumlah += $qty;
                    $item->subtotal = $item->jumlah * $item->harga_per_item;
                    $item->save();
                } else {
                    CartItem::create([
                        'carts_id'       => $cart->id,
                        'produks_id'     => $produk->id,
                        'variants_id'    => $variantId,
                        'jumlah'         => $qty,
                        'harga_per_item' => $harga,
                        'subtotal'       => $harga * $qty,
                    ]);
                }
            }
        }

        return back()->with('success', 'Menu berhasil ditambahkan ke keranjang');
    }

    public function update(Request $request)
    {
        $customer = $this->resolveCustomer($request);
        if (!$customer) return $this->redirectUnauthorized();

        $validated = $request->validate([
            'cart_item_id' => 'required|integer|exists:cart_items,id',
            'quantity'     => 'required|integer|min:1',
            'token'        => 'nullable|string',
        ]);

        $cart = Cart::where('customers_id', $customer->id)->firstOrFail();

        $item = CartItem::where('id', $validated['cart_item_id'])
            ->where('carts_id', $cart->id)
            ->firstOrFail();

        $item->jumlah = max(1, $validated['quantity']);
        $item->subtotal = $item->jumlah * $item->harga_per_item;
        $item->save();

        return back()->with('success', 'Jumlah item berhasil diperbarui');
    }

    public function remove(Request $request)
    {
        $customer = $this->resolveCustomer($request);
        if (!$customer) return $this->redirectUnauthorized();

        $validated = $request->validate([
            'cart_item_id' => 'required|integer|exists:cart_items,id',
            'token'        => 'nullable|string',
        ]);

        $cart = Cart::where('customers_id', $customer->id)->firstOrFail();

        $item = CartItem::where('id', $validated['cart_item_id'])
            ->where('carts_id', $cart->id)
            ->firstOrFail();

        $item->delete();

        return back()->with('success', 'Item berhasil dihapus');
    }

    public function checkout(Request $request)
    {
        $customer = $this->resolveCustomer($request);
        if (!$customer) return $this->redirectUnauthorized();

        $validated = $request->validate([
            'order_type'         => 'required|in:Dine In,Takeaway,Delivery',
            'metode_pembayaran'  => 'required|in:cash,qris',
            'alamat'             => 'nullable|string',
            'catatan'            => 'nullable|string',
            'token'              => 'nullable|string',
        ]);

        if ($validated['order_type'] === 'Delivery' && empty($validated['alamat'])) {
            return back()->withInput()->withErrors(['alamat' => 'Alamat wajib diisi untuk Delivery.']);
        }

        $cart = Cart::where('customers_id', $customer->id)->first();
        if (!$cart) return back()->withErrors(['cart' => 'Keranjang kosong']);

        $cartItems = CartItem::with(['produk', 'variant'])
            ->where('carts_id', $cart->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->withErrors(['cart' => 'Keranjang kosong']);
        }

        DB::beginTransaction();
        try {
            $grouped = $cartItems->groupBy(fn($item) => optional($item->produk)->tenants_id);

            foreach ($grouped as $tenantId => $items) {
                if (!$tenantId) throw new \Exception('Produk tidak memiliki tenant.');

                $total = $items->sum('subtotal');
                $totalProduk = $items->sum('jumlah');

                $order = Order::create([
                    'customers_id'  => $customer->id,
                    'order_tanggal' => now(),
                    'order_type'    => $validated['order_type'],
                    'total_produk'  => $totalProduk,
                    'total_harga'   => $total,
                    'order_status'  => 'pending',
                ]);

                foreach ($items as $item) {
                    DetailOrder::create([
                        'orders_id'   => $order->id,
                        'produks_id'  => $item->produks_id,
                        'jumlah'      => $item->jumlah,
                        'total_harga' => $item->subtotal,
                    ]);
                }

                Transaksi::create([
                    'orders_id'         => $order->id,
                    'metode_pembayaran' => $validated['metode_pembayaran'],
                    'status_pembayaran' => 'pending',
                    'jumlah_bayar'      => $total,
                    'waktu_bayar'       => null,
                    'reference_payment' => $validated['metode_pembayaran'] === 'qris'
                        ? 'QR-' . strtoupper(Str::random(10))
                        : null,
                ]);

                if ($validated['order_type'] === 'Delivery') {
                    Delivery::create([
                        'orders_id'       => $order->id,
                        'alamat'          => $validated['alamat'],
                        'catatan'         => $validated['catatan'] ?? null,
                        'status_delivery' => 'pending',
                    ]);
                }
            }

            CartItem::where('carts_id', $cart->id)->delete();

            DB::commit();

            return redirect()->route('transactions.list_transaction', [
                'token' => $request->input('token') ?? $request->query('token'),
            ])->with('success', 'Checkout berhasil, transaksi dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['checkout' => $e->getMessage()]);
        }
    }
}