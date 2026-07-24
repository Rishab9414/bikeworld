<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private InventoryService $inventory,
        private PaymentService $payment,
        private NotificationService $notifications,
        private TaxService $tax,
        private CouponService $coupons,
    ) {}

    public function createFromCart(
        User $user,
        Collection $cartItems,
        array $data,
        string $paymentMethod = 'cod'
    ): Order {
        return DB::transaction(function () use ($user, $cartItems, $data, $paymentMethod) {
            if (! $this->inventory->checkAvailability($cartItems)) {
                throw new \RuntimeException('One or more items are out of stock.');
            }

            $customer = Customer::where('user_id', $user->id)->first();
            $taxSummary = $this->tax->summarizeCart($cartItems);
            $subtotal = $taxSummary['subtotal'];
            $tax = $taxSummary['tax_amount'];
            $itemsTotal = $cartItems->sum(fn ($i) => $i->subtotal());
            $shipping = (float) ($data['shipping_charge'] ?? config('delhivery.default_shipping_charge', 99));
            $couponDiscount = (float) ($data['coupon_discount'] ?? 0);
            $coupon = $data['coupon'] ?? null;
            $discount = $couponDiscount + (float) ($data['wallet_discount'] ?? 0);
            $grandTotal = max(0, $taxSummary['items_total'] - $couponDiscount + $shipping);

            $shippingText = $data['shipping_address'] ?? '';
            $billingText = $data['billing_address'] ?? $shippingText;
            $shippingJson = $data['shipping_address_json'] ?? $this->parseAddressJson($shippingText);
            $billingJson = $data['billing_address_json'] ?? $this->parseAddressJson($billingText);

            $order = Order::create([
                'user_id' => $user->id,
                'customer_id' => $customer?->id,
                'order_number' => $this->generateOrderNumber(),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'coupon_discount' => $couponDiscount,
                'shipping_charge' => $shipping,
                'tax_amount' => $tax,
                'grand_total' => $grandTotal,
                'total' => $grandTotal,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'expected_delivery' => $data['expected_delivery'] ?? null,
                'shipping_address' => $shippingText,
                'billing_address' => $billingText,
                'shipping_address_json' => $shippingJson,
                'billing_address_json' => $billingJson,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($cartItems as $item) {
                $product = $item->product;
                $lineSubtotal = $item->subtotal();
                $lineTax = $this->tax->lineBreakdown($product, $lineSubtotal);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $item->variant_id ?? null,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'gst' => $lineTax['tax_amount'],
                    'total' => $lineTax['inclusive_amount'],
                    'quantity' => $item->quantity,
                    'subtotal' => $lineTax['exclusive_amount'],
                    'weight' => $product->weight ?? config('delhivery.default_weight_kg'),
                    'status' => 'pending',
                ]);
            }

            $this->payment->processCheckoutPayment($order, $paymentMethod);
            $this->logStatus($order, 'pending', 'Order Placed', 'Customer placed the order');

            if ($coupon && $couponDiscount > 0) {
                $this->coupons->recordUsage($coupon, $user, $order, $couponDiscount);
            }

            $this->notifications->notifyOrderEvent($order, 'order_placed');

            if ($order->payment_status === 'paid') {
                $this->logStatus($order, 'pending', 'Payment Success', 'Payment received');
                $this->notifications->notifyOrderEvent($order, 'payment_success');
            }

            return $order->load('items.product');
        });
    }

    public function confirm(Order $order, string $actor = 'admin'): Order
    {
        $this->inventory->reserveStock($order);
        $order->update(['status' => 'confirmed']);
        $this->logStatus($order, 'confirmed', 'Order Confirmed', 'Stock reserved', $actor);
        $this->notifications->notifyOrderEvent($order, 'order_confirmed');

        return $order->fresh();
    }

    public function updateStatus(Order $order, string $status, string $title, ?string $remarks = null, ?string $actor = 'system'): Order
    {
        $order->update(['status' => $status]);
        $this->logStatus($order, $status, $title, $remarks, $actor);

        $this->notifications->notifyAdminStatusChange($order->fresh(), $status, $title, $remarks);

        return $order->fresh();
    }

    public function cancel(Order $order, string $reason = 'Cancelled by admin'): Order
    {
        $this->inventory->releaseStock($order);
        $order->update(['status' => 'cancelled']);
        $this->logStatus($order, 'cancelled', 'Order Cancelled', $reason);
        $this->notifications->notifyOrderEvent($order, 'order_cancelled', $reason);

        return $order->fresh();
    }

    public function refund(Order $order): Order
    {
        $this->payment->refund($order);
        $this->inventory->releaseStock($order);
        $this->logStatus($order, 'refunded', 'Refund Completed', 'Payment refunded');
        $this->notifications->notifyOrderEvent($order, 'refund_completed');

        return $order->fresh();
    }

    public function generateInvoice(Order $order): Invoice
    {
        if ($order->invoiceRecord) {
            return $order->invoiceRecord;
        }

        $invoiceNo = 'INV-'.date('Y').'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
        $html = view('admin.orders.invoice', compact('order'))->render();
        $path = 'invoices/'.$invoiceNo.'.html';
        \Storage::disk('public')->put($path, $html);

        $invoice = Invoice::create([
            'invoice_no' => $invoiceNo,
            'order_id' => $order->id,
            'invoice_pdf' => $path,
            'invoice_date' => now()->toDateString(),
        ]);

        $order->update(['invoice_id' => $invoice->id]);

        return $invoice;
    }

    public function timeline(Order $order): Collection
    {
        return $order->statusLogs;
    }

    public function logStatus(Order $order, string $status, string $title, ?string $remarks = null, ?string $actor = 'system'): void
    {
        OrderStatusLog::create([
            'order_id' => $order->id,
            'status' => $status,
            'title' => $title,
            'remarks' => $remarks,
            'actor' => $actor,
        ]);
    }

    private function generateOrderNumber(): string
    {
        return 'BW-'.date('Ymd').'-'.strtoupper(Str::random(6));
    }

    private function parseAddressJson(string $address): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $address)));

        return [
            'full_address' => $address,
            'line_1' => $lines[0] ?? $address,
            'line_2' => $lines[1] ?? '',
            'city' => $lines[2] ?? 'Mumbai',
            'state' => $lines[3] ?? 'Maharashtra',
            'pincode' => preg_match('/\b(\d{6})\b/', $address, $m) ? $m[1] : config('delhivery.pickup_pin'),
            'phone' => preg_match('/\b(\d{10})\b/', $address, $m) ? $m[1] : config('delhivery.pickup_phone'),
            'name' => $lines[0] ?? 'Customer',
        ];
    }
}
