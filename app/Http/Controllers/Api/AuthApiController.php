<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\Penjual;
use App\Models\Superadmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{

    public function login(Request $request)
    {

        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'role' => 'required'
        ]);

        $username = $request->username;
        $password = $request->password;
        $role = $request->role;

        switch ($role) {

            /**
             * ========================
             * LOGIN CUSTOMER
             * ========================
             */
            case 'customer':

                $user = Customers::where('customer_username', $username)->first();

                if (!$user || !Hash::check($password, $user->customer_password)) {

                    return response()->json([
                        'success' => false,
                        'message' => 'Username atau password customer salah'
                    ], 401);
                }

                $token = $user->createToken('customer_token')->plainTextToken;

                break;



            /**
             * ========================
             * LOGIN PENJUAL
             * ========================
             */
            case 'penjual':

                $user = Penjual::where('penjual_username', $username)->first();

                if (!$user || !Hash::check($password, $user->penjual_password)) {

                    return response()->json([
                        'success' => false,
                        'message' => 'Username atau password penjual salah'
                    ], 401);
                }

                $token = $user->createToken('penjual_token')->plainTextToken;

                break;



            /**
             * ========================
             * LOGIN SUPERADMIN
             * ========================
             */
            case 'superadmin':

                $user = Superadmin::where('superadmin_username', $username)->first();

                if (!$user || !Hash::check($password, $user->superadmin_password)) {

                    return response()->json([
                        'success' => false,
                        'message' => 'Username atau password superadmin salah'
                    ], 401);
                }

                $token = $user->createToken('superadmin_token')->plainTextToken;

                break;



            default:

                return response()->json([
                    'success' => false,
                    'message' => 'Role tidak valid'
                ], 400);
        }


        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'role' => $role,
            'token' => $token,
            'data' => $user
        ]);
    }
}