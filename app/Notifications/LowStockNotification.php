<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class LowStockNotification extends Notification
{
    protected $produk;

    public function __construct($produk)
    {
        $this->produk = $produk;
    }

    public function via($notifiable)
    {
        return ['database']; // simpan ke DB
    }

    public function toDatabase($notifiable)
    {
        return [
            'produk_id' => $this->produk->id,
            'nama_produk' => $this->produk->nama,
            'stok' => $this->produk->stok,
            'message' => 'Stok produk hampir habis!'
        ];
    }
}