<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VehicleAvailabilityNotification extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public $paymentUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($data, $paymentUrl)
    {
        $this->data = $data;
        $this->paymentUrl = $paymentUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kendaraan Tersedia: Reservasi Anda Telah Dikonfirmasi - CosmoRent',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'response_email.response_confirmed_reservation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

}
