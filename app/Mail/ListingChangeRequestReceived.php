<?php

namespace App\Mail;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingChangeRequestReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Listing $listing, public array $requestData)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We received your listing request',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.listings.change-request-received',
        );
    }
}