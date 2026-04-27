<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Tenants;
use App\Models\Penjual;
use App\Models\Customers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Laravel\Sanctum\PersonalAccessToken;

class PenjualController extends Controller
{
    private function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson() || $request->wantsJson();
    }

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
        $user = $this->resolveUser($request);

        if (!$user) return $this->unauthorizedResponse($request);
        if (strtolower((string) $user->role) !== 'penjual') return $this->forbiddenRoleResponse($request);

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

        // --- Tambahkan query order pending khusus ---
        $orderPending = 0;
        if ($tenantId) {
            $orderPending = DB::table('orders')
                ->join('detailorders', 'orders.id', '=', 'detailorders.orders_id')
                ->join('produks', 'detailorders.produks_id', '=', 'produks.id')
                ->where('produks.tenants_id', $tenantId)
                ->where('orders.order_status', 'pending')
                ->distinct()
                ->count('orders.id');
        }
        // --------------------------------------------

        $transaksiPending = 0;
        $transaksiDibatalkan = 0;
        $transaksiSelesai = 0;
        $produkDalamPengiriman = 0;
        $totalPendapatan = 0;

        try {
            $transaksiBase = DB::table('transaksis as t')
                ->join('orders as o', 'o.id', '=', 't.orders_id')
                ->join('detailorders as d', 'd.orders_id', '=', 'o.id')
                ->join('produks as p', 'p.id', '=', 'd.produks_id')
                ->join('tenants as tn', 'tn.id', '=', 'p.tenants_id')
                ->where('tn.penjuals_id', $penjualId)
                ->select('t.id', 't.status_pembayaran', 'o.order_status', 'o.total_harga')
                ->distinct();

            $transaksiPending = (clone $transaksiBase)
                ->where(function ($q) {
                    $q->where('t.status_pembayaran', 'pending')
                      ->orWhere('o.order_status', 'pending');
                })->count('t.id');

            $transaksiDibatalkan = (clone $transaksiBase)
                ->where(function ($q) {
                    $q->where('t.status_pembayaran', 'failed')
                      ->orWhere('o.order_status', 'failed')
                      ->orWhere('o.order_status', 'cancelled');
                })->count('t.id');

            $transaksiSelesai = (clone $transaksiBase)
                ->where(function ($q) {
                    $q->where('t.status_pembayaran', 'paid')
                      ->orWhere('o.order_status', 'done')
                      ->orWhere('o.order_status', 'completed');
                })->count('t.id');

            $produkDalamPengiriman = (clone $transaksiBase)
                ->where(function ($q) {
                    $q->where('o.order_status', 'delivered')
                      ->orWhere('o.order_status', 'shipped')
                      ->orWhere('o.order_status', 'on_delivery');
                })->count('t.id');

            $totalPendapatan = (clone $transaksiBase)
                ->where(function ($q) {
                    $q->where('t.status_pembayaran', 'paid')
                      ->orWhere('o.order_status', 'selesai')
                      ->orWhere('o.order_status', 'selesai');
                })->sum('o.total_harga');
        } catch (\Throwable $e) {
            Log::error('Dashboard penjual query error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

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
                        'order_pending' => $orderPending, // <-- Tambah field ini!
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
    public function notifications(Request $request)
{
    $plainTextToken = $request->bearerToken();

    if (!$plainTextToken) {
        return response()->json([
            'notifications' => []
        ]);
    }

    $accessToken = PersonalAccessToken::findToken($plainTextToken);
    $user = $accessToken?->tokenable;

    if (!$user) {
        return response()->json([
            'notifications' => []
        ]);
    }

    return response()->json([
        'notifications' => $user->unreadNotifications
    ]);
}

    public function profile(Request $request)
    {
        $user = $this->resolveUser($request);

        if (!$user) return $this->unauthorizedResponse($request);
        if (strtolower((string) $user->role) !== 'penjual') return $this->forbiddenRoleResponse($request);

        $penjual = Penjual::with('tenant')->firstOrCreate(
            ['users_id' => $user->id],
            ['nama_lengkap' => $user->username ?? '', 'kontak' => null, 'gender' => null, 'status' => 'aktif']
        );

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => 'Profile penjual berhasil diambil',
                'data' => ['penjual' => $penjual, 'user' => $user]
            ], 200);
        }

        return view('penjual.profile.index', compact('penjual', 'user'));
    }

    public function profileEdit(Request $request)
    {
        $user = $this->resolveUser($request);

        if (!$user) return $this->unauthorizedResponse($request);
        if (strtolower((string) $user->role) !== 'penjual') return $this->forbiddenRoleResponse($request);

        $penjual = Penjual::with('tenant')->firstOrCreate(
            ['users_id' => $user->id],
            ['nama_lengkap' => '', 'kontak' => '', 'gender' => '', 'status' => 'aktif']
        );

        return view('penjual.profile.edit', compact('penjual', 'user'));
    }

    public function profileUpdate(Request $request)
{
    $user = $this->resolveUser($request);

    if (!$user) {
        return back()->withInput()->withErrors(['auth' => 'Session/token tidak valid, silakan login ulang.']);
    }
    if (strtolower((string) $user->role) !== 'penjual') {
        return back()->withInput()->withErrors(['auth' => 'Akses ditolak.']);
    }

    $request->validate([
        'username'      => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
        'nama_lengkap'  => 'required|string|max:255',
        'kontak'        => 'nullable|string|max:50',
        'gender'        => 'nullable|in:Laki-Laki,Perempuan',
        'status'        => 'required|string|max:20',
        'tenant_name'   => 'required|string|max:255',
        'desk_tenant'   => 'nullable|string|max:255',
        'kantin'        => 'required|in:1,2',
        'no_tenant'     => 'nullable|string|max:50',
        'foto_tenant'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'status_delivery' => 'nullable|in:0,1',
        'current_password' => 'nullable|string',
        'new_password'     => 'nullable|string|min:6|confirmed',
        'token'            => 'nullable|string',
    ]);

    $penjual = Penjual::with('tenant')->firstOrCreate(
        ['users_id' => $user->id],
        ['nama_lengkap' => '', 'kontak' => '', 'gender' => '', 'status' => 'aktif']
    );

    \DB::beginTransaction();
    try {
        $user->username = $request->username;

        if ($request->filled('new_password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                \DB::rollBack();
                return back()->withInput()->withErrors(['current_password' => 'Password saat ini salah.']);
            }
            $user->password = \Hash::make($request->new_password);
        }

        $user->save();

        $penjual->update([
            'nama_lengkap' => $request->nama_lengkap,
            'kontak'       => $request->kontak,
            'gender'       => $request->gender,
            'status'       => $request->status,
        ]);

        $tenant = $penjual->tenant ?: new Tenants();
        $tenant->penjuals_id = $penjual->id;
        $tenant->tenant_name = $request->tenant_name;
        $tenant->desk_tenant = $request->desk_tenant ?? null;
        $tenant->kantin      = $request->kantin;
        $tenant->no_tenant   = $request->no_tenant;

        // --- TAMBAHKAN INI ---
        $tenant->status_delivery = $request->has('status_delivery') ? $request->status_delivery : 0;
        // ---------------------

        if ($request->hasFile('foto_tenant')) {
            if ($tenant->foto_tenant && \Storage::disk('public')->exists($tenant->foto_tenant)) {
                \Storage::disk('public')->delete($tenant->foto_tenant);
            }
            $tenant->foto_tenant = $request->file('foto_tenant')->store('tenants', 'public');
        }

        $tenant->save();

        \DB::commit();

        return redirect()->route('penjual.profile.edit', [
            'token' => $request->query('token') ?? $request->input('token')
        ])->with('success', 'Profile berhasil diupdate.');
    } catch (\Throwable $e) {
        \DB::rollBack();
        return back()->withInput()->with('error', 'Gagal update profile: ' . $e->getMessage());
    }
}

    public function profileDestroy(Request $request)
    {
        $user = $this->resolveUser($request);

        if (!$user) return $this->unauthorizedResponse($request);
        if (strtolower((string) $user->role) !== 'penjual') return $this->forbiddenRoleResponse($request);

        $penjual = Penjual::with('tenant')->where('users_id', $user->id)->first();
        if (!$penjual) {
            if ($this->isApiRequest($request)) return response()->json(['message' => 'Profile penjual tidak ditemukan'], 404);
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

            return redirect()->route('penjual.homepenjual', [
                'token' => $request->query('token') ?? $request->input('token')
            ])->with('success', 'Profile berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($this->isApiRequest($request)) {
                return response()->json(['message' => 'Gagal hapus profile', 'error' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Gagal hapus profile: ' . $e->getMessage());
        }
    }
    public function laporan(Request $request)
{
    $start = $request->start_date;
    $end   = $request->end_date;

    $query = \App\Models\Transaksi::with('order.customer')
        ->where('status_pembayaran', 'paid');

    // ✅ FILTER pakai order_tanggal
    if ($start && $end) {
        $query->whereHas('order', function ($q) use ($start, $end) {
            $q->whereBetween('order_tanggal', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay()
            ]);
        });
    }

    $transaksis = $query->latest()->get();

    $totalPendapatan = $transaksis->sum('jumlah_bayar');
    $totalTransaksi  = $transaksis->count();

    return view('penjual.laporan.index', compact(
        'transaksis',
        'totalPendapatan',
        'totalTransaksi'
    ));
}
public function detailLaporan($id)
{
    $transaksi = \App\Models\Transaksi::with([
        'order.customer',
        'order.details.produk',
        'order.details.variant' 
    ])->findOrFail($id);

    return view('penjual.laporan.detail', compact('transaksi'));
}
public function exportPdf(Request $request)
{
    $query = \App\Models\Transaksi::with('order.customer')
        ->where('status_pembayaran', 'paid');

    // FILTER TANGGAL
    if ($request->start_date && $request->end_date) {
        $query->whereBetween('waktu_bayar', [
            $request->start_date,
            $request->end_date
        ]);
    }

    $transaksis = $query->get();

    $totalPendapatan = $transaksis->sum('jumlah_bayar');
    $totalTransaksi  = $transaksis->count();

    $pdf = Pdf::loadView('penjual.laporan.pdf', compact(
        'transaksis',
        'totalPendapatan',
        'totalTransaksi',
        'request'
    ));

    return $pdf->download('laporan-penjualan.pdf');
}
}
