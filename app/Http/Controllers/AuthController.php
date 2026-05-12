<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customers;

class AuthController extends Controller
{

    public function showLoginForm()
    {
        return view('auth.login');
    }

public function showRegistrationForm()
    {
        return view('auth.regis');
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTER CUSTOMER
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        $request->validate([
            'user_name' => 'required',
            'user_password' => 'required'
        ]);

        // CUSTOMER
        if (Auth::guard('customer')->attempt([
            'customer_username' => $request->user_name,
            'password' => $request->user_password
        ])) {

            $request->session()->regenerate();
            return redirect()->route('customer.homecustomer');
        }

        // PENJUAL
        if (Auth::guard('penjual')->attempt([
            'username' => $request->user_name,
            'password' => $request->user_password
        ])) {

            $request->session()->regenerate();
            return redirect()->route('penjual.homepenjual');
        }


        // SUPERADMIN
        if (Auth::guard('superadmin')->attempt([
            'username' => $request->user_name,
            'password' => $request->user_password
        ])) {

            $request->session()->regenerate();
            return redirect()->route('superadmin.homesuperadmin');
        }

        return back()->withErrors([
            'login' => 'Username atau password salah'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        Auth::guard('penjual')->logout();
        Auth::guard('customer')->logout();
        Auth::guard('superadmin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}