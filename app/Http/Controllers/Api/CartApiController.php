<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

class CartApiController extends Controller
{
    // ================= AUTH =================

    private function resolveUser(Request $request)
    {
        $user = $request->user() ?? auth()->user();

        if ($user) {
            return $user;
        }

        $plainTextToken = $request->bearerToken()
            ?? $request->input('token')
            ?? $request->query('token');

        if (!$plainTextToken) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($plainTextToken);

        return $accessToken?->tokenable;
    }

    private function resolveCustomer(Request $request): ?Customers
    {
        $user = $this->resolveUser($request);

        if (!$user || $user->role !== 'customer') {
            return null;
        }

        return Customers::firstOrCreate(
            ['users_id' => $user->id],
            ['users_id' => $user->id]
        );
    }

    private function resolveCart(Customers $customer): Cart
    {
        return Cart::firstOrCreate([
            'customers_id' => $customer->id
        ]);
    }

    // ================= GET CART =================

    public function index(Request $request)
    {
        $customer = $this->resolveCustomer($request);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $cart = Cart::where('customers_id', $customer->id)->first();

        if (!$cart) {
            return response()->json([
                'success' => true,
                'data' => [],
                'total_item' => 0,
                'total_harga' => 0
            ]);
        }

        $items = CartItem::with([
            'produk',
            'variant'
        ])
        ->where('carts_id', $cart->id)
        ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
            'total_item' => $items->sum('jumlah'),
            'total_harga' => $items->sum('subtotal')
        ]);
    }

    // ================= ADD CART =================

    public function add(Request $request)
    {
        $customer = $this->resolveCustomer($request);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'product_id'    => 'required|integer|exists:produks,id',
            'qty'           => 'nullable|integer|min:1',
            'variant_ids'   => 'nullable|array',
            'variant_ids.*' => 'integer|exists:variants,id',
            'catatan_menu'  => 'nullable|string',
        ]);

        $produk = Produk::findOrFail($validated['product_id']);

        $qty = $validated['qty'] ?? 1;

        $variantIds = $validated['variant_ids'] ?? [];

        $catatanMenu = $validated['catatan_menu'] ?? null;

        $cart = $this->resolveCart($customer);

        // TANPA VARIANT
        if (empty($variantIds)) {

            $item = CartItem::where('carts_id', $cart->id)
                ->where('produks_id', $produk->id)
                ->whereNull('variants_id')
                ->first();

            $harga = $produk->harga ?? 0;

            if ($item) {

                $item->jumlah += $qty;
                $item->subtotal = $item->jumlah * $item->harga_per_item;
                $item->catatan_menu = $catatanMenu;
                $item->save();

            } else {

                $item = CartItem::create([
                    'carts_id'       => $cart->id,
                    'produks_id'     => $produk->id,
                    'variants_id'    => null,
                    'jumlah'         => $qty,
                    'harga_per_item' => $harga,
                    'subtotal'       => $harga * $qty,
                    'catatan_menu'   => $catatanMenu,
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
                    $item->catatan_menu = $catatanMenu;
                    $item->save();

                } else {

                    $item = CartItem::create([
                        'carts_id'       => $cart->id,
                        'produks_id'     => $produk->id,
                        'variants_id'    => $variantId,
                        'jumlah'         => $qty,
                        'harga_per_item' => $harga,
                        'subtotal'       => $harga * $qty,
                        'catatan_menu'   => $catatanMenu,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke cart'
        ]);
    }

    // ================= UPDATE CART =================

    public function update(Request $request)
    {
        $customer = $this->resolveCustomer($request);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'cart_item_id' => 'required|integer|exists:cart_items,id',
            'quantity'     => 'required|integer|min:1',
            'catatan_menu' => 'nullable|string',
        ]);

        $cart = Cart::where('customers_id', $customer->id)->firstOrFail();

        $item = CartItem::where('id', $validated['cart_item_id'])
            ->where('carts_id', $cart->id)
            ->firstOrFail();

        $item->jumlah = $validated['quantity'];
        $item->subtotal = $item->jumlah * $item->harga_per_item;

        if (isset($validated['catatan_menu'])) {
            $item->catatan_menu = $validated['catatan_menu'];
        }

        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Cart berhasil diupdate',
            'data' => $item
        ]);
    }

    // ================= DELETE CART =================

    public function remove(Request $request)
    {
        $customer = $this->resolveCustomer($request);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'cart_item_id' => 'required|integer|exists:cart_items,id',
        ]);

        $cart = Cart::where('customers_id', $customer->id)->firstOrFail();

        $item = CartItem::where('id', $validated['cart_item_id'])
            ->where('carts_id', $cart->id)
            ->firstOrFail();

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus'
        ]);
    }

    // ================= SUMMARY CART =================

    public function summary(Request $request)
    {
        $customer = $this->resolveCustomer($request);

        if (!$customer) {
            return response()->json([
                'count' => 0,
                'total' => 0
            ]);
        }

        $cart = Cart::where('customers_id', $customer->id)->first();

        if (!$cart) {
            return response()->json([
                'count' => 0,
                'total' => 0
            ]);
        }

        $items = CartItem::where('carts_id', $cart->id)->get();

        return response()->json([
            'count' => $items->sum('jumlah'),
            'total' => $items->sum('subtotal')
        ]);
    }

    // ================= CHECKOUT =================

    public function checkout(Request $request)
    {
        $customer = $this->resolveCustomer($request);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validated = $request->validate([
            'order_type'        => 'required|in:Dine In,Takeaway,Delivery',
            'metode_pembayaran' => 'required|in:cash,qris',
            'nomor_meja'        => 'nullable|string|max:32',
            'alamat'            => 'nullable|string',
        ]);

        $cart = Cart::where('customers_id', $customer->id)->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong'
            ], 400);
        }

        $cartItems = CartItem::with([
            'produk',
            'variant'
        ])
        ->where('carts_id', $cart->id)
        ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong'
            ], 400);
        }

        DB::beginTransaction();

        try {

            foreach ($cartItems as $item) {

                $produk = $item->produk;

                if (!$produk) {
                    throw new \Exception('Produk tidak ditemukan');
                }

                if ($produk->stok < $item->jumlah) {
                    throw new \Exception(
                        "Stok {$produk->nama} tidak cukup"
                    );
                }
            }

            $total = $cartItems->sum('subtotal');

            $order = Order::create([
                'customers_id'  => $customer->id,
                'order_tanggal' => now(),
                'order_type'    => $validated['order_type'],
                'total_produk'  => $cartItems->sum('jumlah'),
                'total_harga'   => $total,
                'order_status'  => 'pending',
                'nomor_meja'    => $validated['nomor_meja'] ?? null,
            ]);

            foreach ($cartItems as $item) {

                DetailOrder::create([
                    'orders_id'    => $order->id,
                    'produks_id'   => $item->produks_id,
                    'variants_id'  => $item->variants_id,
                    'jumlah'       => $item->jumlah,
                    'total_harga'  => $item->subtotal,
                    'catatan_menu' => $item->catatan_menu,
                ]);

                $produk = Produk::find($item->produks_id);

                if ($produk) {

                    $produk->stok = max(
                        0,
                        $produk->stok - $item->jumlah
                    );

                    $produk->save();
                }
            }

            $transaksi = Transaksi::create([
                'orders_id'         => $order->id,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => $validated['metode_pembayaran'] == 'cash'
                    ? 'paid'
                    : 'pending',
                'jumlah_bayar'      => $total,
                'waktu_bayar'       => $validated['metode_pembayaran'] == 'cash'
                    ? now()
                    : null,
            ]);

            if ($validated['order_type'] == 'Delivery') {

                Delivery::create([
                    'orders_id'       => $order->id,
                    'alamat'          => $validated['alamat'],
                    'status_delivery' => 'pending',
                ]);
            }

            CartItem::where('carts_id', $cart->id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Checkout berhasil',
                'data' => [
                    'order_id' => $order->id,
                    'total_harga' => $order->total_harga,
                    'status_pembayaran' => $transaksi->status_pembayaran,
                ]
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}