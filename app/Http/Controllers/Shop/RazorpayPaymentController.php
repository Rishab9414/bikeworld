<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\NotificationService;
use App\Services\OrderService;
use App\Services\RazorpayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RazorpayPaymentController extends Controller
{
    public function __construct(
        private RazorpayService $razorpay,
        private OrderService $orders,
        private NotificationService $notifications,
    ) {}

    public function show(Order $order): View|RedirectResponse
    {
        $this->authorizeOrder($order);

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.show', $order)->with('success', 'This order is already paid.');
        }

        if ($order->payment_method !== 'online') {
            return redirect()->route('orders.show', $order);
        }

        if (! $order->razorpay_order_id) {
            $this->razorpay->createRazorpayOrder($order);
            $order->refresh();
        }

        $order->loadCount('items');

        $checkout = $this->razorpay->checkoutOptions($order, [
            'contact' => $order->shipping_address_json['phone'] ?? null,
        ]);

        return view('shop.orders.payment', compact('order', 'checkout'));
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['nullable', 'string'],
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $this->authorizeOrder($order);

        try {
            $isMock = $this->razorpay->usesMockMode() || str_starts_with($validated['razorpay_order_id'], 'order_mock_');

            if ($isMock) {
                $this->razorpay->verifyPayment(
                    $order,
                    $validated['razorpay_payment_id'],
                    $validated['razorpay_order_id'],
                    $validated['razorpay_signature'] ?? 'mock_signature'
                );
            } else {
                $request->validate([
                    'razorpay_signature' => ['required', 'string'],
                ]);
                $this->razorpay->verifyPayment(
                    $order,
                    $validated['razorpay_payment_id'],
                    $validated['razorpay_order_id'],
                    $validated['razorpay_signature']
                );
            }

            $this->orders->logStatus($order->fresh(), 'pending', 'Payment Success', 'Razorpay payment verified', 'customer');
            $this->notifications->notifyOrderEvent($order->fresh(), 'payment_success');
        } catch (\Throwable $e) {
            return redirect()
                ->route('orders.payment', $order)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('orders.confirmation', $order)
            ->with('success', 'Payment successful! Your order has been placed.');
    }

    private function authorizeOrder(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 403);
    }
}
