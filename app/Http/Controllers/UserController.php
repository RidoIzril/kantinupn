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
    $data = $request->validate([
        'customer_fullname' => 'required|string|max:255',
        'customer_email'    => 'required|email',
        'customer_contact'  => 'required|string|max:20',
        'customer_dob'      => 'required|date',
        'customer_gender'   => 'required',
        'customer_faculty'  => 'required|string',
        'customer_status'   => 'required',
    ]);

    $customer = Customers::findOrFail($id);
    $customer->update($data);

    return redirect()->route('user.list_user')
        ->with('success', 'Customer berhasil diupdate.');
}

    public function destroy($id)
    {
        $customer = Customers::findOrFail($id);
        $customer->delete();

        return redirect()->route('user.list_user')->with('success', 'Customer Berhasil dihapus.');
    }

}
