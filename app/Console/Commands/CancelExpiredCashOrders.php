<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class CancelExpiredCashOrders extends Command
{
    protected $signature = 'orders:cancel-expired-cash';
    protected $description = 'Batalkan pesanan tunai yang belum dibayar setelah 10 menit';

    public function handle()
    {
        $expired = Carbon::now()->subMinutes(10);
        $orders = Order::where('order_status', 'pending')
            ->whereHas('transaksi', function($q){
                $q->where('metode_pembayaran', 'cash')
                  ->where('status_pembayaran', 'pending');
            })
            ->where('created_at', '<=', $expired)
            ->get();

        foreach($orders as $order){
            $order->order_status = 'batal';
            $order->save();
            // Opsi, update transaksi status juga jika mau
        }

        $this->info('Canceled orders: ' . $orders->count());
    }
}