<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use App\Notifications\OrderMasukNotification;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Tenants;
use App\Models\Penjual;
use App\Models\Produk;
use App\Models\Customers;
use App\Models\Order;
use App\Models\DetailOrder;
use App\Models\Transaksi;
use App\Models\Delivery;
use App\Models\Variant;

class CartController extends Controller
{
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
    if (!$user || $user->role !== 'customer') {
        // Langsung redirect dengan pesan error
        return redirect('/customer/profile')->withErrors([
            'profile' => 'Silakan lengkapi profil terlebih dahulu sebelum melakukan transaksi.'
        ]);
    }

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
        $catatanMenu = $request->input('catatan_menu');
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
                $item->catatan_menu = $catatanMenu;
                $item->save();
            } else {
                CartItem::create([
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
                    CartItem::create([
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

        return back()->with('success', 'Menu berhasil ditambahkan ke keranjang');
    }

    public function update(Request $request)
{
    $customer = $this->resolveCustomer($request);
    if (!$customer) return $this->redirectUnauthorized();

    $validated = $request->validate([
        'cart_item_id' => 'required|integer|exists:cart_items,id',
        'quantity'     => 'nullable|integer|min:1',
        'catatan_menu' => 'nullable|array',
        'catatan_menu.*' => 'nullable|string',
        'update_catatan_menu' => 'nullable'
    ]);

    $cart = Cart::where('customers_id', $customer->id)->firstOrFail();
    $item = CartItem::where('id', $validated['cart_item_id'])
        ->where('carts_id', $cart->id)
        ->firstOrFail();

    // Update jumlah jika ada
    if ($request->filled('quantity')) {
        $item->jumlah = max(1, $request->quantity);
        $item->subtotal = $item->jumlah * $item->harga_per_item;
    }

    // Update catatan_menu spesifik
    if ($request->filled('update_catatan_menu') && $request->has('catatan_menu')) {
        $catatanArr = $request->catatan_menu; // array [cart_item_id => value]
        $item->catatan_menu = $catatanArr[$item->id] ?? null;
    }

    $item->save();

    return back()->with('success', 'Item berhasil diperbarui');
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
            'nomor_meja'         => 'nullable|string|max:32',
            'alamat'             => 'nullable|string',
            'catatan_menu'       => 'nullable|array',
            'catatan_menu.*'     => 'nullable|string',
            'token'              => 'nullable|string',
        ]);

        $cart = Cart::where('customers_id', $customer->id)->first();
        if (!$cart) return back()->withErrors(['cart' => 'Keranjang kosong']);

        $cartItems = CartItem::with(['produk', 'variant'])
            ->where('carts_id', $cart->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->withErrors(['cart' => 'Keranjang kosong']);
        }

        // Validasi stok
        foreach ($cartItems as $item) {
            $produk = $item->produk;
            if (!$produk) {
                return back()->withErrors(['produk' => 'Produk tidak ditemukan.']);
            }
            if ($produk->stok < $item->jumlah) {
                return back()->withErrors([
                    'produk' => "Stok untuk '{$produk->nama}' tidak cukup. Sisa: {$produk->stok}, dipesan: {$item->jumlah}."
                ]);
            }
        }

        $lastOrderId = null;

        DB::beginTransaction();
        try {
            $grouped = $cartItems->groupBy(fn($item) => optional($item->produk)->tenants_id);

            foreach ($grouped as $tenantId => $items) {
                if (!$tenantId) throw new \Exception('Produk tidak memiliki tenant.');

                $total = $items->sum('subtotal');
                $totalProduk = $items->sum('jumlah');

                $nomorMeja = null;
                if ($validated['order_type'] === 'Dine In') {
                    $nomorMeja = $validated['nomor_meja']
                        ?? $items->firstWhere('nomor_meja', '!=', null)?->nomor_meja
                        ?? null;
                }

                $order = Order::create([
                    'customers_id'  => $customer->id,
                    'order_tanggal' => now(),
                    'order_type'    => $validated['order_type'],
                    'total_produk'  => $totalProduk,
                    'total_harga'   => $total,
                    'order_status'  => 'pending',
                    'nomor_meja'    => $nomorMeja,
                ]);
                $tenant = Tenants::find($tenantId);
                $penjual = $tenant?->penjual;

                if ($penjual && $penjual->user) {
                    $penjual->user->notify(
                        new \App\Notifications\OrderMasukNotification($order)
                    );
                }

                $lastOrderId = $order->id;

                foreach ($items as $item) {
                    $catatan = $validated['catatan_menu'][$item->id] ?? null;
                    DetailOrder::create([
                        'orders_id'    => $order->id,
                        'produks_id'   => $item->produks_id,
                        'variants_id'  => $item->variants_id ?? null,
                        'jumlah'       => $item->jumlah,
                        'total_harga'  => $item->subtotal,
                        'catatan_menu' => $catatan,
                    ]);
                    $produk = Produk::find($item->produks_id);
                    if ($produk) {
                        $produk->stok = max(0, $produk->stok - $item->jumlah);
                        $produk->save();
                    }
                }

                $transaksiData = [
                    'orders_id'         => $order->id,
                    'metode_pembayaran' => $validated['metode_pembayaran'],
                    'status_pembayaran' => $validated['metode_pembayaran'] === 'cash' ? 'paid' : 'pending',
                    'jumlah_bayar'      => $total,
                    'waktu_bayar'       => $validated['metode_pembayaran'] === 'cash' ? now() : null,
                    'reference_payment' => null, // Untuk Xendit: isi di PaymentController
                    'qris_url'          => null, // Xendit URL QRIS
                ];

                $transaksi = Transaksi::create($transaksiData);

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

            if ($validated['metode_pembayaran'] === 'cash') {
                return redirect()->route('orders.history.show', [
                    'order' => $lastOrderId,
                    'token' => $request->input('token') ?? $request->query('token')
                ]);
            } else { // QRIS via Xendit: redirect ke PaymentController
                return redirect()->route('customer.payment.qris', [
                    'order_id' => $lastOrderId,
                    'token' => $request->input('token') ?? $request->query('token'),
                ]);
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['checkout' => 'Checkout error: '.$e->getMessage()]);
        }
    }


public function showQris($order_id)
{
    $transaksi = Transaksi::where('orders_id', $order_id)->firstOrFail();
    $order = Order::findOrFail($order_id);
    return view('customer.payment.qris', [
        'qris_url'    => $transaksi->qris_url,
        'total_harga' => $order->total_harga,
        'order_id'    => $order->id,
    ]);
}
}