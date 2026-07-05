<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $mailSubject,
        public string $messageText,
        public string $event,
        public array $meta = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-status', with: [
            'order' => $this->order,
            'messageText' => $this->messageText,
            'event' => $this->event,
            'meta' => $this->meta,
            'headline' => $this->meta['headline'] ?? 'Order Update',
            'badge' => $this->meta['badge'] ?? 'Updated',
            'badgeBg' => $this->meta['badge_bg'] ?? '#F4F4F5',
            'badgeColor' => $this->meta['badge_color'] ?? '#0A0A0A',
            'orderUrl' => url(route('orders.show', $this->order, false)),
        ]);
    }
}
