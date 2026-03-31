<?php

namespace App\Mail;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingChangeRequestSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Listing $listing,
        public array $requestData,
        public array $supportingDocuments = []
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Listing update/takedown request received',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.listings.change-request-submitted',
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