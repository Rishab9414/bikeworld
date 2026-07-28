<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShipmentTracking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DelhiveryService
{
    public function __construct(
        private OrderService $orderService,
        private NotificationService $notifications,
    ) {}

    public function createShipment(Order $order): Shipment
    {
        if ($order->shipment) {
            return $order->shipment;
        }

        $payload = $this->buildShipmentPayload($order);
        $response = $this->request('POST', '/api/cmu/create.json', $payload, asForm: true);
        $data = $this->parseCreateResponse($response);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'courier_name' => 'Delhivery',
            'shipment_id' => $data['shipment_id'],
            'waybill' => $data['waybill'],
            'tracking_number' => $data['waybill'],
            'tracking_url' => $data['tracking_url'],
            'shipment_status' => 'created',
            'estimated_delivery' => now()->addDays(5)->toDateString(),
            'shipping_cost' => $order->shipping_charge,
        ]);

        $order->update([
            'shipment_id' => $shipment->id,
            'status' => 'shipment_created',
            'expected_delivery' => $shipment->estimated_delivery,
        ]);

        $this->recordTracking($shipment, 'created', 'Shipment created', 'Delhivery');
        $this->orderService->logStatus($order, 'shipment_created', 'Shipment Created', "AWB: {$data['waybill']}", 'admin');
        $this->notifications->notifyOrderEvent($order->fresh(), 'shipment_created');

        return $shipment;
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
            throw new \RuntimeException(
                'Cannot print label: AWB / waybill is missing on this shipment. Create the Delhivery shipment again so an AWB is assigned.'
            );
        }

        if (! $shipment->waybill) {
            $shipment->update([
                'waybill' => $waybill,
                'tracking_number' => $shipment->tracking_number ?: $waybill,
                'tracking_url' => $shipment->tracking_url ?: "https://www.delhivery.com/track/package/{$waybill}",
            ]);
        }

        if ($this->isMock()) {
            $path = $this->storeHtmlLabel($shipment);

            return $path;
        }

        // Official API returns JSON (optionally with a PDF link). FAQ: not a raw PDF body.
        $response = $this->request('GET', '/api/p/packing_slip', [
            'wbns' => $waybill,
            'pdf' => 'true',
        ]);

        if (! $response->successful()) {
            Log::error('Delhivery packing slip HTTP error', [
                'waybill' => $waybill,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Delhivery label request failed (HTTP '.$response->status().').');
        }

        $body = $response->body();
        $contentType = strtolower((string) $response->header('Content-Type'));

        // Rare: binary PDF returned directly
        if (str_contains($contentType, 'pdf') || str_starts_with($body, '%PDF')) {
            $path = 'labels/'.$waybill.'.pdf';
            Storage::disk('public')->put($path, $body);
            $shipment->update(['shipping_label' => $path]);

            return $path;
        }

        $json = json_decode($body, true);

        if (! is_array($json)) {
            Log::error('Delhivery packing slip unexpected body', [
                'waybill' => $waybill,
                'body' => substr($body, 0, 500),
            ]);

            throw new \RuntimeException('Delhivery returned an unexpected label response.');
        }

        if ($error = ($json['error'] ?? $json['message'] ?? null)) {
            // Some success payloads still include message — only fail on clear errors.
            if (! isset($json['packages']) && ! isset($json['packages_info']) && ! $this->extractPackingSlipPdfUrl($json)) {
                throw new \RuntimeException("Delhivery label error: {$error}");
            }
        }

        if ($pdfUrl = $this->extractPackingSlipPdfUrl($json)) {
            $pdf = Http::withHeaders([
                'Authorization' => 'Token '.config('delhivery.api_token'),
            ])->timeout(60)->get($pdfUrl);

            if ($pdf->successful() && (str_starts_with($pdf->body(), '%PDF') || str_contains(strtolower((string) $pdf->header('Content-Type')), 'pdf'))) {
                $path = 'labels/'.$waybill.'.pdf';
                Storage::disk('public')->put($path, $pdf->body());
                $shipment->update(['shipping_label' => $path]);

                return $path;
            }
        }

        $slip = $this->extractPackingSlipPackage($json, $waybill);
        $path = $this->storeHtmlLabel($shipment, $slip);

        return $path;
    }

    private function extractPackingSlipPdfUrl(array $json): ?string
    {
        $candidates = [
            $json['pdf_download'] ?? null,
            $json['pdf_download_link'] ?? null,
            $json['pdf_url'] ?? null,
            $json['packages_info'][0]['pdf_download'] ?? null,
            $json['packages'][0]['pdf_download'] ?? null,
            $json['packages'][0]['pdf_url'] ?? null,
        ];

        foreach ($candidates as $url) {
            if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            }
        }

        return null;
    }

    private function extractPackingSlipPackage(array $json, string $waybill): array
    {
        $packages = $json['packages_info']
            ?? $json['packages']
            ?? $json['package']
            ?? [];

        if (isset($packages[0]) && is_array($packages[0])) {
            $pkg = $packages[0];
            $pkg['wbn'] = $pkg['wbn'] ?? $pkg['waybill'] ?? $waybill;

            return $pkg;
        }

        return ['wbn' => $waybill];
    }

    private function storeHtmlLabel(Shipment $shipment, array $slip = []): string
    {
        $shipment->loadMissing('order.items', 'order.customer');
        $waybill = $shipment->waybill ?: ($slip['wbn'] ?? 'label');

        $html = view('admin.orders.shipping-label', [
            'shipment' => $shipment,
            'order' => $shipment->order,
            'slip' => $slip,
        ])->render();

        $path = 'labels/'.$waybill.'.html';
        Storage::disk('public')->put($path, $html);
        $shipment->update(['shipping_label' => $path]);

        return $path;
    }

    public function schedulePickup(Shipment $shipment): Shipment
    {
        $payload = [
            'pickup_time' => now()->addDay()->format('H:i:s'),
            'pickup_date' => now()->addDay()->format('Y-m-d'),
            'pickup_location' => config('delhivery.pickup_location'),
            'expected_package_count' => 1,
        ];

        $response = $this->request('POST', '/fm/request/new/', $payload);
        $pickupId = $response->json('pickup_id') ?? $response->json('data.pickup_id') ?? 'PU-'.Str::random(8);

        $shipment->update([
            'pickup_request_id' => $pickupId,
            'pickup_date' => now()->addDay()->toDateString(),
            'shipment_status' => 'pickup_scheduled',
        ]);

        $order = $shipment->order;
        $order->update(['status' => 'pickup_scheduled']);
        $this->orderService->logStatus($order, 'pickup_scheduled', 'Pickup Scheduled', "Pickup ID: {$pickupId}", 'admin');
        $this->notifications->notifyOrderEvent($order->fresh(), 'pickup_scheduled', "Pickup has been scheduled for your order. Pickup ID: {$pickupId}");

        return $shipment->fresh();
    }

    public function cancelShipment(Shipment $shipment): Shipment
    {
        if (! $this->isMock()) {
            $this->request('POST', '/api/p/edit', [
                'waybill' => $shipment->waybill,
                'cancellation' => true,
            ], asForm: true);
        }

        $shipment->update(['shipment_status' => 'cancelled']);
        $this->recordTracking($shipment, 'cancelled', 'Shipment cancelled');

        return $shipment->fresh();
    }

    public function trackShipment(Shipment $shipment): array
    {
        if ($this->isMock()) {
            return ['status' => $shipment->shipment_status, 'scans' => []];
        }

        $response = $this->request('GET', '/api/v1/packages/json/', [
            'waybill' => $shipment->waybill,
            'verbose' => 2,
        ]);

        return $response->json() ?? [];
    }

    public function syncTracking(Shipment $shipment): void
    {
        $data = $this->trackShipment($shipment);
        $status = strtolower($data['ShipmentData'][0]['Shipment']['Status']['Status'] ?? $shipment->shipment_status);
        $this->applyShipmentStatus($shipment, $status, 'Tracking sync');
    }

    public function calculateShippingCost(string $destinationPin, int $weightGrams, string $paymentMode = 'Pre-paid'): array
    {
        $destinationPin = preg_replace('/\D/', '', $destinationPin);
        $weightGrams = max(100, $weightGrams);

        if (strlen($destinationPin) !== 6) {
            return ['success' => false, 'message' => 'Invalid destination pincode.'];
        }

        if ($this->isMock()) {
            return $this->mockShippingCost($destinationPin, $weightGrams);
        }

        $params = [
            'md' => config('delhivery.shipping_mode', 'E'),
            'cgm' => $weightGrams,
            'o_pin' => (int) config('delhivery.pickup_pin', '400001'),
            'd_pin' => (int) $destinationPin,
            'ss' => 'Delivered',
            'pt' => strtoupper($paymentMode) === 'COD' ? 'COD' : 'Pre-paid',
        ];

        if ($client = config('delhivery.client_name')) {
            $params['cl'] = $client;
        }

        try {
            $response = $this->request('GET', '/api/kinko/v1/invoice/charges/.json', $params);

            return $this->parseShippingCostResponse($response);
        } catch (\Throwable $e) {
            Log::warning('Delhivery shipping cost exception', ['message' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Could not fetch shipping cost from Delhivery.'];
        }
    }

    public function checkPincode(string $pincode): array
    {
        $pincode = preg_replace('/\D/', '', $pincode);

        if (strlen($pincode) !== 6) {
            return [
                'success' => false,
                'serviceable' => false,
                'pincode' => $pincode,
                'message' => 'Please enter a valid 6-digit pincode.',
            ];
        }

        if ($this->isMock()) {
            return $this->mockPincodeCheck($pincode);
        }

        $response = $this->request('GET', '/c/api/pin-codes/json/', ['filter_codes' => $pincode]);
        $codes = $response->json('delivery_codes') ?? [];

        if (empty($codes)) {
            return [
                'success' => true,
                'serviceable' => false,
                'pincode' => $pincode,
                'message' => 'Sorry, we do not deliver to this pincode yet.',
            ];
        }

        $first = $codes[0];
        $eta = $this->fetchExpectedTat($pincode);

        return [
            'success' => true,
            'serviceable' => true,
            'pincode' => $pincode,
            'city' => $first['city'] ?? null,
            'state' => $first['state_code'] ?? $first['state'] ?? null,
            'district' => $first['district'] ?? null,
            'cod_available' => filter_var($first['cod'] ?? 'Y', FILTER_VALIDATE_BOOLEAN) || ($first['cod'] ?? 'Y') === 'Y',
            'prepaid_available' => true,
            'estimated_delivery_days' => $eta['days'],
            'estimated_delivery_date' => $eta['date'],
            'message' => $eta['message'],
        ];
    }

    public function handleWebhook(array $payload): void
    {
        $waybill = $payload['waybill'] ?? $payload['AWB'] ?? null;
        $status = strtolower($payload['status'] ?? $payload['ShipmentStatus'] ?? '');

        if (! $waybill) {
            return;
        }

        $shipment = Shipment::where('waybill', $waybill)->first();
        if (! $shipment) {
            Log::warning('Delhivery webhook: unknown waybill', $payload);

            return;
        }

        $location = $payload['location'] ?? $payload['city'] ?? null;
        $remarks = $payload['remarks'] ?? $payload['instructions'] ?? null;
        $scanTime = $payload['scan_time'] ?? $payload['timestamp'] ?? now();

        $this->recordTracking($shipment, $status, $remarks, $location, $scanTime);
        $this->applyShipmentStatus($shipment, $status, $remarks);
    }

    public function applyShipmentStatus(Shipment $shipment, string $rawStatus, ?string $remarks = null): void
    {
        $map = [
            'picked' => 'picked_up',
            'picked_up' => 'picked_up',
            'in transit' => 'in_transit',
            'in_transit' => 'in_transit',
            'dispatched' => 'in_transit',
            'reached destination' => 'reached_destination_hub',
            'reached_destination_hub' => 'reached_destination_hub',
            'out for delivery' => 'out_for_delivery',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
            'created' => 'shipment_created',
        ];

        $normalized = $map[$rawStatus] ?? str_replace(' ', '_', $rawStatus);
        $shipment->update(['shipment_status' => $normalized]);

        $order = $shipment->order;
        $orderStatus = in_array($normalized, Order::STATUSES) ? $normalized : $order->status;

        if ($normalized === 'delivered') {
            $orderStatus = 'delivered';
        }

        $order->update(['status' => $orderStatus]);
        $this->orderService->logStatus($order, $orderStatus, ucwords(str_replace('_', ' ', $normalized)), $remarks, 'delhivery');

        $eventMap = [
            'picked_up' => 'pickup_completed',
            'in_transit' => 'shipped',
            'out_for_delivery' => 'out_for_delivery',
            'delivered' => 'delivered',
        ];

        if (isset($eventMap[$normalized])) {
            $this->notifications->notifyOrderEvent($order, $eventMap[$normalized]);
        }

        if ($normalized === 'delivered') {
            $order->update(['status' => 'completed']);
            $this->orderService->logStatus($order, 'completed', 'Order Completed', null, 'system');
        }
    }

    private function recordTracking(Shipment $shipment, string $status, ?string $remarks = null, ?string $location = null, $scanTime = null): void
    {
        ShipmentTracking::create([
            'shipment_id' => $shipment->id,
            'status' => $status,
            'location' => $location,
            'remarks' => $remarks,
            'scan_time' => $scanTime ? \Carbon\Carbon::parse($scanTime) : now(),
        ]);
    }

    private function buildShipmentPayload(Order $order): array
    {
        $addr = $order->shipping_address_json ?? [];
        $weight = $order->items->sum(fn ($i) => ($i->weight ?? 0.5) * $i->quantity) ?: config('delhivery.default_weight_kg');
        $paymentMode = $order->payment_method === 'cod' ? 'COD' : 'Prepaid';

        return [
            'format' => 'json',
            'data' => json_encode([
                'shipments' => [[
                    'name' => $addr['name'] ?? $order->customer?->full_name ?? 'Customer',
                    'add' => $addr['line_1'] ?? $order->shipping_address,
                    'pin' => $addr['pincode'] ?? config('delhivery.pickup_pin'),
                    'city' => $addr['city'] ?? config('delhivery.pickup_city'),
                    'state' => $addr['state'] ?? config('delhivery.pickup_state'),
                    'country' => 'India',
                    'phone' => $addr['phone'] ?? $order->customer?->mobile ?? config('delhivery.pickup_phone'),
                    'order' => $order->order_number,
                    'payment_mode' => $paymentMode,
                    'cod_amount' => $paymentMode === 'COD' ? $order->displayTotal() : 0,
                    'total_amount' => $order->displayTotal(),
                    'quantity' => $order->items->sum('quantity'),
                    'weight' => max(0.1, $weight),
                ]],
                'pickup_location' => ['name' => config('delhivery.pickup_location')],
            ]),
        ];
    }

    private function parseCreateResponse($response): array
    {
        if ($this->isMock()) {
            $waybill = 'DL'.rand(1000000000, 9999999999);

            return [
                'shipment_id' => 'DLV-'.Str::upper(Str::random(10)),
                'waybill' => $waybill,
                'tracking_url' => "https://www.delhivery.com/track/package/{$waybill}",
            ];
        }

        $json = $response->json() ?? [];

        // Common Delhivery CMU shapes
        $pkg = $json['packages'][0]
            ?? $json['package'][0]
            ?? $json['shipment_data']['packages'][0]
            ?? $json['data']['packages'][0]
            ?? [];

        $waybill = $pkg['waybill']
            ?? $pkg['wbn']
            ?? $json['waybill']
            ?? $json['wbn']
            ?? null;

        $status = strtolower((string) ($pkg['status'] ?? $json['status'] ?? ''));
        $remark = $pkg['remarks'] ?? $pkg['remark'] ?? $json['rmk'] ?? $json['message'] ?? null;

        if (! $waybill) {
            Log::error('Delhivery create shipment: waybill missing', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException(
                $remark
                    ? "Delhivery did not assign an AWB: {$remark}"
                    : 'Delhivery did not return a waybill/AWB. Check pickup warehouse name, token, and API response.'
            );
        }

        if (in_array($status, ['fail', 'failed', 'error'], true)) {
            throw new \RuntimeException($remark ?: 'Delhivery shipment creation failed.');
        }

        return [
            'shipment_id' => $pkg['refnum'] ?? $pkg['ref_num'] ?? $json['shipment_id'] ?? (string) $waybill,
            'waybill' => (string) $waybill,
            'tracking_url' => "https://www.delhivery.com/track/package/{$waybill}",
        ];
    }

    private function request(string $method, string $path, array $data = [], bool $asForm = false)
    {
        if ($this->isMock()) {
            return new \Illuminate\Http\Client\Response(
                new \GuzzleHttp\Psr7\Response(200, [], json_encode(['success' => true, 'mock' => true]))
            );
        }

        $url = rtrim(config('delhivery.base_url'), '/').$path;
        $http = Http::withHeaders([
            'Authorization' => 'Token '.config('delhivery.api_token'),
            'Accept' => 'application/json',
        ])->timeout(30);

        // GET must send params as query string (Delhivery packing_slip needs ?wbns=...)
        if (strtoupper($method) === 'GET') {
            return $http->get($url, $data);
        }

        return $asForm
            ? $http->asForm()->{$method}($url, $data)
            : $http->{$method}($url, $data);
    }

    private function isMock(): bool
    {
        return (bool) config('delhivery.mock', true) || empty(config('delhivery.api_token'));
    }

    private function mockPincodeCheck(string $pincode): array
    {
        if (str_starts_with($pincode, '000')) {
            return [
                'success' => true,
                'serviceable' => false,
                'pincode' => $pincode,
                'message' => 'Delivery is not available for this pincode.',
                'mock' => true,
            ];
        }

        $origin = (string) config('delhivery.pickup_pin', '400069');
        $distance = abs((int) substr($pincode, 0, 3) - (int) substr($origin, 0, 3));
        $days = min(7, max(2, 2 + (int) floor($distance / 50)));
        $maxDays = $days + 1;

        return [
            'success' => true,
            'serviceable' => true,
            'pincode' => $pincode,
            'city' => 'Serviceable Area',
            'state' => config('delhivery.pickup_state', 'Maharashtra'),
            'cod_available' => true,
            'prepaid_available' => true,
            'estimated_delivery_days' => $days,
            'estimated_delivery_date' => now()->addDays($days)->format('d M Y'),
            'message' => "Estimated delivery in {$days}–{$maxDays} business days",
            'mock' => true,
        ];
    }

    private function fetchExpectedTat(string $destinationPin): array
    {
        $origin = (string) config('delhivery.pickup_pin', '400069');

        try {
            $response = $this->request('GET', '/api/dc/expected_tat', [
                'origin_pin' => $origin,
                'destination_pin' => $destinationPin,
                'mot' => 'S',
                'pdt' => 'Prepaid',
            ]);

            $days = (int) ($response->json('data.tat')
                ?? $response->json('tat')
                ?? $response->json('expected_tat')
                ?? 5);
        } catch (\Throwable) {
            $days = 5;
        }

        $days = max(2, min(10, $days));

        return [
            'days' => $days,
            'date' => now()->addDays($days)->format('d M Y'),
            'message' => "Estimated delivery in {$days}–".($days + 1).' business days',
        ];
    }

    private function parseShippingCostResponse($response): array
    {
        if (! $response->successful()) {
            Log::warning('Delhivery shipping cost failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['success' => false, 'message' => 'Delhivery shipping API returned an error.'];
        }

        $body = trim($response->body());
        $json = json_decode($body, true);

        if (is_array($json)) {
            $row = $json[0] ?? $json['data'][0] ?? $json;
            $amount = $row['total_amount'] ?? $row['total'] ?? $row['gross_amount'] ?? null;

            if ($amount !== null && is_numeric($amount)) {
                return [
                    'success' => true,
                    'amount' => (float) $amount,
                    'raw' => $row,
                ];
            }
        }

        if (str_starts_with($body, '<')) {
            try {
                $xml = simplexml_load_string($body);
                $amount = (float) ($xml->charge->total_amount ?? $xml->total_amount ?? 0);

                if ($amount > 0) {
                    return ['success' => true, 'amount' => $amount];
                }
            } catch (\Throwable $e) {
                Log::warning('Delhivery shipping cost XML parse failed', ['message' => $e->getMessage()]);
            }
        }

        return ['success' => false, 'message' => 'Could not parse Delhivery shipping quote.'];
    }

    private function mockShippingCost(string $destinationPin, int $weightGrams): array
    {
        $origin = (string) config('delhivery.pickup_pin', '400001');
        $distance = abs((int) substr($destinationPin, 0, 3) - (int) substr($origin, 0, 3));
        $weightFactor = max(0, ($weightGrams - 500) / 500) * 12;
        $distanceFactor = min(60, $distance / 8);
        $amount = round(59 + $weightFactor + $distanceFactor, 2);

        return [
            'success' => true,
            'amount' => max(59, $amount),
            'mock' => true,
        ];
    }
}
