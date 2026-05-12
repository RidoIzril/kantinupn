<?php

namespace App\Http\Controllers;

use App\Models\Penjual;
use App\Models\Kategori;
use App\Models\Customers;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Tenants;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperadminController extends Controller
{
    public function home()
    {
        return view('superadmin.homesuperadmin', [
            'jumlahPenjual'   => Penjual::count(),
            'jumlahCustomer'  => Customers::count(),
            'jumlahKategori'  => Kategori::count(),
            'jumlahTransaksi' => Transaksi::count(),
        ]);
    }

    public function penjual(Request $request)
    {
        $keyword = $request->search;

        $penjuals = DB::table('penjuals as p')
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
            ->select([
                'p.id',
                'p.nama_lengkap',
                'p.kontak',
                'p.gender',
                'p.status',
                'u.username',
                't.tenant_name',
                't.no_tenant',
                't.desk_tenant',
                't.kantin',
                't.foto_tenant',
            ])
            ->orderByDesc('p.id')
            ->get();

        return view('superadmin.akun.akun_penjual', compact('penjuals', 'keyword'));
    }

    public function createPenjual()
    {
        return view('superadmin.akun.tambah_penjual');
    }

    public function storePenjual(Request $request)
    {
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
            $user = User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'role'     => 'penjual',
            ]);

            $penjual = Penjual::create([
                'users_id'     => $user->id,
                'nama_lengkap' => $data['nama_lengkap'],
                'kontak'       => $data['kontak'],
                'gender'       => $data['gender'],
                'status'       => $data['status'],
            ]);

            $fotoPath = null;
            if ($request->hasFile('foto_tenant')) {
                $fotoPath = $request->file('foto_tenant')->store('foto_tenant', 'public');
            }

            Tenants::create([
                'penjuals_id' => $penjual->id,
                'tenant_name' => $data['tenant_name'],
                'desk_tenant' => $data['desk_tenant'] ?? null,
                'kantin'      => $data['kantin'],
                'no_tenant'   => $data['no_tenant'] ?? null,
                'foto_tenant' => $fotoPath,
            ]);

            DB::commit();

            return redirect()
                ->route('superadmin.penjual.index')
                ->with('success', 'Akun penjual berhasil ditambahkan');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function editPenjual($id)
    {
        $penjual = DB::table('penjuals as p')
            ->leftJoin('users as u', 'u.id', '=', 'p.users_id')
            ->leftJoin('tenants as t', 't.penjuals_id', '=', 'p.id')
            ->where('p.id', $id)
            ->select(
                'p.*',
                'u.id as user_ref_id',
                'u.username',
                't.id as tenant_id',
                't.tenant_name',
                't.desk_tenant',
                't.kantin',
                't.no_tenant',
                't.foto_tenant'
            )
            ->first();

        abort_if(!$penjual, 404);

        return view('superadmin.akun.edit_penjual', compact('penjual'));
    }

    public function updatePenjual(Request $request, $id)
    {
        $penjual = Penjual::findOrFail($id);

        $data = $request->validate([
            'username'     => 'required|string|max:100',
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
            $user = User::findOrFail($penjual->users_id);

            $request->validate([
                'username' => 'unique:users,username,' . $user->id . ',id',
            ]);

            $userPayload = ['username' => $data['username']];
            if (!empty($data['password'])) {
                $userPayload['password'] = Hash::make($data['password']);
            }
            $user->update($userPayload);

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
                $tenantPayload['foto_tenant'] = $request->file('foto_tenant')->store('foto_tenant', 'public');
            }

            if ($tenant) {
                $tenant->update($tenantPayload);
            } else {
                Tenants::create(array_merge($tenantPayload, [
                    'penjuals_id' => $penjual->id
                ]));
            }

            DB::commit();

            return redirect()
                ->route('superadmin.penjual.index')
                ->with('success', 'Data penjual berhasil diperbarui');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $penjual = Penjual::findOrFail($id);
        $penjual->update(['status' => $request->status]);

        return back()->with('success', 'Status penjual berhasil diperbarui');
    }

    public function destroyPenjual($id)
    {
        DB::beginTransaction();
        try {
            $penjual = Penjual::findOrFail($id);

            Tenants::where('penjuals_id', $penjual->id)->delete();
            User::where('id', $penjual->users_id)->delete();
            $penjual->delete();

            DB::commit();
            return back()->with('success', 'Akun penjual berhasil dihapus');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function apiIndexPenjual(Request $request)
    {
        $keyword = $request->search;

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
                'u.username',
                'p.nama_lengkap',
                'p.kontak',
                'p.gender',
                'p.status',
                't.tenant_name',
                't.desk_tenant',
                't.kantin',
                't.no_tenant',
                't.foto_tenant'
            )
            ->orderByDesc('p.id')
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function apiStorePenjual(Request $request)
    {
        return $this->storePenjual($request);
    }
    public function laporan(Request $request)
{
    $start  = $request->start_date;
    $end    = $request->end_date;
    $tenant = $request->tenant_id;

    $query = \App\Models\Transaksi::with([
        'order.customer',
        'order.details.produk.tenant' // penting 🔥
    ])
    ->where('status_pembayaran', 'paid');

    // ✅ FILTER TANGGAL (pakai order_tanggal)
    if ($start && $end) {
        $query->whereHas('order', function ($q) use ($start, $end) {
            $q->whereBetween('order_tanggal', [$start, $end]);
        });
    }

    // ✅ FILTER TENANT
    if ($tenant) {
        $query->whereHas('order.details.produk', function ($q) use ($tenant) {
            $q->where('tenants_id', $tenant);
        });
    }

    $transaksis = $query->latest()->get();

    $totalPendapatan = $transaksis->sum('jumlah_bayar');
    $totalTransaksi  = $transaksis->count();

    // ambil list tenant untuk dropdown
    $tenants = \App\Models\Tenants::all();

    return view('superadmin.laporan.index', compact(
        'transaksis',
        'totalPendapatan',
        'totalTransaksi',
        'tenants'
    ));
}
public function exportPdf(Request $request)
{
    $query = \App\Models\Transaksi::with([
        'order.customer',
        'order.details.produk.tenant'
    ])->where('status_pembayaran', 'paid');

    // FILTER TANGGAL
    if ($request->start_date && $request->end_date) {
        $query->whereHas('order', function ($q) use ($request) {
            $q->whereBetween('order_tanggal', [
                \Carbon\Carbon::parse($request->start_date)->startOfDay(),
                \Carbon\Carbon::parse($request->end_date)->endOfDay()
            ]);
        });
    }

    // FILTER TENANT
    if ($request->tenant_id) {
        $query->whereHas('order.details.produk', function ($q) use ($request) {
            $q->where('tenants_id', $request->tenant_id);
        });
    }

    $transaksis = $query->get();

    $totalPendapatan = $transaksis->sum('jumlah_bayar');
    $totalTransaksi  = $transaksis->count();

    // ✅ FIX: PASTIKAN SELALU ADA NILAI
    $tenantNama = 'Semua Tenant';

    if ($request->tenant_id) {
    $tenant = \App\Models\Tenants::find($request->tenant_id);
    $tenantNama = $tenant->tenant_name ?? 'Tidak ditemukan';
} else {
    // 🔥 ambil langsung dari DB
    $tenantNama = DB::table('detailorders')
        ->join('produks', 'detailorders.produks_id', '=', 'produks.id')
        ->join('tenants', 'produks.tenants_id', '=', 'tenants.id')
        ->select('tenants.tenant_name')
        ->distinct()
        ->pluck('tenant_name')
        ->implode(', ');

    if (!$tenantNama) {
        $tenantNama = 'Semua Tenant';
    }
    }

    $pdf = Pdf::loadView('superadmin.laporan.pdf', compact(
        'transaksis',
        'totalPendapatan',
        'totalTransaksi',
        'request',
        'tenantNama'
    ));

    return $pdf->download('laporan-penjualan.pdf');
}
public function chartPenjualanPerTenantMingguan(Request $request)
{
    $start = $request->start_date
        ? Carbon::parse($request->start_date)->startOfDay()
        : now()->subWeeks(8)->startOfDay();

    $end = $request->end_date
        ? Carbon::parse($request->end_date)->endOfDay()
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

    $tenants = $rows->groupBy('tenant_id')->map(function ($items) use ($weeks) {
        $name = $items->first()->tenant_name;

        $byWeek = $items->keyBy('yearweek')->map(fn($r) => (float) $r->total);

        $data = $weeks->map(fn($yw) => $byWeek[$yw] ?? 0)->values();

        return [
            'label' => $name,
            'data'  => $data,
        ];
    })->values();

    return response()->json([
        'labels'   => $labels,
        'datasets' => $tenants,
    ]);
}
}