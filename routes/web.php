<?php

use App\Http\Controllers\OrderController;
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
use App\Http\Controllers\XenditWebhookController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

// LANDING PAGE (guest) -> langsung dashboard customer
Route::get('/', [CusController::class, 'index'])->name('welcome');

// optional: kalau masih mau simpan halaman welcome blade default
Route::view('/welcome', 'welcome')->name('welcome.page');

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

    Route::get('/customer/profile', [CusController::class, 'show'])->name('profile.profilecustomer');
    Route::put('/customer/profile', [CusController::class, 'update'])->name('profile.profilecustomer.update');
    Route::put('/customer/profile/password', [CusController::class, 'updatePassword'])->name('profile.profilecustomer.password');

    // Cart
    Route::get('/customer/keranjang', [CartController::class, 'index'])->name('carts.cartcustomer');
    Route::post('/customer/keranjang/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/customer/keranjang/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/customer/keranjang/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/customer/keranjang/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
        
    //invoice
    Route::get('/customer/invoice/cash/{id}', [App\Http\Controllers\TransactionController::class, 'showInvoice'])->name('invoice.cash');
    // AJAX check status pembayaran order
    Route::get('/customer/payment/qris', [PaymentController::class, 'qris'])->name('customer.payment.qris');
    Route::get('/customer/payment/qris/checkstatus', [PaymentController::class, 'checkQrisStatus'])->name('payment.qris.checkstatus');
    Route::post('/xendit/webhook', [XenditWebhookController::class, 'handle']);

    Route::get('/riwayat-pesanan', [OrderController::class, 'history'])->name('orders.history');
    Route::get('/riwayat-pesanan/{order}', [OrderController::class, 'historyShow'])->name('orders.history.show');
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

    Route::get('/penjual/notifications', [PenjualController::class, 'notifications']);

    Route::get('/profile', function () {
    return view('penjual.profile.index');
})->name('penjual.profile.show');

    Route::get('/profile/edit', [PenjualController::class, 'profileEdit'])->name('penjual.profile.edit');
    Route::put('/profile/update', [PenjualController::class, 'profileUpdate'])->name('penjual.profile.update');
    Route::delete('/profile/delete', [PenjualController::class, 'profileDestroy'])->name('penjual.profile.destroy');

    Route::get('/order', [OrderController::class, 'pesanan'])->name('penjual.order.index');
    Route::get('/order/{id}', [OrderController::class, 'pesananShow'])->name('penjual.order.show');
    Route::post('/order/{id}/process', [OrderController::class, 'pesananProcess'])->name('penjual.order.process');
    Route::post('/order/{id}/complete', [OrderController::class, 'pesananComplete'])->name('penjual.order.complete');
    Route::post('/order/{id}/cancel', [OrderController::class, 'pesananCancel'])->name('penjual.order.cancel');

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

    Route::get('/penjual/laporan', [PenjualController::class, 'laporan'])->name('penjual.laporan.index');
    Route::get('/penjual/laporan/{id}', [PenjualController::class, 'detailLaporan'])
    ->name('penjual.laporan.detail');
    Route::get('/laporan/pdf', [PenjualController::class, 'exportPdf'])
    ->name('penjual.laporan.pdf');

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

     Route::get('/penjual', [SuperadminController::class, 'penjual'])->name('penjual.index');
    Route::get('/penjual/create', [SuperadminController::class, 'createPenjual'])->name('penjual.create');
    Route::post('/penjual', [SuperadminController::class, 'storePenjual'])->name('penjual.store');
    Route::get('/penjual/{id}/edit', [SuperadminController::class, 'editPenjual'])->name('penjual.edit');
    Route::put('/penjual/{id}', [SuperadminController::class, 'updatePenjual'])->name('penjual.update');
    Route::put('/penjual/{id}/status', [SuperadminController::class, 'updateStatus'])->name('penjual.update_status');
    Route::delete('/penjual/{id}', [SuperadminController::class, 'destroyPenjual'])->name('penjual.destroy');

    Route::get('laporan', [SuperadminController::class, 'laporan'])->name('laporan.index');
    Route::get('/superadmin/laporan/{id}', [SuperadminController::class, 'detailLaporan'])
    ->name('superadmin.laporan.detail');
    Route::get('/laporan/pdf', [SuperadminController::class, 'exportPdf'])
    ->name('laporan.pdf');
});