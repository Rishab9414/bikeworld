<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayService
{
    public function hasCredentials(): bool
    {
        return filled(config('razorpay.key_id')) && filled(config('razorpay.key_secret'));
    }

    public function usesMockMode(): bool
    {
        return (bool) config('razorpay.mock') || ! $this->hasCredentials();
    }

    public function isConfigured(): bool
    {
        return $this->usesMockMode() || $this->hasCredentials();
    }

    public function isAvailable(): bool
    {
        return $this->isConfigured();
    }

    public function createRazorpayOrder(Order $order): Order
    {
        if ($order->razorpay_order_id) {
            return $order;
        }

        $amountPaise = $this->amountInPaise($order);

        if ($this->usesMockMode()) {
            $mockId = 'order_mock_'.Str::lower(Str::random(14));
            $order->update(['razorpay_order_id' => $mockId]);

            Log::info('Razorpay mock order created', ['order_id' => $order->id, 'razorpay_order_id' => $mockId]);

            return $order->fresh();
        }

        $api = $this->api();
        $razorpayOrder = $api->order->create([
            'receipt' => $order->order_number,
            'amount' => $amountPaise,
            'currency' => config('razorpay.currency', 'INR'),
            'notes' => [
                'local_order_id' => (string) $order->id,
                'order_number' => $order->order_number,
            ],
        ]);

        $order->update(['razorpay_order_id' => $razorpayOrder['id']]);

        Log::info('Razorpay order created', [
            'order_id' => $order->id,
            'razorpay_order_id' => $razorpayOrder['id'],
            'amount_paise' => $amountPaise,
        ]);

        return $order->fresh();
    }

    public function verifyPayment(Order $order, string $razorpayPaymentId, string $razorpayOrderId, string $signature): Order
    {
        if ($order->payment_status === 'paid') {
            return $order;
        }

        if ($order->razorpay_order_id !== $razorpayOrderId) {
            throw new \InvalidArgumentException('Razorpay order ID does not match.');
        }

        if ($this->usesMockMode() || str_starts_with($razorpayOrderId, 'order_mock_')) {
            return $this->markOrderPaid($order, $razorpayPaymentId ?: 'pay_mock_'.Str::lower(Str::random(14)));
        }

        try {
            $this->api()->utility->verifyPaymentSignature([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $signature,
            ]);
        } catch (SignatureVerificationError $e) {
            Log::warning('Razorpay signature verification failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Payment verification failed. Please contact support.');
        }

        return $this->markOrderPaid($order, $razorpayPaymentId);
    }

    public function handleWebhook(array $payload): void
    {
        $event = $payload['event'] ?? null;

        if ($event === 'payment.captured') {
            $payment = $payload['payload']['payment']['entity'] ?? [];
            $this->handlePaymentCaptured($payment);

            return;
        }

        if ($event === 'payment.failed') {
            $payment = $payload['payload']['payment']['entity'] ?? [];
            $this->handlePaymentFailed($payment);
        }
    }

    public function checkoutOptions(Order $order, array $prefill = []): array
    {
        return [
            'key' => config('razorpay.key_id') ?: 'rzp_test_mock',
            'amount' => $this->amountInPaise($order),
            'currency' => config('razorpay.currency', 'INR'),
            'name' => config('razorpay.company_name'),
            'description' => 'Order '.$order->order_number,
            'order_id' => $order->razorpay_order_id,
            'prefill' => array_filter([
                'name' => $prefill['name'] ?? auth()->user()?->name,
                'email' => $prefill['email'] ?? auth()->user()?->email,
                'contact' => $prefill['contact'] ?? null,
            ]),
            'theme' => ['color' => '#E31E24'],
            'mock' => $this->usesMockMode() || str_starts_with($order->razorpay_order_id ?? '', 'order_mock_'),
            'live' => ! $this->usesMockMode(),
        ];
    }

    public function amountInPaise(Order $order): int
    {
        return (int) round($order->displayTotal() * 100);
    }

    private function markOrderPaid(Order $order, string $razorpayPaymentId): Order
    {
        $order->update([
            'razorpay_payment_id' => $razorpayPaymentId,
            'payment_method' => 'online',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        return $order->fresh();
    }

    private function handlePaymentCaptured(array $payment): void
    {
        $razorpayOrderId = $payment['order_id'] ?? null;
        $razorpayPaymentId = $payment['id'] ?? null;

        if (! $razorpayOrderId || ! $razorpayPaymentId) {
            return;
        }

        $order = Order::where('razorpay_order_id', $razorpayOrderId)->first();

        if (! $order || $order->payment_status === 'paid') {
            return;
        }

        $this->markOrderPaid($order, $razorpayPaymentId);
        app(OrderService::class)->logStatus($order, 'pending', 'Payment Success', 'Payment captured via Razorpay webhook', 'razorpay');
        app(NotificationService::class)->notifyOrderEvent($order->fresh(), 'payment_success');
    }

    private function handlePaymentFailed(array $payment): void
    {
        $razorpayOrderId = $payment['order_id'] ?? null;

        if (! $razorpayOrderId) {
            return;
        }

        $order = Order::where('razorpay_order_id', $razorpayOrderId)->first();

        if (! $order || $order->payment_status === 'paid') {
            return;
        }

        app(PaymentService::class)->markFailed($order);
        app(OrderService::class)->logStatus($order, 'pending', 'Payment Failed', $payment['error_description'] ?? 'Payment failed', 'razorpay');
    }

    private function api(): Api
    {
        return new Api(config('razorpay.key_id'), config('razorpay.key_secret'));
    }
}
