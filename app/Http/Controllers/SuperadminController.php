<?php

namespace App\Http\Controllers;

use App\Models\Penjual;
use App\Models\Kategori;
use App\Models\Customers;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperadminController extends Controller
{
    /* =======================
     | DASHBOARD
     =======================*/
    public function home()
    {
        return view('superadmin.homesuperadmin', [
            'jumlahPenjual'   => Penjual::count(),
            'jumlahCustomer'  => Customers::count(),
            'jumlahKategori'  => Kategori::count(),
            'jumlahTransaksi' => Transaksi::count(),
        ]);
    }


    /* =======================
     | LIST + SEARCH PENJUAL
     =======================*/
    public function penjual(Request $request)
    {
        $keyword = $request->search;

        $penjuals = Penjual::query()
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('penjual_username', 'like', "%{$keyword}%")
                      ->orWhere('penjual_tenantname', 'like', "%{$keyword}%")
                      ->orWhere('penjual_notenant', 'like', "%{$keyword}%")
                      ->orWhere('penjual_nohp', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('superadmin.akun.akun_penjual', compact('penjuals', 'keyword'));
    }


    /* =======================
     | FORM TAMBAH PENJUAL
     =======================*/
    public function createPenjual()
    {
        return view('superadmin.akun.tambah_penjual');
    }


    /* =======================
     | SIMPAN PENJUAL
     =======================*/
    public function storePenjual(Request $request)
    {
        $data = $request->validate([
            'username'   => 'required|unique:penjuals,penjual_username',
            'password'   => 'required|min:6',
            'status'     => 'required|in:aktif,nonaktif',
        ]);

        // upload foto
        if ($request->hasFile('foto_tenant')) {
            $data['foto_tenant'] = $request->file('foto_tenant')
                ->store('foto_tenant', 'public');
        }

        // hash password
        $data['penjual_password'] = Hash::make($data['penjual_password']);

        Penjual::create($data);

        return redirect()
            ->route('penjual.index')
            ->with('success', 'Akun penjual berhasil ditambahkan');
    }


    /* =======================
     | FORM EDIT PENJUAL
     =======================*/
    public function editPenjual($id)
    {
        $penjual = Penjual::findOrFail($id);

        return view('superadmin.akun.edit_penjual', compact('penjual'));
    }


    /* =======================
     | UPDATE PENJUAL
     =======================*/
    public function updatePenjual(Request $request, $id)
    {
        $penjual = Penjual::findOrFail($id);

        $data = $request->validate([
            'username'   => 'required|unique:penjuals,penjual_username,' . $id . ',penjual_id',
            'password'   => 'nullable|min:6',
        ]);

        // upload foto baru
        if ($request->hasFile('foto_tenant')) {
            $data['foto_tenant'] = $request->file('foto_tenant')
                ->store('foto_tenant', 'public');
        }

        // password optional
        if ($request->filled('penjual_password')) {
            $data['penjual_password'] = Hash::make($request->penjual_password);
        } else {
            unset($data['penjual_password']);
        }

        $penjual->update($data);

        return redirect()
            ->route('penjual.index')
            ->with('success', 'Data penjual berhasil diperbarui');
    }


    /* =======================
     | UPDATE STATUS
     =======================*/
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'penjual_status' => 'required|in:aktif,nonaktif',
        ]);

        $penjual = Penjual::findOrFail($id);

        $penjual->update([
            'penjual_status' => $request->penjual_status
        ]);

        return back()->with('success', 'Status penjual berhasil diperbarui');
    }


    /* =======================
     | DELETE PENJUAL
     =======================*/
    public function destroyPenjual($id)
    {
        Penjual::findOrFail($id)->delete();

        return back()->with('success', 'Akun penjual berhasil dihapus');
    }
}