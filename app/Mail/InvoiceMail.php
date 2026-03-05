<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;
    public $transaction;
    public $pdf;

    public function __construct($transaction, $pdf)
    {
        $this->transaction = $transaction;
        $this->pdf = $pdf;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice #' . $this->transaction->custom_code_transaction . ' - APOTEKNih BOS',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdf, 'Invoice_' . $this->transaction->custom_code_transaction . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
