<?php

namespace App\Mail;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingSubmissionReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Listing $listing)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We received your directory listing submission',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.listings.submission-received',
        );
    }
}