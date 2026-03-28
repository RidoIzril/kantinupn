<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Penjual;
use Illuminate\Http\Request;

class CusController extends Controller
{
    /*
    |==========================================
    | HOME CUSTOMER
    |==========================================
    */
    public function index(Request $request)
    {
        $keyword    = $request->input('keyword');
        $categoryId = $request->input('categories');
        $categories = Kategori::all();

        /*
        | TANPA SEARCH
        */
        if (!$keyword) {

            $penjuals = Penjual::with([
                'tenant.produks' => function ($q) {
                    $q->where('stok', '>', 0)
                      ->with('kategori');
                }
            ])
            ->get();

            return view('customer.homecustomer', [
                'penjuals'   => $penjuals,
                'categories' => $categories,
                'keyword'    => null,
            ]);
        }

        /*
        | DENGAN SEARCH
        */
        $penjuals = Penjual::whereHas('tenant', function ($q) use ($keyword) {

                // search nama tenant
                $q->where('nama_tenant', 'like', "%$keyword%");

            })

            // SEARCH PRODUK
            ->orWhereHas('tenant.produks', function ($q) use ($keyword, $categoryId) {

                $q->where('stok', '>', 0)
                  ->where(function ($qq) use ($keyword) {
                      $qq->where('nama', 'like', "%$keyword%")
                         ->orWhere('deskripsi', 'like', "%$keyword%");
                  });

                if ($categoryId) {
                    $q->where('kategoris_id', $categoryId);
                }
            })

            ->with(['tenant.produks' => function ($q) use ($keyword, $categoryId) {

                $q->where('stok', '>', 0)
                  ->where(function ($qq) use ($keyword) {
                      $qq->where('nama', 'like', "%$keyword%")
                         ->orWhere('deskripsi', 'like', "%$keyword%");
                  });

                if ($categoryId) {
                    $q->where('kategoris_id', $categoryId);
                }

                $q->with('kategori','variants');
            }])

            ->get();

        return view('customer.homecustomer', [
            'penjuals'   => $penjuals,
            'categories' => $categories,
            'keyword'    => $keyword,
        ]);
    }


    /*
    |==========================================
    | DETAIL PENJUAL (MENU)
    |==========================================
    */
    public function showPenjual($id)
    {
        $penjual = Penjual::with([
            'tenant.produks' => function ($q) {
                $q->where('stok', '>', 0)
                  ->with('kategori','variants');
            }
        ])
        ->where('penjuals_id', $id)
        ->firstOrFail();

        return view('customer.menu.show', compact('penjual'));
    }


    /*
    |==========================================
    | PROFILE CUSTOMER
    |==========================================
    */
    public function profile()
    {
        $customer = auth()->user();
        return view('customer.profile.profilecustomer', compact('customer'));
    }


    public function editProfile()
    {
        $customer = auth()->user();
        return view('customer.profile.edit_profilecust', compact('customer'));
    }


    public function updateProfile(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required',
            'email' => 'required|email',
            'kontak' => 'required',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required',
            'fakultas' => 'required',
            'status' => 'required',
        ]);

        $customer = auth()->user();

        $customer->update([
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'kontak' => $request->kontak,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'fakultas' => $request->fakultas,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success','Profil berhasil diupdate');
    }
}