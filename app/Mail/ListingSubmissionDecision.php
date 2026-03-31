<?php

namespace App\Mail;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingSubmissionDecision extends Mailable
{
    use Queueable, SerializesModels;

    public string $decision;

    public function __construct(public Listing $listing, string $decision)
    {
        $this->decision = strtolower(trim($decision));
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->decision) {
            'approved' => 'Your directory listing was approved',
            'rejected' => 'Your directory listing was not approved',
            default => 'Update on your directory listing',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.listings.submission-decision',
        );
    }
}