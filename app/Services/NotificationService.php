<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Mail\OrderStatusMail;
use App\Models\Order;
use App\Models\OrderEmailLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function __construct(private MessagingService $messaging) {}

    /** @var array<string, array{subject: string, headline: string, badge: string, badge_bg: string, badge_color: string}> */
    private array $events = [
        'order_placed' => [
            'subject' => 'Order placed successfully',
            'headline' => 'Thank you for your order!',
            'badge' => 'Order Placed',
            'badge_bg' => '#FEE2E2',
            'badge_color' => '#E31E24',
        ],
        'payment_success' => [
            'subject' => 'Payment received',
            'headline' => 'Payment confirmed',
            'badge' => 'Paid',
            'badge_bg' => '#D1FAE5',
            'badge_color' => '#059669',
        ],
        'order_confirmed' => [
            'subject' => 'Your order is confirmed',
            'headline' => 'We\'re preparing your order',
            'badge' => 'Confirmed',
            'badge_bg' => '#DBEAFE',
            'badge_color' => '#2563EB',
        ],
        'order_packing' => [
            'subject' => 'Your order is being packed',
            'headline' => 'Packing in progress',
            'badge' => 'Packing',
            'badge_bg' => '#FEF3C7',
            'badge_color' => '#D97706',
        ],
        'order_packed' => [
            'subject' => 'Your order has been packed',
            'headline' => 'Ready for dispatch',
            'badge' => 'Packed',
            'badge_bg' => '#FEF3C7',
            'badge_color' => '#D97706',
        ],
        'shipment_created' => [
            'subject' => 'Shipment created for your order',
            'headline' => 'Your package is on its way soon',
            'badge' => 'Shipped',
            'badge_bg' => '#E0E7FF',
            'badge_color' => '#4F46E5',
        ],
        'pickup_scheduled' => [
            'subject' => 'Pickup scheduled for your order',
            'headline' => 'Courier pickup scheduled',
            'badge' => 'Pickup Scheduled',
            'badge_bg' => '#E0E7FF',
            'badge_color' => '#4F46E5',
        ],
        'pickup_completed' => [
            'subject' => 'Package picked up',
            'headline' => 'Courier has collected your package',
            'badge' => 'Picked Up',
            'badge_bg' => '#E0E7FF',
            'badge_color' => '#4F46E5',
        ],
        'shipped' => [
            'subject' => 'Your order is on the way',
            'headline' => 'In transit to your address',
            'badge' => 'In Transit',
            'badge_bg' => '#E0E7FF',
            'badge_color' => '#4F46E5',
        ],
        'out_for_delivery' => [
            'subject' => 'Out for delivery today',
            'headline' => 'Your order arrives today',
            'badge' => 'Out for Delivery',
            'badge_bg' => '#FEF3C7',
            'badge_color' => '#D97706',
        ],
        'delivered' => [
            'subject' => 'Order delivered successfully',
            'headline' => 'Enjoy your ride!',
            'badge' => 'Delivered',
            'badge_bg' => '#D1FAE5',
            'badge_color' => '#059669',
        ],
        'order_completed' => [
            'subject' => 'Order completed',
            'headline' => 'Thank you for shopping with us',
            'badge' => 'Completed',
            'badge_bg' => '#D1FAE5',
            'badge_color' => '#059669',
        ],
        'order_cancelled' => [
            'subject' => 'Order cancelled',
            'headline' => 'Your order has been cancelled',
            'badge' => 'Cancelled',
            'badge_bg' => '#FEE2E2',
            'badge_color' => '#DC2626',
        ],
        'return_initiated' => [
            'subject' => 'Return request received',
            'headline' => 'We\'re processing your return',
            'badge' => 'Return Initiated',
            'badge_bg' => '#FEF3C7',
            'badge_color' => '#D97706',
        ],
        'return_approved' => [
            'subject' => 'Return approved',
            'headline' => 'Your return has been approved',
            'badge' => 'Return Approved',
            'badge_bg' => '#D1FAE5',
            'badge_color' => '#059669',
        ],
        'refund_completed' => [
            'subject' => 'Refund processed',
            'headline' => 'Your refund is on its way',
            'badge' => 'Refunded',
            'badge_bg' => '#D1FAE5',
            'badge_color' => '#059669',
        ],
        'status_updated' => [
            'subject' => 'Order status updated',
            'headline' => 'There\'s an update on your order',
            'badge' => 'Updated',
            'badge_bg' => '#F4F4F5',
            'badge_color' => '#0A0A0A',
        ],
    ];

    public function notifyOrderEvent(Order $order, string $event, ?string $customMessage = null, ?string $customSubject = null): void
    {
        $order = $order->fresh(['items', 'customer', 'user', 'shipmentRecord']);
        $email = $this->resolveEmail($order);

        if (! $email) {
            return;
        }

        $meta = $this->eventMeta($event);
        $subject = $customSubject ?? $meta['subject'].' — '.$order->order_number;

        if (! $this->reserveNotification($order->id, $event, $email, $subject)) {
            return;
        }

        if (config('brevo.queue_notifications', true) && config('queue.default') !== 'sync') {
            SendEmailJob::dispatch($order->id, $event, $customMessage, $customSubject);
            $this->queueSmsIfEnabled($order, $event, $customMessage);

            return;
        }

        $this->sendOrderEvent($order, $event, $customMessage, $customSubject);
    }

    public function sendOrderEvent(Order $order, string $event, ?string $customMessage = null, ?string $customSubject = null): void
    {
        $order = $order->fresh(['items', 'customer', 'user', 'shipmentRecord']);
        $email = $this->resolveEmail($order);

        if (! $email) {
            return;
        }

        $meta = $this->eventMeta($event);
        $subject = $customSubject ?? $meta['subject'].' — '.$order->order_number;
        $message = $customMessage ?? $this->defaultMessage($order, $event);

        try {
            Mail::to($email)->send(new OrderStatusMail(
                order: $order,
                mailSubject: $subject,
                messageText: $message,
                event: $event,
                meta: $meta,
            ));
        } catch (\Throwable $e) {
            OrderEmailLog::where('order_id', $order->id)->where('event', $event)->delete();

            Log::error('Order email failed', [
                'order_id' => $order->id,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        $this->queueSmsIfEnabled($order, $event, $message);
    }

    private function queueSmsIfEnabled(Order $order, string $event, ?string $message): void
    {
        if (! config('brevo.sms_enabled') && ! config('brevo.whatsapp_enabled')) {
            return;
        }

        $text = $message ?? $this->defaultMessage($order, $event);
        $this->messaging->notifyOrderChannels($order, $event, $text);
    }

    public function notifyAdminStatusChange(Order $order, string $status, string $title, ?string $remarks = null): void
    {
        $baseEvent = $this->statusToEvent($status);

        if ($baseEvent === null) {
            return;
        }

        // Unique log key per status so every status change can email the customer.
        $event = $baseEvent === 'status_updated' ? 'status_'.$status : $baseEvent;

        $message = $this->defaultMessage($order, $baseEvent);
        if ($remarks) {
            $message .= ' Note: '.$remarks;
        }

        $subject = ($this->eventMeta($baseEvent)['subject']).' — '.$order->order_number;

        $this->notifyOrderEvent($order, $event, $message, $subject);
    }

    public function statusToEvent(string $status): ?string
    {
        return match ($status) {
            'confirmed' => 'order_confirmed',
            'packing' => 'order_packing',
            'packed' => 'order_packed',
            'shipment_created' => 'shipment_created',
            'pickup_scheduled' => 'pickup_scheduled',
            'picked_up' => 'pickup_completed',
            'shipped', 'in_transit', 'reached_destination_hub' => 'shipped',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            'completed' => 'order_completed',
            'cancelled' => 'order_cancelled',
            'returned' => 'return_initiated',
            'refunded' => 'refund_completed',
            'pending' => null,
            default => 'status_updated',
        };
    }

    public function eventMeta(string $event): array
    {
        if (isset($this->events[$event])) {
            return $this->events[$event];
        }

        // status_{name} keys from generic status updates
        if (str_starts_with($event, 'status_')) {
            $meta = $this->events['status_updated'];
            $label = str_replace('_', ' ', substr($event, 7));
            $meta['badge'] = ucwords($label);
            $meta['subject'] = 'Order status updated';

            return $meta;
        }

        return $this->events['status_updated'];
    }

    private function reserveNotification(int $orderId, string $event, string $email, string $subject): bool
    {
        $log = OrderEmailLog::firstOrCreate(
            ['order_id' => $orderId, 'event' => $event],
            ['recipient' => $email, 'subject' => $subject, 'sent_at' => now()]
        );

        return $log->wasRecentlyCreated;
    }

    private function resolveEmail(Order $order): ?string
    {
        if ($order->customer?->email) {
            return $order->customer->email;
        }

        return $order->user?->email;
    }

    private function defaultMessage(Order $order, string $event): string
    {
        $customerName = $order->customer?->full_name ?? $order->user?->name ?? 'Customer';
        $waybill = $order->shipmentRecord?->waybill ?? $order->shipment?->waybill;
        $total = '₹'.number_format($order->displayTotal(), 2);
        $paymentMethod = strtoupper($order->payment_method ?? 'COD');
        $paymentRef = $order->razorpay_payment_id ?: $order->razorpay_order_id;

        return match ($event) {
            'order_placed' => "Hi {$customerName}, thank you for shopping with ".config('app.name')."! Your order {$order->order_number} ({$total}) has been placed successfully. We'll notify you at every step.",
            'payment_success' => "Hi {$customerName}, we received your payment of {$total} for order {$order->order_number} via {$paymentMethod}."
                .($paymentRef ? " Payment reference: {$paymentRef}." : '')
                .' Your order is now being processed.',
            'order_confirmed' => "Hi {$customerName}, great news! Order {$order->order_number} ({$total}) has been confirmed and our team is preparing your items.",
            'order_packing' => "Hi {$customerName}, your order {$order->order_number} is being carefully packed and will ship soon.",
            'order_packed' => "Hi {$customerName}, your order {$order->order_number} has been packed and is ready for dispatch.",
            'shipment_created' => "Hi {$customerName}, shipment has been created for order {$order->order_number}."
                .($waybill ? " Tracking AWB: {$waybill}." : ''),
            'pickup_scheduled' => "Hi {$customerName}, courier pickup has been scheduled for order {$order->order_number}."
                .($waybill ? " Tracking AWB: {$waybill}." : ''),
            'pickup_completed' => "Hi {$customerName}, the courier has picked up your package for order {$order->order_number}."
                .($waybill ? " Tracking AWB: {$waybill}." : ''),
            'shipped' => "Hi {$customerName}, your order {$order->order_number} is in transit and on its way to you."
                .($waybill ? " Tracking AWB: {$waybill}." : ''),
            'out_for_delivery' => "Hi {$customerName}, your order {$order->order_number} is out for delivery and should arrive today!"
                .($waybill ? " Tracking AWB: {$waybill}." : ''),
            'delivered' => "Hi {$customerName}, your order {$order->order_number} has been delivered. We hope you love your purchase!",
            'order_completed' => "Hi {$customerName}, order {$order->order_number} is now complete. Thank you for choosing ".config('app.name').'!',
            'order_cancelled' => "Hi {$customerName}, order {$order->order_number} has been cancelled. If you have questions, please contact our support team.",
            'return_initiated', 'return_approved' => "Hi {$customerName}, your return request for order {$order->order_number} has been received and is being processed.",
            'refund_completed' => "Hi {$customerName}, refund for order {$order->order_number} ({$total}) has been processed. It may take 5–7 business days to reflect in your account.",
            default => "Hi {$customerName}, there is an update on your order {$order->order_number}. Current status: ".$order->statusLabel().'.',
        };
    }
}
