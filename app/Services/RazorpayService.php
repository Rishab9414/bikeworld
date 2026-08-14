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
            $this->completePayment(
                $order,
                $razorpayPaymentId ?: 'pay_mock_'.Str::lower(Str::random(14)),
                'verify'
            );

            return $order->fresh();
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

        $this->completePayment($order, $razorpayPaymentId, 'verify');

        return $order->fresh();
    }

    public function handleWebhook(array $payload): void
    {
        $event = $payload['event'] ?? null;

        match ($event) {
            'payment.captured', 'payment.authorized' => $this->handlePaymentCaptured(
                $payload['payload']['payment']['entity'] ?? []
            ),
            'order.paid' => $this->handleOrderPaid($payload),
            'payment.failed' => $this->handlePaymentFailed(
                $payload['payload']['payment']['entity'] ?? []
            ),
            default => null,
        };
    }

    /**
     * Check Razorpay API and mark the local order paid when payment already succeeded.
     */
    public function syncPaymentStatus(Order $order, string $source = 'sync'): bool
    {
        if ($order->payment_status === 'paid') {
            return true;
        }

        if ($order->payment_method !== 'online' || ! $order->razorpay_order_id) {
            return false;
        }

        if ($this->usesMockMode() || str_starts_with($order->razorpay_order_id, 'order_mock_')) {
            return false;
        }

        try {
            $razorpayOrder = $this->api()->order->fetch($order->razorpay_order_id);
            $payments = $this->api()->order->fetch($order->razorpay_order_id)->payments();
            $items = $payments['items'] ?? [];

            foreach ($items as $payment) {
                $status = $payment['status'] ?? '';
                if (! in_array($status, ['captured', 'authorized'], true)) {
                    continue;
                }

                return $this->completePayment(
                    $order,
                    (string) ($payment['id'] ?? ''),
                    $source
                );
            }

            if (($razorpayOrder['status'] ?? '') === 'paid' && ! empty($items[0]['id'])) {
                return $this->completePayment($order, (string) $items[0]['id'], $source);
            }
        } catch (\Throwable $e) {
            Log::warning('Razorpay payment sync failed', [
                'order_id' => $order->id,
                'razorpay_order_id' => $order->razorpay_order_id,
                'message' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * @return array{checked:int,updated:array<int,array<string,mixed>>,still_pending:array<int,array<string,mixed>>,errors:array<int,array<string,mixed>>,skipped:int}
     */
    public function syncAllPendingPayments(int $limit = 100, int $days = 60): array
    {
        $results = [
            'checked' => 0,
            'updated' => [],
            'still_pending' => [],
            'errors' => [],
            'skipped' => 0,
        ];

        if ($this->usesMockMode()) {
            return $results;
        }

        $orders = Order::query()
            ->where('payment_method', 'online')
            ->whereIn('payment_status', ['pending', 'failed'])
            ->whereNotNull('razorpay_order_id')
            ->where('razorpay_order_id', 'not like', 'order_mock_%')
            ->where('created_at', '>=', now()->subDays($days))
            ->latest('id')
            ->limit($limit)
            ->get(['id', 'order_number', 'payment_status', 'razorpay_order_id', 'grand_total', 'total', 'created_at', 'paid_at', 'razorpay_payment_id']);

        foreach ($orders as $order) {
            if ($order->payment_status === 'paid') {
                $results['skipped']++;

                continue;
            }

            $results['checked']++;

            try {
                if ($this->syncPaymentStatus($order, 'cron')) {
                    $order->refresh();
                    $results['updated'][] = [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'razorpay_payment_id' => $order->razorpay_payment_id,
                        'amount' => $order->displayTotal(),
                        'paid_at' => $order->paid_at?->format('Y-m-d H:i:s'),
                    ];
                } else {
                    $results['still_pending'][] = [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'payment_status' => $order->payment_status,
                        'razorpay_order_id' => $order->razorpay_order_id,
                        'amount' => $order->displayTotal(),
                        'created_at' => $order->created_at?->format('Y-m-d H:i:s'),
                    ];
                }
            } catch (\Throwable $e) {
                Log::error('Payment cron sync failed for order', [
                    'order_id' => $order->id,
                    'message' => $e->getMessage(),
                ]);

                $results['errors'][] = [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public function completePayment(Order $order, string $razorpayPaymentId, string $source = 'razorpay'): bool
    {
        if ($order->payment_status === 'paid') {
            return true;
        }

        if ($razorpayPaymentId === '') {
            return false;
        }

        $this->markOrderPaid($order, $razorpayPaymentId);
        $fresh = $order->fresh();
        app(OrderService::class)->logStatus(
            $fresh,
            $fresh->status,
            'Payment Success',
            match ($source) {
                'webhook' => 'Payment confirmed via Razorpay webhook',
                'sync' => 'Payment confirmed via Razorpay sync',
                'admin' => 'Payment confirmed via admin sync',
                'cron' => 'Payment confirmed via payment sync cron',
                'verify' => 'Razorpay payment verified',
                default => 'Razorpay payment confirmed',
            },
            $source === 'verify' ? 'customer' : $source
        );
        app(NotificationService::class)->notifyOrderEvent($fresh, 'payment_success');

        return true;
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

        if (! $order) {
            return;
        }

        $this->completePayment($order, $razorpayPaymentId, 'webhook');
    }

    private function handleOrderPaid(array $payload): void
    {
        $orderEntity = $payload['payload']['order']['entity'] ?? [];
        $paymentEntity = $payload['payload']['payment']['entity'] ?? [];
        $razorpayOrderId = $orderEntity['id'] ?? null;
        $razorpayPaymentId = $paymentEntity['id'] ?? null;

        if (! $razorpayOrderId) {
            return;
        }

        $order = Order::where('razorpay_order_id', $razorpayOrderId)->first();

        if (! $order) {
            return;
        }

        if (! $razorpayPaymentId && ! empty($orderEntity['id'])) {
            $this->syncPaymentStatus($order, 'webhook');

            return;
        }

        if ($razorpayPaymentId) {
            $this->completePayment($order, $razorpayPaymentId, 'webhook');
        }
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
