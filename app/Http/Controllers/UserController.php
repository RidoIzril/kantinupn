<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $customers = Customers::all();
        return view('penjual.user.list_user', compact('customers'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_fullname' => $request->customer_fullname,
            'customer_email'    => $request->customer_email,
            'customer_contact'  => $request->customer_contact,
            'customer_dob'      => $request->customer_dob,
            'customer_gender'   => $request->customer_gender,
            'customer_faculty'  => $request->customer_faculty,
            'customer_status'   => $request->customer_status,
        ]);

        $customer = Customers::findOrFail($id);
        $customer->update($request->all());

        return redirect()->route('user.list_user')->with('success', 'Customer Berhasil diupdate.');
    }

    public function destroy($id)
    {
        $customer = Customers::findOrFail($id);
        $customer->delete();

        return redirect()->route('user.list_user')->with('success', 'Customer Berhasil dihapus.');
    }

}
