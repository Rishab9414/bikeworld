<?php

namespace App\Services;

use App\Mail\OrderStatusMail;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function notifyOrderEvent(Order $order, string $event, ?string $customMessage = null): void
    {
        $email = $this->resolveEmail($order);
        if (! $email) {
            return;
        }

        $subjects = [
            'order_confirmed' => 'Your order has been confirmed',
            'shipment_created' => 'Shipment created for your order',
            'pickup_completed' => 'Your package has been picked up',
            'shipped' => 'Your order is on the way',
            'out_for_delivery' => 'Your order is out for delivery',
            'delivered' => 'Your order has been delivered',
            'return_approved' => 'Your return request has been approved',
            'refund_completed' => 'Your refund has been processed',
            'order_placed' => 'Order placed successfully',
            'payment_success' => 'Payment received for your order',
        ];

        $subject = $subjects[$event] ?? 'Order update';
        $message = $customMessage ?? $this->defaultMessage($order, $event);

        Mail::to($email)->send(new OrderStatusMail($order, $subject, $message, $event));
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
        return match ($event) {
            'order_confirmed' => "Order {$order->order_number} has been confirmed and is being prepared.",
            'shipment_created' => "Shipment has been created for order {$order->order_number}. AWB: ".($order->shipmentRecord?->waybill ?? 'pending'),
            'pickup_completed' => "Courier has picked up your package for order {$order->order_number}.",
            'shipped' => "Your order {$order->order_number} is in transit.",
            'out_for_delivery' => "Your order {$order->order_number} is out for delivery today.",
            'delivered' => "Your order {$order->order_number} has been delivered. Thank you for shopping with us!",
            'return_approved' => "Return for order {$order->order_number} has been approved.",
            'refund_completed' => "Refund for order {$order->order_number} has been completed.",
            'order_placed' => "Thank you! Your order {$order->order_number} has been placed.",
            'payment_success' => "Payment for order {$order->order_number} was successful.",
            default => "There is an update on your order {$order->order_number}.",
        };
    }
}
