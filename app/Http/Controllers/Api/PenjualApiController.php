<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Barryvdh\DomPDF\Facade\Pdf;


use App\Models\Produk;
use App\Models\Tenants;
use App\Models\Penjual;
use App\Models\Customers;
use App\Models\Transaksi;

class PenjualApiController extends Controller
{
    // ================= AUTH =================

    private function resolveUser(Request $request)
    {
        $user = $request->user() ?? auth()->user();
        if ($user) return $user;

        $plainTextToken = $request->bearerToken()
            ?? $request->query('token')
            ?? $request->input('token');

        if (!$plainTextToken) return null;

        $accessToken = PersonalAccessToken::findToken($plainTextToken);
        return $accessToken?->tokenable;
    }

    private function requirePenjual(Request $request)
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return [null, response()->json(['success' => false, 'message' => 'Unauthorized'], 401)];
        }

        if (strtolower((string) ($user->role ?? '')) !== 'penjual') {
            return [null, response()->json(['success' => false, 'message' => 'Forbidden: role bukan penjual'], 403)];
        }

        $penjual = $user->penjual ?? Penjual::where('users_id', $user->id)->first();

        if (!$penjual) {
            return [null, response()->json(['success' => false, 'message' => 'Penjual tidak ditemukan'], 404)];
        }

        return [[
            'user' => $user,
            'penjual' => $penjual
        ], null];
    }

    // ================= DASHBOARD =================

    /**
     * GET /api/penjual/dashboard
     */
    public function dashboard(Request $request)
    {
        [$ctx, $err] = $this->requirePenjual($request);
        if ($err) return $err;

        $user = $ctx['user'];
        $penjual = $ctx['penjual'];

        $penjualId = $penjual->id;
        $tenantId  = optional($penjual->tenant)->id;

        $jumlahProduk = $tenantId ? Produk::where('tenants_id', $tenantId)->count() : 0;

        // order pending khusus tenant
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
                })
                ->count('t.id');

            $transaksiDibatalkan = (clone $transaksiBase)
                ->where(function ($q) {
                    $q->where('t.status_pembayaran', 'failed')
                      ->orWhere('o.order_status', 'failed')
                      ->orWhere('o.order_status', 'cancelled');
                })
                ->count('t.id');

            $transaksiSelesai = (clone $transaksiBase)
                ->where(function ($q) {
                    $q->where('t.status_pembayaran', 'paid')
                      ->orWhere('o.order_status', 'done')
                      ->orWhere('o.order_status', 'completed')
                      ->orWhere('o.order_status', 'selesai'); // tambahan biar nyambung sama status kamu
                })
                ->count('t.id');

            $produkDalamPengiriman = (clone $transaksiBase)
                ->where(function ($q) {
                    $q->where('o.order_status', 'delivered')
                      ->orWhere('o.order_status', 'shipped')
                      ->orWhere('o.order_status', 'on_delivery');
                })
                ->count('t.id');

            $totalPendapatan = (clone $transaksiBase)
                ->where(function ($q) {
                    $q->where('t.status_pembayaran', 'paid')
                      ->orWhere('o.order_status', 'selesai');
                })
                ->sum('o.total_harga');
        } catch (\Throwable $e) {
            Log::error('[PenjualApi] Dashboard query error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        $jumlahCustomer = Customers::count();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard penjual berhasil diambil',
            'data' => [
                'penjual' => [
                    'id' => $penjual->id,
                    'username' => $user->username ?? null,
                ],
                'statistik' => [
                    'jumlah_produk' => $jumlahProduk,
                    'order_pending' => $orderPending,
                    'transaksi_pending' => $transaksiPending,
                    'transaksi_dibatalkan' => $transaksiDibatalkan,
                    'transaksi_selesai' => $transaksiSelesai,
                    'produk_dikirim' => $produkDalamPengiriman,
                    'total_pendapatan' => (float) $totalPendapatan,
                    'jumlah_customer' => $jumlahCustomer,
                ]
            ]
        ], 200);
    }

    // ================= NOTIFICATIONS =================

    /**
     * GET /api/penjual/notifications
     */
    public function notifications(Request $request)
    {
        [$ctx, $err] = $this->requirePenjual($request);
        if ($err) return $err;

        $user = $ctx['user'];

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $user->unreadNotifications
            ]
        ], 200);
    }

    // ================= PROFILE GET =================

    /**
     * GET /api/penjual/profile
     */
    public function profile(Request $request)
    {
        [$ctx, $err] = $this->requirePenjual($request);
        if ($err) return $err;

        $user = $ctx['user'];

        $penjual = Penjual::with('tenant')->firstOrCreate(
            ['users_id' => $user->id],
            ['nama_lengkap' => $user->username ?? '', 'kontak' => null, 'gender' => null, 'status' => 'aktif']
        );

        return response()->json([
            'success' => true,
            'message' => 'Profile penjual berhasil diambil',
            'data' => [
                'user' => $user,
                'penjual' => $penjual
            ]
        ], 200);
    }

    // ================= PROFILE UPDATE =================

    /**
     * POST /api/penjual/profile/update
     * Content-Type: multipart/form-data (kalau upload foto)
     */
    public function profileUpdate(Request $request)
    {
        [$ctx, $err] = $this->requirePenjual($request);
        if ($err) return $err;

        $user = $ctx['user'];

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
        ]);

        $penjual = Penjual::with('tenant')->firstOrCreate(
            ['users_id' => $user->id],
            ['nama_lengkap' => '', 'kontak' => '', 'gender' => '', 'status' => 'aktif']
        );

        DB::beginTransaction();
        try {
            $user->username = $request->username;

            if ($request->filled('new_password')) {
                if (!$request->filled('current_password') || !Hash::check($request->current_password, $user->password)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Password saat ini salah.'
                    ], 422);
                }
                $user->password = Hash::make($request->new_password);
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

            $tenant->status_delivery = $request->has('status_delivery')
                ? $request->status_delivery
                : 0;

            if ($request->hasFile('foto_tenant')) {
                if ($tenant->foto_tenant && Storage::disk('public')->exists($tenant->foto_tenant)) {
                    Storage::disk('public')->delete($tenant->foto_tenant);
                }
                $tenant->foto_tenant = $request->file('foto_tenant')->store('tenants', 'public');
            }

            $tenant->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diupdate.',
                'data' => [
                    'user' => $user->fresh(),
                    'penjual' => $penjual->fresh(['tenant']),
                ]
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    // ================= PROFILE DESTROY =================

    /**
     * DELETE /api/penjual/profile
     */
    public function profileDestroy(Request $request)
    {
        [$ctx, $err] = $this->requirePenjual($request);
        if ($err) return $err;

        $user = $ctx['user'];

        $penjual = Penjual::with('tenant')->where('users_id', $user->id)->first();
        if (!$penjual) {
            return response()->json([
                'success' => false,
                'message' => 'Profile penjual tidak ditemukan'
            ], 404);
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

            return response()->json([
                'success' => true,
                'message' => 'Profile penjual berhasil dihapus'
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal hapus profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================= LAPORAN =================

    /**
     * GET /api/penjual/laporan?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
     */
    public function laporan(Request $request)
    {
        [$ctx, $err] = $this->requirePenjual($request);
        if ($err) return $err;

        $start = $request->query('start_date');
        $end   = $request->query('end_date');

        $query = Transaksi::with('order.customer')
            ->where('status_pembayaran', 'paid');

        if ($start && $end) {
            $query->whereHas('order', function ($q) use ($start, $end) {
                $q->whereBetween('order_tanggal', [
                    Carbon::parse($start)->startOfDay(),
                    Carbon::parse($end)->endOfDay(),
                ]);
            });
        }

        $transaksis = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'transaksis' => $transaksis,
                'total_pendapatan' => (float) $transaksis->sum('jumlah_bayar'),
                'total_transaksi' => (int) $transaksis->count(),
                'filters' => [
                    'start_date' => $start,
                    'end_date' => $end,
                ]
            ]
        ], 200);
    }

    /**
     * GET /api/penjual/laporan/{id}
     */
    public function detailLaporan($id, Request $request)
    {
        [$ctx, $err] = $this->requirePenjual($request);
        if ($err) return $err;

        $transaksi = Transaksi::with([
            'order.customer',
            'order.details.produk',
            'order.details.variant',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $transaksi
        ], 200);
    }
    public function exportPdf(Request $request)
{
    [$ctx, $err] = $this->requirePenjual($request);
    if ($err) return $err;

    $start = $request->query('start_date');
    $end   = $request->query('end_date');

    $query = Transaksi::with('order.customer')
        ->where('status_pembayaran', 'paid');

    if ($start && $end) {
        $query->whereBetween('waktu_bayar', [
            Carbon::parse($start)->startOfDay(),
            Carbon::parse($end)->endOfDay(),
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

    // Return file PDF dari API
    return response($pdf->output(), 200)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'attachment; filename="laporan-penjualan.pdf"');
}
}