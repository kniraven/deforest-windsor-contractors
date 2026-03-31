<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MicrosoftGraphMailService
{
    private const ACCESS_TOKEN_CACHE_KEY = 'microsoft_graph_mail_access_token';

    public function isConfigured(): bool
    {
        return filled($this->tenantId())
            && filled($this->clientId())
            && filled($this->clientSecret())
            && filled($this->senderUserId());
    }

    /**
     * Convenience helper for manual testing in Tinker.
     */
    public function sendHtmlMessage(
        array $to,
        string $subject,
        string $html,
        array $cc = [],
        array $bcc = []
    ): void {
        $email = new Email;

        $fromAddress = trim((string) config('mail.from.address'));
        $fromName = trim((string) config('mail.from.name'));

        if ($fromAddress !== '') {
            $email->from(new Address($fromAddress, $fromName));
        }

        $toAddresses = $this->makeAddresses($to);
        $ccAddresses = $this->makeAddresses($cc);
        $bccAddresses = $this->makeAddresses($bcc);

        if ($toAddresses !== []) {
            $email->to(...$toAddresses);
        }

        if ($ccAddresses !== []) {
            $email->cc(...$ccAddresses);
        }

        if ($bccAddresses !== []) {
            $email->bcc(...$bccAddresses);
        }

        $email->subject($subject);
        $email->html($html);
        $email->text($this->htmlToText($html));

        $this->sendSymfonyEmail($email);
    }

    /**
     * Send a fully rendered Symfony email through Microsoft Graph using
     * raw MIME so Laravel mailables / notifications can pass through
     * with minimal loss of fidelity.
     */
    public function sendSymfonyEmail(Email $email): void
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Microsoft Graph mail is not fully configured.');
        }

        $mime = $email->toString();

        if (trim($mime) === '') {
            throw new RuntimeException('Cannot send an empty MIME message to Microsoft Graph.');
        }

        $this->sendRawMimeMessage($mime);
    }

    public function sendRawMimeMessage(string $mime): void
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Microsoft Graph mail is not fully configured.');
        }

        $response = Http::acceptJson()
            ->timeout(15)
            ->withToken($this->getAccessToken())
            ->withBody(base64_encode($mime), 'text/plain')
            ->post(
                $this->graphBaseUrl() . '/users/' . rawurlencode($this->senderUserId()) . '/sendMail'
            );

        if ($response->status() !== 202) {
            throw new RuntimeException(
                'Microsoft Graph sendMail failed with status ' . $response->status() . ': ' . $response->body()
            );
        }
    }

    private function getAccessToken(): string
    {
        $cachedToken = Cache::get(self::ACCESS_TOKEN_CACHE_KEY);

        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        $response = Http::asForm()
            ->acceptJson()
            ->timeout(15)
            ->post(
                'https://login.microsoftonline.com/' . rawurlencode($this->tenantId()) . '/oauth2/v2.0/token',
                [
                    'client_id' => $this->clientId(),
                    'client_secret' => $this->clientSecret(),
                    'scope' => 'https://graph.microsoft.com/.default',
                    'grant_type' => 'client_credentials',
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Microsoft Graph token request failed with status ' . $response->status() . ': ' . $response->body()
            );
        }

        $accessToken = (string) $response->json('access_token');

        if ($accessToken === '') {
            throw new RuntimeException('Microsoft Graph token response did not include an access token.');
        }

        $expiresIn = (int) $response->json('expires_in', 3600);
        $cacheSeconds = max($expiresIn - 60, 60);

        Cache::put(
            self::ACCESS_TOKEN_CACHE_KEY,
            $accessToken,
            now()->addSeconds($cacheSeconds)
        );

        return $accessToken;
    }

    /**
     * @return array<int, Address>
     */
    private function makeAddresses(array $recipients): array
    {
        $addresses = [];

        foreach ($recipients as $recipient) {
            $email = trim((string) $recipient);

            if ($email === '') {
                continue;
            }

            $addresses[] = new Address($email);
        }

        return $addresses;
    }

    private function htmlToText(string $html): string
    {
        $lineBreakNormalized = preg_replace('/<br\s*\/?>/i', PHP_EOL, $html) ?? $html;
        $text = trim(strip_tags($lineBreakNormalized));

        return $text === '' ? ' ' : $text;
    }

    private function tenantId(): string
    {
        return trim((string) config('services.microsoft_graph.tenant_id'));
    }

    private function clientId(): string
    {
        return trim((string) config('services.microsoft_graph.client_id'));
    }

    private function clientSecret(): string
    {
        return trim((string) config('services.microsoft_graph.client_secret'));
    }

    private function senderUserId(): string
    {
        return trim((string) config('services.microsoft_graph.sender_user_id'));
    }

    private function graphBaseUrl(): string
    {
        return rtrim(
            (string) config('services.microsoft_graph.base_url', 'https://graph.microsoft.com/v1.0'),
            '/'
        );
    }
}