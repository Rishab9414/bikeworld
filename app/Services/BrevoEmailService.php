<?php

namespace App\Services;

use Brevo\Brevo;
use Brevo\Exceptions\BrevoApiException;
use Brevo\TransactionalEmails\Requests\SendTransacEmailRequest;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestReplyTo;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender;
use Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\Address;

class BrevoEmailService
{
    public function __construct(private Brevo $client) {}

    /**
     * @param  array<int, array{email: string, name?: ?string}>  $to
     * @param  array<int, Address>  $from
     * @param  array<int, Address>  $replyTo
     */
    public function send(
        array $to,
        string $subject,
        ?string $html = null,
        ?string $text = null,
        array $from = [],
        array $replyTo = [],
    ): string {
        $apiKey = config('brevo.api_key');

        if (blank($apiKey)) {
            throw new \RuntimeException('BREVO_API_KEY is not set. Add it in .env (Brevo dashboard → SMTP & API → API Keys).');
        }

        if ($to === []) {
            throw new \InvalidArgumentException('At least one recipient is required.');
        }

        $sender = $this->resolveSender($from);

        $payload = [
            'subject' => $subject,
            'sender' => new SendTransacEmailRequestSender([
                'name' => $sender['name'],
                'email' => $sender['email'],
            ]),
            'to' => array_map(
                fn (array $recipient) => new SendTransacEmailRequestToItem([
                    'email' => $recipient['email'],
                    'name' => $recipient['name'] ?? null,
                ]),
                $to
            ),
        ];

        if ($html) {
            $payload['htmlContent'] = $html;
        }

        if ($text) {
            $payload['textContent'] = $text;
        }

        if ($replyTo !== []) {
            $address = $replyTo[0];
            $payload['replyTo'] = new SendTransacEmailRequestReplyTo([
                'email' => $address->getAddress(),
                'name' => $address->getName() ?: null,
            ]);
        }

        try {
            $result = $this->client->transactionalEmails->sendTransacEmail(
                new SendTransacEmailRequest($payload)
            );

            return (string) ($result->messageId ?? '');
        } catch (BrevoApiException $e) {
            Log::error('Brevo email API error', [
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
                'body' => $e->getBody(),
            ]);

            throw $e;
        }
    }

    /** @param  array<int, Address>  $from */
    private function resolveSender(array $from): array
    {
        if ($from !== []) {
            return [
                'email' => $from[0]->getAddress(),
                'name' => $from[0]->getName() ?: config('brevo.sender.name', config('mail.from.name')),
            ];
        }

        return [
            'email' => config('brevo.sender.email', config('mail.from.address')),
            'name' => config('brevo.sender.name', config('mail.from.name')),
        ];
    }
}
