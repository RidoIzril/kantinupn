<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Penjual;
use Illuminate\Http\Request;

class CusController extends Controller
{
    public function index(Request $request)
    {
        $keyword    = trim((string) $request->input('keyword', ''));
        $categoryId = $request->input('kategoris');
        $categories = Kategori::all();

        $query = Penjual::query()
            ->whereHas('tenant')
            ->with([
                'tenant',
                'tenant.produks' => function ($q) use ($keyword, $categoryId) {
                    $q->where('stok', '>', 0);

                    if ($keyword !== '') {
                        $q->where(function ($qq) use ($keyword) {
                            $qq->where('nama', 'like', "%{$keyword}%")
                               ->orWhere('deskripsi', 'like', "%{$keyword}%");
                        });
                    }

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
}