<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Tenants;
use App\Models\Penjual;
use App\Models\Customers;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PenjualController extends Controller
{
    private function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson() || $request->wantsJson();
    }

    private function unauthorizedResponse(Request $request)
    {
        if ($this->isApiRequest($request)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return redirect()->route('login');
    }

    private function forbiddenRoleResponse(Request $request)
    {
        if ($this->isApiRequest($request)) {
            return response()->json(['message' => 'Forbidden: role bukan penjual'], 403);
        }

        return redirect()->route('login');
    }

    public function index(Request $request)
    {
        // Prioritas user dari request (auth:sanctum), fallback auth() untuk web
        $user = $request->user() ?? auth()->user();

        if (!$user) {
            return $this->unauthorizedResponse($request);
        }

        if (strtolower((string) $user->role) !== 'penjual') {
            return $this->forbiddenRoleResponse($request);
        }

        // Relasi: users -> penjual (hasOne)
        $penjual = $user->penjual;

        if (!$penjual) {
            if ($this->isApiRequest($request)) {
                return response()->json(['message' => 'Penjual tidak ditemukan'], 404);
            }
            return redirect()->route('login')->withErrors('Data penjual tidak ditemukan.');
        }

        $penjualId = $penjual->id;
        $tenantId  = optional($penjual->tenant)->id;

        $jumlahProduk = $tenantId ? Produk::where('tenants_id', $tenantId)->count() : 0;

        // =========================
        // UPDATE BAGIAN TRANSAKSI
        // =========================
        // Catatan:
        // - tabel transaksi tidak punya penjual_id
        // - filter penjual dilakukan via join:
        //   transaksi.orders_id -> order.order_id -> detailorder.order_i   d -> produk.produk_id -> tenant.tenants_id -> penjual.penjual_id
        $transaksiBase = DB::table('transaksis as t')
    ->join('orders as o', 'o.order_id', '=', 't.orders_id')
    ->join('detailorder as d', 'd.order_id', '=', 'o.order_id')
    ->join('produk as p', 'p.produk_id', '=', 'd.product_id')
    ->join('tenant as tn', 'tn.tenants_id', '=', 'p.tenants_id')
    ->where('tn.penjual_id', $penjualId)
    ->select('t.transaksis_id', 't.status_pembayaran', 'o.order_status', 'o.total_harga')
    ->distinct();

$transaksiPending = (clone $transaksiBase)
    ->where(function ($q) {
        $q->where('t.status_pembayaran', 'pending')
          ->orWhere('o.order_status', 'pending');
    })
    ->count('t.transaksis_id');

$transaksiDibatalkan = (clone $transaksiBase)
    ->where(function ($q) {
        $q->where('t.status_pembayaran', 'failed')
          ->orWhere('o.order_status', 'failed')
          ->orWhere('o.order_status', 'cancelled');
    })
    ->count('t.transaksis_id');

$transaksiSelesai = (clone $transaksiBase)
    ->where(function ($q) {
        $q->where('t.status_pembayaran', 'paid')
          ->orWhere('o.order_status', 'done')
          ->orWhere('o.order_status', 'completed');
    })
    ->count('t.transaksis_id');

$produkDalamPengiriman = (clone $transaksiBase)
    ->where(function ($q) {
        $q->where('o.order_status', 'delivered')
          ->orWhere('o.order_status', 'shipped')
          ->orWhere('o.order_status', 'on_delivery');
    })
    ->count('t.transaksis_id');

$totalPendapatan = (clone $transaksiBase)
    ->where(function ($q) {
        $q->where('t.status_pembayaran', 'paid')
          ->orWhere('o.order_status', 'done')
          ->orWhere('o.order_status', 'completed');
    })
    ->sum('o.total_harga');
        // =========================
        // END UPDATE BAGIAN TRANSAKSI
        // =========================

        $jumlahCustomer = Customers::count();

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => 'Dashboard penjual berhasil diambil',
                'data' => [
                    'penjual' => [
                        'id' => $penjual->id,
                        'username' => $user->username ?? null,
                    ],
                    'statistik' => [
                        'jumlah_produk' => $jumlahProduk,
                        'transaksi_pending' => $transaksiPending,
                        'transaksi_dibatalkan' => $transaksiDibatalkan,
                        'transaksi_selesai' => $transaksiSelesai,
                        'produk_dikirim' => $produkDalamPengiriman,
                        'total_pendapatan' => $totalPendapatan,
                        'jumlah_customer' => $jumlahCustomer,
                    ]
                ]
            ], 200);
        }

        return view('penjual.home', compact(
            'jumlahProduk',
            'transaksiPending',
            'transaksiDibatalkan',
            'transaksiSelesai',
            'produkDalamPengiriman',
            'totalPendapatan',
            'jumlahCustomer',
            'penjual'
        ));
    }

    public function profile(Request $request)
{
    $user = $request->user() ?? auth()->user();

    if (!$user) {
        return $this->unauthorizedResponse($request);
    }

    if (strtolower((string) $user->role) !== 'penjual') {
        return $this->forbiddenRoleResponse($request);
    }

    // AUTO CREATE biodata dasar jika belum ada
    $penjual = Penjual::with('tenant')->firstOrCreate(
        ['users_id' => $user->id],
        [
            'nama_lengkap' => $user->username ?? '',
            'kontak'       => null,
            'gender'       => null,
            'status'       => 'aktif',
        ]
    );

    if ($this->isApiRequest($request)) {
        return response()->json([
            'message' => 'Profile penjual berhasil diambil',
            'data'    => $penjual
        ], 200);
    }

    return view('penjual.profile.index', compact('penjual'));
}

    public function profileEdit(Request $request)
    {
        $user = $request->user() ?? auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (strtolower((string) $user->role) !== 'penjual') {
            return redirect()->route('login');
        }

        $penjual = Penjual::with('tenant')->firstOrCreate(
            ['users_id' => $user->id],
            [
                'nama_lengkap' => '',
                'kontak'       => '',
                'gender'       => '',
                'status'       => 'aktif',
            ]
        );

        return view('penjual.profile.edit', compact('penjual'));
    }

    public function profileUpdate(Request $request)
    {
        $user = $request->user() ?? auth()->user();

        if (!$user) {
            return $this->unauthorizedResponse($request);
        }

        if (strtolower((string) $user->role) !== 'penjual') {
            return $this->forbiddenRoleResponse($request);
        }

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'kontak'       => 'nullable|string|max:50',
            'gender'       => 'nullable|string|max:20',
            'status'       => 'required|string|max:20',
            'tenant_name'  => 'required|string|max:255',
            'no_tenant'    => 'nullable|string|max:50',
            'foto_tenant'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $penjual = Penjual::with('tenant')->firstOrCreate(
            ['users_id' => $user->id],
            [
                'nama_lengkap' => '',
                'kontak'       => '',
                'gender'       => '',
                'status'       => 'aktif',
            ]
        );

        DB::beginTransaction();
        try {
            $penjual->update([
                'nama_lengkap' => $request->nama_lengkap,
                'kontak'       => $request->kontak,
                'gender'       => $request->gender,
                'status'       => $request->status,
            ]);

            $tenant = $penjual->tenant ?: new Tenants();
            $tenant->penjuals_id = $penjual->id;
            $tenant->tenant_name = $request->tenant_name;
            $tenant->no_tenant   = $request->no_tenant;

            if ($request->hasFile('foto_tenant')) {
                if ($tenant->foto_tenant && Storage::disk('public')->exists($tenant->foto_tenant)) {
                    Storage::disk('public')->delete($tenant->foto_tenant);
                }
                $tenant->foto_tenant = $request->file('foto_tenant')->store('tenants', 'public');
            }

            $tenant->save();
            DB::commit();

            if ($this->isApiRequest($request)) {
                return response()->json(['message' => 'Profile penjual berhasil diupdate'], 200);
            }

            return redirect()->route('penjual.profile.show')->with('success', 'Profile berhasil diupdate.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'message' => 'Gagal update profile',
                    'error'   => $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal update profile: ' . $e->getMessage());
        }
    }

    public function profileDestroy(Request $request)
    {
        $user = $request->user() ?? auth()->user();

        if (!$user) {
            return $this->unauthorizedResponse($request);
        }

        if (strtolower((string) $user->role) !== 'penjual') {
            return $this->forbiddenRoleResponse($request);
        }

        $penjual = Penjual::with('tenant')->where('users_id', $user->id)->first();

        if (!$penjual) {
            if ($this->isApiRequest($request)) {
                return response()->json(['message' => 'Profile penjual tidak ditemukan'], 404);
            }
            return back()->with('error', 'Profile penjual tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            if ($penjual->tenant) {
                if ($penjual->tenant->foto_tenant && Storage::disk('public')->exists($penjual->tenant->foto_tenant)) {
                    Storage::disk('public')->delete($penjual->tenant->foto_tenant);
                }
                $penjual->tenant->delete();
            }

            $penjual->delete();
            DB::commit();

            if ($this->isApiRequest($request)) {
                return response()->json(['message' => 'Profile penjual berhasil dihapus'], 200);
            }

            return redirect()->route('penjual.homepenjual')->with('success', 'Profile berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'message' => 'Gagal hapus profile',
                    'error'   => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal hapus profile: ' . $e->getMessage());
        }
    }
}