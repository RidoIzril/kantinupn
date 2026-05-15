<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Laravel\Sanctum\PersonalAccessToken;

use App\Models\Penjual;
use App\Models\Kategori;
use App\Models\Customers;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Tenants;

class SuperadminApiController extends Controller
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

    private function requireSuperadmin(Request $request)
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return [null, response()->json(['success' => false, 'message' => 'Unauthorized'], 401)];
        }

        if (strtolower((string)($user->role ?? '')) !== 'superadmin') {
            return [null, response()->json(['success' => false, 'message' => 'Forbidden: role bukan superadmin'], 403)];
        }

        return [$user, null];
    }

    // ================= DASHBOARD =================

    /**
     * GET /api/superadmin/dashboard
     */
    public function dashboard(Request $request)
    {
        [$user, $err] = $this->requireSuperadmin($request);
        if ($err) return $err;

        return response()->json([
            'success' => true,
            'data' => [
                'jumlahPenjual'   => (int) Penjual::count(),
                'jumlahCustomer'  => (int) Customers::count(),
                'jumlahKategori'  => (int) Kategori::count(),
                'jumlahTransaksi' => (int) Transaksi::count(),
            ]
        ], 200);
    }

    // ================= PENJUAL LIST =================

    /**
     * GET /api/superadmin/penjual?search=...
     */
    public function penjualIndex(Request $request)
    {
        [$user, $err] = $this->requireSuperadmin($request);
        if ($err) return $err;

        $keyword = $request->query('search');

        $data = DB::table('penjuals as p')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->leftJoin('tenants as t', 't.penjuals_id', '=', 'p.id')
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($qq) use ($keyword) {
                    $qq->where('u.username', 'like', "%{$keyword}%")
                        ->orWhere('p.nama_lengkap', 'like', "%{$keyword}%")
                        ->orWhere('p.kontak', 'like', "%{$keyword}%")
                        ->orWhere('t.tenant_name', 'like', "%{$keyword}%")
                        ->orWhere('t.no_tenant', 'like', "%{$keyword}%");
                });
            })
            ->select(
                'p.id',
                'u.id as users_id',
                'u.username',
                'p.nama_lengkap',
                'p.kontak',
                'p.gender',
                'p.status',
                't.id as tenant_id',
                't.tenant_name',
                't.desk_tenant',
                't.kantin',
                't.no_tenant',
                't.foto_tenant'
            )
            ->orderByDesc('p.id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    // ================= PENJUAL CREATE =================

    /**
     * POST /api/superadmin/penjual
     * multipart/form-data kalau pakai foto_tenant (file)
     */
    public function penjualStore(Request $request)
    {
        [$user, $err] = $this->requireSuperadmin($request);
        if ($err) return $err;

        $data = $request->validate([
            'username'     => 'required|string|max:100|unique:users,username',
            'password'     => 'required|string|min:6',
            'nama_lengkap' => 'required|string|max:255',
            'kontak'       => 'required|string|max:30',
            'gender'       => 'required|in:Laki-Laki,Perempuan',
            'status'       => 'required|in:aktif,nonaktif',
            'tenant_name'  => 'required|string|max:255',
            'desk_tenant'  => 'nullable|string|max:255',
            'kantin'       => 'required|in:1,2',
            'no_tenant'    => 'nullable|string|max:100',
            'foto_tenant'  => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $newUser = User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'role'     => 'penjual',
            ]);

            $penjual = Penjual::create([
                'users_id'     => $newUser->id,
                'nama_lengkap' => $data['nama_lengkap'],
                'kontak'       => $data['kontak'],
                'gender'       => $data['gender'],
                'status'       => $data['status'],
            ]);

            $fotoPath = null;
            if ($request->hasFile('foto_tenant')) {
                $fotoPath = $request->file('foto_tenant')->store('foto_tenant', 'public');
            }

            $tenant = Tenants::create([
                'penjuals_id' => $penjual->id,
                'tenant_name' => $data['tenant_name'],
                'desk_tenant' => $data['desk_tenant'] ?? null,
                'kantin'      => $data['kantin'],
                'no_tenant'   => $data['no_tenant'] ?? null,
                'foto_tenant' => $fotoPath,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Akun penjual berhasil ditambahkan',
                'data' => [
                    'user' => $newUser,
                    'penjual' => $penjual,
                    'tenant' => $tenant,
                ]
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat penjual',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================= PENJUAL UPDATE =================

    /**
     * PUT /api/superadmin/penjual/{id}
     * id = penjual_id
     */
    public function penjualUpdate(Request $request, $id)
    {
        [$user, $err] = $this->requireSuperadmin($request);
        if ($err) return $err;

        $penjual = Penjual::findOrFail($id);
        $userRow = User::findOrFail($penjual->users_id);

        $data = $request->validate([
            'username'     => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($userRow->id)],
            'password'     => 'nullable|string|min:6',
            'nama_lengkap' => 'required|string|max:255',
            'kontak'       => 'required|string|max:30',
            'gender'       => 'required|in:Laki-Laki,Perempuan',
            'status'       => 'required|in:aktif,nonaktif',
            'tenant_name'  => 'required|string|max:255',
            'desk_tenant'  => 'nullable|string|max:255',
            'kantin'       => 'required|in:1,2',
            'no_tenant'    => 'nullable|string|max:100',
            'foto_tenant'  => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $userPayload = ['username' => $data['username']];
            if (!empty($data['password'])) {
                $userPayload['password'] = Hash::make($data['password']);
            }
            $userRow->update($userPayload);

            $penjual->update([
                'nama_lengkap' => $data['nama_lengkap'],
                'kontak'       => $data['kontak'],
                'gender'       => $data['gender'],
                'status'       => $data['status'],
            ]);

            $tenant = Tenants::where('penjuals_id', $penjual->id)->first();

            $tenantPayload = [
                'tenant_name' => $data['tenant_name'],
                'desk_tenant' => $data['desk_tenant'] ?? null,
                'kantin'      => $data['kantin'],
                'no_tenant'   => $data['no_tenant'] ?? null,
            ];

            if ($request->hasFile('foto_tenant')) {
                // delete old if exists
                if ($tenant && $tenant->foto_tenant && Storage::disk('public')->exists($tenant->foto_tenant)) {
                    Storage::disk('public')->delete($tenant->foto_tenant);
                }
                $tenantPayload['foto_tenant'] = $request->file('foto_tenant')->store('foto_tenant', 'public');
            }

            if ($tenant) {
                $tenant->update($tenantPayload);
            } else {
                $tenant = Tenants::create(array_merge($tenantPayload, [
                    'penjuals_id' => $penjual->id
                ]));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data penjual berhasil diperbarui',
                'data' => [
                    'user' => $userRow->fresh(),
                    'penjual' => $penjual->fresh(),
                    'tenant' => $tenant->fresh(),
                ]
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal update penjual',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================= UPDATE STATUS =================

    /**
     * PATCH /api/superadmin/penjual/{id}/status
     */
    public function penjualUpdateStatus(Request $request, $id)
    {
        [$user, $err] = $this->requireSuperadmin($request);
        if ($err) return $err;

        $request->validate([
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $penjual = Penjual::findOrFail($id);
        $penjual->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status penjual berhasil diperbarui',
            'data' => $penjual
        ], 200);
    }

    // ================= DELETE PENJUAL =================

    /**
     * DELETE /api/superadmin/penjual/{id}
     */
    public function penjualDestroy(Request $request, $id)
    {
        [$user, $err] = $this->requireSuperadmin($request);
        if ($err) return $err;

        DB::beginTransaction();
        try {
            $penjual = Penjual::findOrFail($id);

            // delete tenant photo if exists
            $tenant = Tenants::where('penjuals_id', $penjual->id)->first();
            if ($tenant && $tenant->foto_tenant && Storage::disk('public')->exists($tenant->foto_tenant)) {
                Storage::disk('public')->delete($tenant->foto_tenant);
            }

            Tenants::where('penjuals_id', $penjual->id)->delete();
            User::where('id', $penjual->users_id)->delete();
            $penjual->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Akun penjual berhasil dihapus'
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal hapus penjual',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ================= LAPORAN JSON =================

    /**
     * GET /api/superadmin/laporan?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD&tenant_id=xx
     */
    public function laporan(Request $request)
    {
        [$user, $err] = $this->requireSuperadmin($request);
        if ($err) return $err;

        $start  = $request->query('start_date');
        $end    = $request->query('end_date');
        $tenant = $request->query('tenant_id');

        $query = Transaksi::with([
            'order.customer',
            'order.details.produk.tenant'
        ])->where('status_pembayaran', 'paid');

        if ($start && $end) {
            $query->whereHas('order', function ($q) use ($start, $end) {
                $q->whereBetween('order_tanggal', [
                    Carbon::parse($start)->startOfDay(),
                    Carbon::parse($end)->endOfDay(),
                ]);
            });
        }

        if ($tenant) {
            $query->whereHas('order.details.produk', function ($q) use ($tenant) {
                $q->where('tenants_id', $tenant);
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
                    'tenant_id' => $tenant,
                ]
            ]
        ], 200);
    }

    // ================= LAPORAN PDF (DOWNLOAD) =================

    public function exportPdf(Request $request)
    {
        [$user, $err] = $this->requireSuperadmin($request);
        if ($err) return $err;

        $query = Transaksi::with([
            'order.customer',
            'order.details.produk.tenant'
        ])->where('status_pembayaran', 'paid');

        if ($request->query('start_date') && $request->query('end_date')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->whereBetween('order_tanggal', [
                    Carbon::parse($request->query('start_date'))->startOfDay(),
                    Carbon::parse($request->query('end_date'))->endOfDay(),
                ]);
            });
        }

        if ($request->query('tenant_id')) {
            $query->whereHas('order.details.produk', function ($q) use ($request) {
                $q->where('tenants_id', $request->query('tenant_id'));
            });
        }

        $transaksis = $query->get();

        $totalPendapatan = $transaksis->sum('jumlah_bayar');
        $totalTransaksi  = $transaksis->count();

        $tenantNama = 'Semua Tenant';

        if ($request->query('tenant_id')) {
            $tenant = Tenants::find($request->query('tenant_id'));
            $tenantNama = $tenant->tenant_name ?? 'Tidak ditemukan';
        } else {
            $tenantNama = DB::table('detailorders')
                ->join('produks', 'detailorders.produks_id', '=', 'produks.id')
                ->join('tenants', 'produks.tenants_id', '=', 'tenants.id')
                ->select('tenants.tenant_name')
                ->distinct()
                ->pluck('tenant_name')
                ->implode(', ');

            if (!$tenantNama) $tenantNama = 'Semua Tenant';
        }

        $pdf = Pdf::loadView('superadmin.laporan.pdf', compact(
            'transaksis',
            'totalPendapatan',
            'totalTransaksi',
            'request',
            'tenantNama'
        ));

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="laporan-penjualan.pdf"');
    }

    // ================= CHART JSON =================

    /**
     * GET /api/superadmin/chart/penjualan-mingguan?start_date=...&end_date=...
     */
    public function chartPenjualanPerTenantMingguan(Request $request)
    {
        [$user, $err] = $this->requireSuperadmin($request);
        if ($err) return $err;

        $start = $request->query('start_date')
            ? Carbon::parse($request->query('start_date'))->startOfDay()
            : now()->subWeeks(8)->startOfDay();

        $end = $request->query('end_date')
            ? Carbon::parse($request->query('end_date'))->endOfDay()
            : now()->endOfDay();

        $rows = DB::table('orders as o')
            ->join('detailorders as d', 'd.orders_id', '=', 'o.id')
            ->join('produks as pr', 'pr.id', '=', 'd.produks_id')
            ->join('tenants as t', 't.id', '=', 'pr.tenants_id')
            ->where('o.order_status', 'selesai')
            ->whereBetween('o.order_tanggal', [$start, $end])
            ->selectRaw("
                t.id as tenant_id,
                t.tenant_name,
                YEARWEEK(o.order_tanggal, 3) as yearweek,
                SUM(d.total_harga) as total
            ")
            ->groupBy('tenant_id', 'tenant_name', 'yearweek')
            ->orderBy('yearweek')
            ->get();

        $weeks = $rows->pluck('yearweek')->unique()->values();

        $labels = $weeks->map(function ($yw) {
            $year = (int) substr((string)$yw, 0, 4);
            $week = (int) substr((string)$yw, 4, 2);
            return sprintf('%d-W%02d', $year, $week);
        });

        $datasets = $rows->groupBy('tenant_id')->map(function ($items) use ($weeks) {
            $name = $items->first()->tenant_name;

            $byWeek = $items->keyBy('yearweek')->map(fn ($r) => (float) $r->total);

            $data = $weeks->map(fn ($yw) => $byWeek[$yw] ?? 0)->values();

            return [
                'label' => $name,
                'data'  => $data,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'datasets' => $datasets,
            ]
        ], 200);
    }
}