<?php

namespace App\Mail;

use App\Models\LaundryOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LaundryOrder $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '💳 Pembayaran '.$this->order->tracking_code.' Berhasil Diterima — SafeWash',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-success',
        );
    }
}
