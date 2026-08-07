<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Shipment;
use App\Models\ShipmentTracking;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ManualShippingService
{
    public function defaultProductShipping(): float
    {
        return (float) config('shipping.default_product_shipping', 99);
    }

    public function productShippingCost(Product $product): float
    {
        if ($product->free_shipping) {
            return 0;
        }

        $cost = $product->shipping_cost;

        if ($cost === null || (float) $cost <= 0) {
            return $this->defaultProductShipping();
        }

        return (float) $cost;
    }

    /**
     * @return array{success: bool, amount: float, source: string, note: ?string, breakdown: array<int, array{product: string, qty: int, unit: float, line: float}>}
     */
    public function calculateForCart(Collection $items, ?float $orderAmount = null): array
    {
        if ($items->isEmpty()) {
            return $this->result(0, 'empty', 'Cart is empty');
        }

        if ($orderAmount === null) {
            $orderAmount = (float) $items->sum(fn ($item) => $item->subtotal());
        }

        if (Setting::qualifiesForFreeShipping($orderAmount)) {
            return $this->result(
                0,
                'free_shipping_threshold',
                'Free shipping on orders above ₹'.number_format(Setting::freeShippingMinAmount(), 0)
            );
        }

        if ($items->every(fn ($item) => $item->product->free_shipping)) {
            return $this->result(0, 'free_shipping', 'Free shipping on all items');
        }

        $total = 0;
        $breakdown = [];

        foreach ($items as $item) {
            $product = $item->product;
            $quantity = max(1, (int) $item->quantity);
            $unit = $this->productShippingCost($product);
            $line = round($unit * $quantity, 2);
            $total += $line;

            $breakdown[] = [
                'product' => $product->name,
                'qty' => $quantity,
                'unit' => $unit,
                'line' => $line,
            ];
        }

        return $this->result(
            $total,
            'manual',
            'Shipping calculated from product shipping charges',
            ['breakdown' => $breakdown]
        );
    }

    public function shippingCostForWeight(?float $weightKg): float
    {
        $weight = (float) ($weightKg ?? 0);

        if ($weight <= 0 || $weight < 1) {
            return 99;
        }

        if ($weight <= 2) {
            return 150;
        }

        return 300;
    }

    public function createOrUpdateShipment(Order $order, array $data): Shipment
    {
        $order->loadMissing('shipmentRecord', 'shipment');

        $shipment = $order->shipmentRecord ?? $order->shipment ?? new Shipment(['order_id' => $order->id]);

        $trackingNumber = trim((string) ($data['tracking_number'] ?? $data['waybill'] ?? ''));
        $courierName = trim((string) ($data['courier_name'] ?? 'Manual')) ?: 'Manual';
        $trackingUrl = trim((string) ($data['tracking_url'] ?? ''));

        if ($trackingUrl === '' && $trackingNumber !== '') {
            $trackingUrl = null;
        }

        $shipment->fill([
            'courier_name' => $courierName,
            'waybill' => $trackingNumber ?: $shipment->waybill,
            'tracking_number' => $trackingNumber ?: $shipment->tracking_number,
            'tracking_url' => $trackingUrl ?: $shipment->tracking_url,
            'shipping_cost' => $data['shipping_cost'] ?? $shipment->shipping_cost ?? $order->shipping_charge,
            'estimated_delivery' => $data['estimated_delivery'] ?? $shipment->estimated_delivery,
            'shipment_status' => $data['shipment_status'] ?? $shipment->shipment_status ?? 'shipped',
        ]);

        $shipment->order_id = $order->id;
        $shipment->save();

        if ($order->shipment_id !== $shipment->id) {
            $order->update(['shipment_id' => $shipment->id]);
        }

        if ($trackingNumber !== '' && ! empty($data['add_tracking_scan'])) {
            ShipmentTracking::create([
                'shipment_id' => $shipment->id,
                'status' => $data['shipment_status'] ?? 'shipped',
                'location' => $data['location'] ?? null,
                'remarks' => $data['remarks'] ?? 'Tracking ID added manually',
                'scan_time' => now(),
            ]);
        }

        return $shipment->fresh();
    }

    public function generateLabel(Shipment $shipment): string
    {
        $shipment->refresh();
        $shipment->loadMissing('order.items', 'order.customer');

        if ($shipment->shipping_label && Storage::disk('public')->exists($shipment->shipping_label)) {
            return $shipment->shipping_label;
        }

        $waybill = trim((string) ($shipment->waybill ?: $shipment->tracking_number));

        if ($waybill === '') {
            throw new \RuntimeException('Add a tracking / AWB number before generating the shipping label.');
        }

        if (! $shipment->waybill) {
            $shipment->update([
                'waybill' => $waybill,
                'tracking_number' => $shipment->tracking_number ?: $waybill,
            ]);
        }

        $html = view('admin.orders.shipping-label', [
            'shipment' => $shipment,
            'order' => $shipment->order,
            'slip' => ['wbn' => $waybill],
        ])->render();

        $path = 'labels/'.$waybill.'.html';
        Storage::disk('public')->put($path, $html);
        $shipment->update(['shipping_label' => $path]);

        return $path;
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array{success: bool, amount: float, source: string, note: ?string}
     */
    private function result(float $amount, string $source, ?string $note = null, array $extra = []): array
    {
        return array_merge([
            'success' => true,
            'amount' => round(max(0, $amount), 2),
            'source' => $source,
            'note' => $note,
        ], $extra);
    }
}
