<?php

namespace App\Http\Controllers;

use App\Models\Penjual;
use App\Models\Category;
use App\Models\Customers;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperadminController extends Controller
{
    /* =======================
     | DASHBOARD / HOME
     =======================*/
    public function home()
    {
        return view('superadmin.homesuperadmin', [
            'jumlahPenjual'    => Penjual::count(),
            'jumlahCustomer'  => Customers::count(),
            'jumlahKategori'  => Category::count(),
            'jumlahTransaksi' => Transaction::count(),
        ]);
    }

    /* =======================
     | LIST + SEARCH PENJUAL
     =======================*/
    public function penjual(Request $request)
    {
        $keyword = $request->search;

        $penjuals = Penjual::when($keyword, function ($query) use ($keyword) {
            $query->where('penjual_username', 'like', "%{$keyword}%")
                  ->orWhere('penjual_tenantname', 'like', "%{$keyword}%")
                  ->orWhere('penjual_notenant', 'like', "%{$keyword}%")
                  ->orWhere('penjual_nohp', 'like', "%{$keyword}%");
        })->orderBy('created_at', 'desc')->get();

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
            'penjual_fullname'   => 'required|string|max:255',
            'penjual_notenant'   => 'required|unique:penjuals,penjual_notenant',
            'penjual_tenantname' => 'required|unique:penjuals,penjual_tenantname',
            'penjual_nohp'       => 'required|string|max:20',
            'penjual_gender'     => 'required|in:Laki-laki,Perempuan',
            'penjual_username'   => 'required|unique:penjuals,penjual_username',
            'penjual_password'   => 'required|min:6',
            'penjual_status'     => 'required|in:aktif,nonaktif',
            'foto_tenant'        => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto_tenant')) {
            $data['foto_tenant'] = $request->file('foto_tenant')
                ->store('foto_tenant', 'public');
        }

        $data['penjual_password'] = Hash::make($data['penjual_password']);

        Penjual::create($data);

        return redirect()
            ->route('superadmin.penjual.index')
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
     | UPDATE DATA PENJUAL
     =======================*/
    public function updatePenjual(Request $request, $id)
    {
        $penjual = Penjual::findOrFail($id);

        $data = $request->validate([
            'penjual_fullname'   => 'required|string|max:255',
            'penjual_notenant'   => 'required|unique:penjuals,penjual_notenant,' . $id . ',penjual_id',
            'penjual_tenantname' => 'required|unique:penjuals,penjual_tenantname,' . $id . ',penjual_id',
            'penjual_nohp'       => 'required|string|max:20',
            'penjual_gender'     => 'required|in:Laki-laki,Perempuan',
            'penjual_username'   => 'required|unique:penjuals,penjual_username,' . $id . ',penjual_id',
            'penjual_password'   => 'nullable|min:6',
            'penjual_status'     => 'required|in:aktif,nonaktif',
            'foto_tenant'        => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto_tenant')) {
            $data['foto_tenant'] = $request->file('foto_tenant')
                ->store('foto_tenant', 'public');
        }

        // password opsional
        if ($request->filled('penjual_password')) {
            $data['penjual_password'] = Hash::make($request->penjual_password);
        } else {
            unset($data['penjual_password']);
        }

        $penjual->update($data);

        return redirect()
            ->route('superadmin.penjual.index')
            ->with('success', 'Data penjual berhasil diperbarui');
    }

    /* =======================
     | TOGGLE STATUS PENJUAL
     =======================*/
    public function updateStatus(Request $request, $id)
{
    $penjual = Penjual::findOrFail($id);

    $request->validate([
        'penjual_status' => 'required|in:aktif,nonaktif',
    ]);

    $penjual->update([
        'penjual_status' => $request->penjual_status
    ]);

    return back()->with('success', 'Status penjual berhasil diperbarui');
}


    /* =======================
     | HAPUS PENJUAL
     =======================*/
    public function destroyPenjual($id)
    {
        Penjual::findOrFail($id)->delete();

        return back()->with('success', 'Akun penjual berhasil dihapus');
    }
}
