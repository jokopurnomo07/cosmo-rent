<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExtensionApprovedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $extension;
    public $paymentUrl;

    public function __construct($extension, $paymentUrl)
    {
        $this->extension = $extension;
        $this->paymentUrl = $paymentUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Perpanjangan Rental Disetujui - Silakan Lakukan Pembayaran',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.extension_approved',
            with: [
                'extension' => $this->extension,
                'paymentUrl' => $this->paymentUrl,
                'rental' => $this->extension->rental,
                'vehicle' => $this->extension->rental->vehicle,
                'user' => $this->extension->rental->user,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
