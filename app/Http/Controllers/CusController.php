<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Penjual;
use App\Models\Customers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class CusController extends Controller
{
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

    public function index(Request $request)
    {
        $keyword    = trim((string) $request->input('keyword', ''));
        $categoryId = $request->input('kategoris');
        $categories = Kategori::all();

        $query = Penjual::query()
            ->whereHas('tenant')
            ->with([
                'tenant',
                'tenant.produks' => function ($q) use ($categoryId) {
                    $q->where('stok', '>', 0);

                    if (!empty($categoryId)) {
                        $q->where('kategoris_id', $categoryId);
                    }

                    $q->with('kategoris');
                }
            ]);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword, $categoryId) {
                $q->whereHas('tenant', function ($t) use ($keyword) {
                    $t->where('tenant_name', 'like', "%{$keyword}%");
                })
                ->orWhereHas('tenant.produks', function ($p) use ($keyword, $categoryId) {
                    $p->where('stok', '>', 0)
                      ->where(function ($pp) use ($keyword) {
                          $pp->where('nama', 'like', "%{$keyword}%")
                             ->orWhere('deskripsi', 'like', "%{$keyword}%");
                      });

                    if (!empty($categoryId)) {
                        $p->where('kategoris_id', $categoryId);
                    }
                });
            });
        }

        $penjuals = $query->get();

        if ($keyword !== '') {
            $penjuals->each(function ($penjual) use ($keyword) {
                $tenantName = strtolower((string) optional($penjual->tenant)->tenant_name);
                $kw = strtolower($keyword);

                $matchTenant = str_contains($tenantName, $kw);
                $produk = $penjual->tenant?->produks ?? collect();

                if (!$matchTenant) {
                    $produk = $produk->filter(function ($p) use ($kw) {
                        return str_contains(strtolower((string) $p->nama), $kw)
                            || str_contains(strtolower((string) $p->deskripsi), $kw);
                    })->values();
                }

                if ($penjual->tenant) {
                    $penjual->tenant->setRelation('produks', $produk);
                }
            });
        }

        return view('customer.homecustomer', [
            'penjuals'  => $penjuals,
            'kategoris' => $categories,
            'keyword'   => $keyword === '' ? null : $keyword,
        ]);
    }

    public function showPenjual($id)
    {
        $penjual = Penjual::with([
            'tenant',
            'tenant.produks' => function ($q) {
                $q->where('stok', '>', 0)->with('kategoris', 'variants');
            }
        ])->findOrFail($id);

        return view('customer.menu.show', compact('penjual'));
    }

    // ===== PROFILE CUSTOMER =====

    public function show(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) return redirect('/login');

        if ($user->role !== 'customer') {
            return redirect('/login')->withErrors(['error' => 'Akses ditolak']);
        }

        $customer = Customers::firstOrCreate(
            ['users_id' => $user->id],
            ['users_id' => $user->id, 'email' => null]
        );

        return view('customer.profile.profilecustomer', compact('customer', 'user'));
    }

    public function update(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) return redirect('/login');

        if ($user->role !== 'customer') {
            return redirect('/login')->withErrors(['error' => 'Akses ditolak']);
        }

        $validated = $request->validate([
            'nama_lengkap'   => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'tanggal_lahir'  => 'nullable|date',
            'jenis_kelamin'  => 'nullable|in:laki-laki,perempuan',
            'fakultas'       => 'nullable|string|max:255',
            'status'         => 'nullable|in:Mahasiswa,Dosen,Tendik',
            'kontak'         => 'nullable|string|max:30',
            'token'          => 'nullable|string',
        ]);

        $customer = Customers::firstOrCreate(
            ['users_id' => $user->id],
            ['users_id' => $user->id]
        );

        $customer->update($validated);

        return redirect()->route('profile.profilecustomer', [
            'token' => $request->input('token') ?? $request->query('token')
        ])->with('success', 'Profile berhasil diperbarui');
    }

    public function updatePassword(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) return redirect('/login');

        if ($user->role !== 'customer') {
            return redirect('/login')->withErrors(['error' => 'Akses ditolak']);
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
            'token'            => 'nullable|string',
        ], [
            'new_password.confirmed' => 'Konfirmasi password baru tidak sama.',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Password lama salah.'])
                ->withInput();
        }

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return redirect()->route('profile.profilecustomer', [
            'token' => $request->input('token') ?? $request->query('token')
        ])->with('success_password', 'Password berhasil diperbarui.');
    }
}