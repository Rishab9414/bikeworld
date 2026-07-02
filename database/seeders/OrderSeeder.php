<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\ShipmentTracking;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::count() > 0) {
            return;
        }

        $customerUser = User::where('email', 'customer@bikeworld.com')->first();
        $customer = Customer::where('user_id', $customerUser?->id)->first()
            ?? Customer::first();

        $products = Product::limit(6)->get();
        if ($products->isEmpty()) {
            $this->command->warn('No products found. Run product seeder first.');

            return;
        }

        $samples = [
            ['status' => 'pending', 'payment_status' => 'pending', 'payment_method' => 'cod', 'days_ago' => 0],
            ['status' => 'confirmed', 'payment_status' => 'paid', 'payment_method' => 'online', 'days_ago' => 0],
            ['status' => 'shipment_created', 'payment_status' => 'paid', 'payment_method' => 'online', 'days_ago' => 0, 'with_shipment' => true],
            ['status' => 'in_transit', 'payment_status' => 'paid', 'payment_method' => 'online', 'days_ago' => 1, 'with_shipment' => true],
            ['status' => 'out_for_delivery', 'payment_status' => 'paid', 'payment_method' => 'cod', 'days_ago' => 1, 'with_shipment' => true],
            ['status' => 'delivered', 'payment_status' => 'paid', 'payment_method' => 'online', 'days_ago' => 2, 'with_shipment' => true],
            ['status' => 'completed', 'payment_status' => 'paid', 'payment_method' => 'online', 'days_ago' => 3, 'with_shipment' => true],
            ['status' => 'cancelled', 'payment_status' => 'pending', 'payment_method' => 'cod', 'days_ago' => 4],
            ['status' => 'refunded', 'payment_status' => 'refunded', 'payment_method' => 'online', 'days_ago' => 5],
            ['status' => 'pickup_scheduled', 'payment_status' => 'paid', 'payment_method' => 'wallet', 'days_ago' => 0, 'with_shipment' => true],
        ];

        foreach ($samples as $i => $sample) {
            $product = $products[$i % $products->count()];
            $qty = rand(1, 3);
            $lineSubtotal = (float) $product->price * $qty;
            $tax = round($lineSubtotal * 0.18, 2);
            $shipping = rand(0, 1) ? 99 : 0;
            $grandTotal = $lineSubtotal + $tax + $shipping;
            $createdAt = now()->subDays($sample['days_ago'])->subHours(rand(1, 10));

            $order = Order::create([
                'user_id' => $customerUser?->id,
                'customer_id' => $customer?->id,
                'order_number' => 'BW-'.now()->format('Ymd').'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'status' => $sample['status'],
                'subtotal' => $lineSubtotal,
                'shipping_charge' => $shipping,
                'tax_amount' => $tax,
                'grand_total' => $grandTotal,
                'total' => $grandTotal,
                'payment_method' => $sample['payment_method'],
                'payment_status' => $sample['payment_status'],
                'stock_reserved' => in_array($sample['status'], ['confirmed', 'shipment_created', 'pickup_scheduled', 'in_transit', 'out_for_delivery', 'delivered', 'completed']),
                'shipping_address' => "Rahul Sharma\n42 MG Road, Andheri East\nMumbai, Maharashtra\n400069\n9876543210",
                'billing_address' => "Rahul Sharma\n42 MG Road, Andheri East\nMumbai, Maharashtra\n400069",
                'shipping_address_json' => [
                    'name' => $customer?->full_name ?? 'Rahul Sharma',
                    'line_1' => '42 MG Road, Andheri East',
                    'city' => 'Mumbai',
                    'state' => 'Maharashtra',
                    'pincode' => '400069',
                    'phone' => $customer?->mobile ?? '9876543210',
                ],
                'expected_delivery' => now()->addDays(5)->toDateString(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku ?? 'SKU-'.$product->id,
                'price' => $product->price,
                'gst' => $tax,
                'total' => $lineSubtotal + $tax,
                'quantity' => $qty,
                'subtotal' => $lineSubtotal,
                'weight' => $product->weight ?? 0.5,
                'status' => $sample['status'] === 'cancelled' ? 'cancelled' : 'reserved',
            ]);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'title' => 'Order Placed',
                'remarks' => 'Customer placed the order',
                'actor' => 'customer',
                'created_at' => $createdAt,
            ]);

            if ($sample['status'] !== 'pending') {
                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => $sample['status'],
                    'title' => ucwords(str_replace('_', ' ', $sample['status'])),
                    'actor' => 'system',
                    'created_at' => $createdAt->copy()->addHours(2),
                ]);
            }

            if (! empty($sample['with_shipment'])) {
                $waybill = 'DL'.rand(1000000000, 9999999999);
                $shipment = Shipment::create([
                    'order_id' => $order->id,
                    'courier_name' => 'Delhivery',
                    'shipment_id' => 'DLV-'.strtoupper(substr(md5((string) $order->id), 0, 10)),
                    'waybill' => $waybill,
                    'tracking_number' => $waybill,
                    'tracking_url' => "https://www.delhivery.com/track/package/{$waybill}",
                    'shipment_status' => match ($sample['status']) {
                        'shipment_created', 'pickup_scheduled' => 'created',
                        'in_transit' => 'in_transit',
                        'out_for_delivery' => 'out_for_delivery',
                        'delivered', 'completed' => 'delivered',
                        default => 'created',
                    },
                    'estimated_delivery' => now()->addDays(3)->toDateString(),
                    'shipping_cost' => $shipping,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $order->update(['shipment_id' => $shipment->id]);

                ShipmentTracking::create([
                    'shipment_id' => $shipment->id,
                    'status' => 'created',
                    'location' => 'Mumbai',
                    'remarks' => 'Shipment created',
                    'scan_time' => $createdAt->copy()->addHours(3),
                ]);
            }
        }

        $this->command->info('Created '.count($samples).' sample orders.');
    }
}
