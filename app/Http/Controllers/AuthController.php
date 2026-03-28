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

    public function register(Request $request)
    {
        $request->validate([
            'customer_username' => 'required|unique:customers',
            'customer_fullname' => 'required',
            'customer_email' => 'required|email|unique:customers',
            'customer_contact' => 'required',
            'customer_password' => 'required|min:6|same:customer_confirm',
            'customer_dob' => 'required',
            'customer_gender' => 'required',
            'customer_faculty' => 'required',
            'customer_status' => 'required'
        ]);

        Customers::create([
            'customer_username' => $request->customer_username,
            'customer_fullname' => $request->customer_fullname,
            'customer_email' => $request->customer_email,
            'customer_contact' => $request->customer_contact,
            'customer_password' => Hash::make($request->customer_password),
            'customer_dob' => $request->customer_dob,
            'customer_gender' => $request->customer_gender,
            'customer_faculty' => $request->customer_faculty,
            'customer_status' => $request->customer_status
        ]);

        return redirect()->route('login')->with('success','Registrasi berhasil');
    }
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
            'penjual_username' => $request->user_name,
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