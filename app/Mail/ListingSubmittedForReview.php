<?php

namespace App\Mail;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingSubmittedForReview extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Listing $listing,
        public array $supportingDocuments = [],
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New directory listing submitted for review',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.listings.submitted-for-review',
        );
    }

    public function attachments(): array
    {
        return collect($this->supportingDocuments)
            ->map(function (array $document) {
                return Attachment::fromData(
                    fn () => $document['content'],
                    $document['name']
                )->withMime($document['mime']);
            })
            ->all();
    }
}