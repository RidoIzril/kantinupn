<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CusController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PenjualController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ManageController;
use App\Http\Controllers\SuperadminController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.regis');
})->name('register');


/*
|--------------------------------------------------------------------------
| CUSTOMER
|--------------------------------------------------------------------------
*/
Route::prefix('customer')->group(function(){

    Route::get('/home', [CusController::class,'index'])
        ->name('customer.homecustomer');

    Route::get('/customer/penjual/{id}', [CusController::class, 'showPenjual'])
        ->name('customer.menu.show');

    Route::get('/customer/profile', [CusController::class, 'profile'])
        ->name('profile.profilecustomer');

    Route::get('/customer/profile/edit', [CusController::class, 'editProfile'])
        ->name('profile.edit_profilecust');

    Route::put('/customer/profile', [CusController::class, 'updateProfile'])
        ->name('customer.profile.update');

    // Cart
    Route::get('/customer/keranjang', [CartController::class, 'index'])
        ->name('carts.cartcustomer');

    Route::post('/cart/add', [CartController::class, 'add'])
        ->name('cart.add');

    Route::patch('/cart/update', [CartController::class, 'update'])
        ->name('cart.update');

    Route::delete('/cart/remove', [CartController::class, 'remove'])
        ->name('cart.remove');

     Route::post('/cart/checkout', [TransactionController::class, 'checkout'])
        ->name('cart.checkout');
        
    // Transaksi
    Route::get('/customer/transaksi', [TransactionController::class, 'index'])
        ->name('transactions.list_transaction');

    Route::get('/customer/transaksi/{id}', [TransactionController::class, 'show'])
        ->name('transactions.show');

    Route::post('/customer/transaksi/accept/{id}', [TransactionController::class, 'accept'])
        ->name('transactions.accept');

    Route::post('/customer/transaksi/cancel/{id}', [TransactionController::class, 'cancel'])
        ->name('transaction.cancel');

    Route::get('/customer/transaksi/{id}/download-pdf', [TransactionController::class, 'downloadPdf'])
        ->name('transactions.download_pdf');

    Route::post('/customer/transaksi/{id}/send-invoice', [TransactionController::class, 'sendInvoice'])
        ->name('transactions.send_invoice');

});


/*
|--------------------------------------------------------------------------
| PENJUAL
|--------------------------------------------------------------------------
*/
Route::prefix('penjual')->group(function () {

    // halaman shell (proteksi via JS + token API)
    Route::get('/home', function () {
        return view('penjual.homepenjual');
    })->name('penjual.homepenjual');

    Route::get('/profile', function () {
    return view('penjual.profile.index');
})->name('penjual.profile.show');

    Route::get('/profile/edit', [PenjualController::class, 'profileEdit'])->name('penjual.profile.edit');
    Route::put('/profile/update', [PenjualController::class, 'profileUpdate'])->name('penjual.profile.update');
    Route::delete('/profile/delete', [PenjualController::class, 'profileDestroy'])->name('penjual.profile.destroy');
    
    Route::get('/kelola_transaksi', [ManageController::class, 'index'])
        ->name('penjual.transaction_manage.manage');

    Route::get('/kelola_transaksi/show/{id}', [ManageController::class, 'show'])
        ->name('penjual.transaction_manage.show');

    Route::post('/kelola_transaksi/update/{id}', [ManageController::class, 'update'])
        ->name('penjual.transaction_manage.update');

    Route::post('/kelola_transaksi/cancel/{id}', [ManageController::class, 'cancel'])
        ->name('penjual.transaction_manage.cancel');

    // PRODUK
    Route::get('/produk', [ProductController::class, 'index'])
        ->name('produk.list_produk');

    Route::get('/produk/tambah', [ProductController::class, 'create'])
        ->name('produk.tambah_produk');

    Route::post('/produk', [ProductController::class, 'store'])
        ->name('produk.store');

    Route::get('/produk/edit/{id}', [ProductController::class, 'edit'])
        ->name('produk.edit_produk');

    Route::put('/produk/update/{id}', [ProductController::class, 'update'])
        ->name('produk.update');

    Route::delete('/produk/{id}', [ProductController::class, 'destroy'])
        ->name('produk.destroy');

    // PAYMENT
    Route::get('/payment', [PaymentController::class, 'index'])
        ->name('payment.list_payment');

    Route::post('/payment', [PaymentController::class, 'store'])
        ->name('payment.store');
});


/*
|--------------------------------------------------------------------------
| SUPERADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {

    Route::get('/dashboard', [SuperadminController::class, 'home'])
        ->name('homesuperadmin');

   Route::get('/kategori', [CategoryController::class, 'index'])
        ->name('kategori.list_kategori');

    Route::post('/kategori', [CategoryController::class, 'store'])
        ->name('kategori.store');

    Route::put('/kategori/{id}', [CategoryController::class, 'update'])
        ->name('kategori.update');

    Route::delete('/kategori/{id}', [CategoryController::class, 'destroy'])
        ->name('kategori.destroy');

    Route::get('/penjual', [SuperadminController::class, 'penjual'])
        ->name('penjual.index');

    Route::get('/penjual/tambah', [SuperadminController::class, 'createPenjual'])
        ->name('penjual.create');

    Route::post('/penjual', [SuperadminController::class, 'storePenjual'])
        ->name('penjual.store');

    Route::get('/penjual/{id}/edit', [SuperadminController::class, 'editPenjual'])
        ->name('penjual.edit');

    Route::put('/penjual/{id}', [SuperadminController::class, 'updatePenjual'])
        ->name('penjual.update');

    Route::put('/penjual/{id}/status', [SuperadminController::class, 'updateStatus'])
        ->name('penjual.update_status');

    Route::delete('/penjual/{id}', [SuperadminController::class, 'destroyPenjual'])
        ->name('penjual.destroy');

});