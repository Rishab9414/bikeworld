<?php

namespace App\Services;

use App\Models\Order;

class PaymentService
{
    public function markPaid(Order $order, string $method = 'online'): Order
    {
        $order->update([
            'payment_method' => $method,
            'payment_status' => 'paid',
        ]);

        return $order->fresh();
    }

    public function markPending(Order $order, string $method = 'cod'): Order
    {
        $order->update([
            'payment_method' => $method,
            'payment_status' => 'pending',
        ]);

        return $order->fresh();
    }

    public function processCheckoutPayment(Order $order, string $method): Order
    {
        if (in_array($method, ['cod', 'cash_on_delivery'], true)) {
            return $this->markPending($order, 'cod');
        }

        if ($method === 'online') {
            return $this->markPending($order, 'online');
        }

        return $this->markPaid($order, $method);
    }

    public function markFailed(Order $order): Order
    {
        $order->update(['payment_status' => 'failed']);

        return $order->fresh();
    }

    public function refund(Order $order, ?float $amount = null): Order
    {
        $order->update([
            'payment_status' => 'refunded',
            'status' => 'refunded',
        ]);

        return $order->fresh();
    }
}
