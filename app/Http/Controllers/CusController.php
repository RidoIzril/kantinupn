<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Penjual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CusController extends Controller
{
    /**
     * ==========================================
     * HOME CUSTOMER
     * ==========================================
     */
    public function index(Request $request)
    {
        $keyword    = $request->input('keyword');
        $categoryId = $request->input('categories');
        $categories = Category::all();

        /*
        |--------------------------------------------------------------------------
        | TANPA SEARCH
        |--------------------------------------------------------------------------
        */
        if (!$keyword) {

            $penjuals = Penjual::with([
                'products' => function ($q) {
                    $q->where('product_stock', '>', 0)
                      ->with('category');
                }
            ])
            ->withCount([
                'products' => function ($q) {
                    $q->where('product_stock', '>', 0);
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
        |--------------------------------------------------------------------------
        | DENGAN SEARCH
        |--------------------------------------------------------------------------
        */
        $penjuals = Penjual::whereHas('products', function ($q) use ($keyword, $categoryId) {

                $q->where('product_stock', '>', 0)
                  ->where(function ($qq) use ($keyword) {
                      $qq->where('product_name', 'like', "%$keyword%")
                         ->orWhere('product_description', 'like', "%$keyword%");
                  });

                if ($categoryId) {
                    $q->where('category_id', $categoryId);
                }
            })
            ->with(['products' => function ($q) use ($keyword, $categoryId) {

                $q->where('product_stock', '>', 0)
                  ->where(function ($qq) use ($keyword) {
                      $qq->where('product_name', 'like', "%$keyword%")
                         ->orWhere('product_description', 'like', "%$keyword%");
                  });

                if ($categoryId) {
                    $q->where('category_id', $categoryId);
                }

                $q->with('category');
            }])
            ->withCount(['products' => function ($q) {
                $q->where('product_stock', '>', 0);
            }])
            ->get();

        return view('customer.homecustomer', [
            'penjuals'   => $penjuals,
            'categories' => $categories,
            'keyword'    => $keyword,
        ]);
    }


    /**
     * ==========================================
     * DETAIL PENJUAL (MENU)
     * ==========================================
     */
    public function showPenjual($id)
    {
        $penjual = Penjual::with([
            'products' => function ($q) {
                $q->where('product_stock', '>', 0)
                  ->with('category');
            }
        ])
        ->where('penjual_id', $id)
        ->firstOrFail();

        return view('customer.menu.show', compact('penjual'));
    }


    /**
     * ==========================================
     * PROFILE CUSTOMER (VIEW)
     * ==========================================
     */
    public function profile()
    {
        $customer = Auth::guard('customer')->user();
        return view('customer.profile.profilecustomer', compact('customer'));
    }


    /**
     * ==========================================
     * EDIT PROFILE CUSTOMER
     * ==========================================
     */
    public function editProfile()
    {
        $customer = Auth::guard('customer')->user();
        return view('customer.profile.edit_profilecust', compact('customer'));
    }


    /**
     * ==========================================
     * UPDATE PROFILE CUSTOMER
     * ==========================================
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'customer_fullname' => 'required|string|max:255',
            'customer_email'    => 'required|email|max:255',
            'customer_contact'  => 'required|string|max:20',
            'customer_dob'      => 'required|date',
            'customer_gender'   => 'required|string',
            'customer_faculty'  => 'required|string|max:255',
            'customer_status'   => 'required|string|max:100',
        ]);

        $customer = Auth::guard('customer')->user();

        $customer->update([
            'customer_fullname' => $request->customer_fullname,
            'customer_email'    => $request->customer_email,
            'customer_contact'  => $request->customer_contact,
            'customer_dob'      => $request->customer_dob,
            'customer_gender'   => $request->customer_gender,
            'customer_faculty'  => $request->customer_faculty,
            'customer_status'   => $request->customer_status,
        ]);

        return redirect()
            ->route('profile.profilecustomer')
            ->with('success', 'Profil berhasil diperbarui');
    }
}