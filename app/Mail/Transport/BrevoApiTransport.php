<?php

namespace App\Mail\Transport;

use App\Services\BrevoEmailService;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class BrevoApiTransport extends AbstractTransport
{
    public function __construct(private BrevoEmailService $brevo) {}

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $to = collect($email->getTo())
            ->map(fn (Address $address) => [
                'email' => $address->getAddress(),
                'name' => $address->getName() ?: null,
            ])
            ->values()
            ->all();

        $html = $email->getHtmlBody();
        $text = $email->getTextBody();

        if (is_resource($html)) {
            $html = stream_get_contents($html) ?: null;
        }

        if (is_resource($text)) {
            $text = stream_get_contents($text) ?: null;
        }

        $this->brevo->send(
            to: $to,
            subject: $email->getSubject() ?? '',
            html: is_string($html) ? $html : null,
            text: is_string($text) ? $text : null,
            from: $email->getFrom(),
            replyTo: $email->getReplyTo(),
        );
    }

    public function __toString(): string
    {
        return 'brevo-api';
    }
}
