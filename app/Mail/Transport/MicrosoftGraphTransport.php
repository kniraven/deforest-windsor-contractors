<?php

namespace App\Mail\Transport;

use App\Services\MicrosoftGraphMailService;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class MicrosoftGraphTransport extends AbstractTransport
{
    public function __construct(
        protected MicrosoftGraphMailService $graphMailService,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $this->graphMailService->sendSymfonyEmail($email);
    }

    public function __toString(): string
    {
        return 'graph';
    }
}