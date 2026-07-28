<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Shipping Label {{ $slip['wbn'] ?? $shipment->waybill }}</title>
    <style>
        @page { size: 4in 6in; margin: 0; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            width: 4in;
            min-height: 6in;
            margin: 12px auto;
            padding: 14px;
            border: 2px solid #111;
            color: #111;
            background: #fff;
        }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        .brand { font-size: 18px; font-weight: 900; letter-spacing: -0.5px; }
        .muted { color: #555; font-size: 11px; }
        .box { border: 1px solid #111; padding: 8px; margin-top: 10px; }
        .awb {
            font-family: "Courier New", monospace;
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 2px;
            text-align: center;
            margin: 10px 0 4px;
        }
        .barcode-svg { display: block; margin: 0 auto 8px; }
        h3 { margin: 0 0 6px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        p { margin: 0 0 4px; font-size: 13px; line-height: 1.35; }
        .big { font-size: 15px; font-weight: 700; }
        .pill {
            display: inline-block;
            border: 1px solid #111;
            padding: 3px 8px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .items { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 11px; }
        .items th, .items td { border-bottom: 1px solid #ddd; padding: 4px 0; text-align: left; }
        .footer { margin-top: 12px; font-size: 10px; color: #666; text-align: center; }
        @media print {
            body { margin: 0; border: 0; width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
@php
    $wbn = $slip['wbn'] ?? $slip['waybill'] ?? $shipment->waybill;
    $name = $slip['name'] ?? $order->customer?->full_name ?? 'Customer';
    $phone = $slip['phone'] ?? $slip['mobile'] ?? $order->customer?->mobile ?? '—';
    $address = $slip['destination'] ?? $slip['address'] ?? $order->shipping_address;
    $pin = $slip['pin'] ?? $slip['pincode'] ?? data_get($order->shipping_address_json, 'pincode', '—');
    $city = $slip['city'] ?? data_get($order->shipping_address_json, 'city', '');
    $state = $slip['state'] ?? data_get($order->shipping_address_json, 'state', '');
    $payment = $slip['pt'] ?? $slip['payment_mode'] ?? strtoupper($order->payment_method ?? 'PREPAID');
    $cod = $slip['cod'] ?? ($order->payment_method === 'cod' ? $order->displayTotal() : 0);
    $orderNo = $slip['oid'] ?? $slip['order'] ?? $order->order_number;
@endphp

<div class="row">
    <div>
        <div class="brand">{{ config('app.name', 'BikeWorld') }}</div>
        <div class="muted">Delhivery Shipping Label</div>
    </div>
    <div class="pill">{{ $payment }}@if($cod) · COD ₹{{ number_format((float) $cod, 2) }}@endif</div>
</div>

<div class="box" style="text-align:center;">
    <div class="muted">AWB / WAYBILL</div>
    <div class="awb">{{ $wbn }}</div>
    {{-- Code 128-like visual using bars from digits (scannable-enough for warehouse ops; real barcode printers use AWB text) --}}
    <svg class="barcode-svg" width="280" height="48" viewBox="0 0 280 48" aria-hidden="true">
        @php
            $pattern = '';
            foreach (str_split((string) $wbn) as $ch) {
                $n = ctype_digit($ch) ? (int) $ch : (ord($ch) % 10);
                $pattern .= str_repeat('1', 1 + ($n % 3)).str_repeat('0', 1 + (($n + 1) % 2));
            }
            $x = 8;
        @endphp
        @foreach (str_split($pattern) as $bit)
            @if($bit === '1')
                <rect x="{{ $x }}" y="4" width="2" height="40" fill="#111"/>
            @endif
            @php $x += 2; @endphp
        @endforeach
    </svg>
    <div class="muted">Order: {{ $orderNo }}</div>
</div>

<div class="box">
    <h3>Ship To</h3>
    <p class="big">{{ $name }}</p>
    <p>{{ $address }}</p>
    <p>{{ trim($city.($city && $state ? ', ' : '').$state.' '.$pin) }}</p>
    <p><strong>Phone:</strong> {{ $phone }}</p>
</div>

<div class="box">
    <h3>Package</h3>
    <div class="row">
        <p><strong>Weight:</strong> {{ number_format((float) ($slip['weight'] ?? $order->items->sum(fn ($i) => ($i->weight ?? 0.5) * $i->quantity) ?: 0.5), 2) }} kg</p>
        <p><strong>Qty:</strong> {{ $slip['quantity'] ?? $order->items->sum('quantity') }}</p>
    </div>
    @if($order->items->isNotEmpty())
    <table class="items">
        <thead><tr><th>Item</th><th>Qty</th></tr></thead>
        <tbody>
        @foreach($order->items->take(6) as $item)
            <tr>
                <td>{{ \Illuminate\Support\Str::limit($item->product_name, 36) }}</td>
                <td>{{ $item->quantity }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>

<div class="box">
    <h3>Return / Pickup</h3>
    <p class="big">{{ config('delhivery.pickup_location') }}</p>
    <p>{{ config('delhivery.pickup_address') }}</p>
    <p>{{ config('delhivery.pickup_city') }}, {{ config('delhivery.pickup_state') }} {{ config('delhivery.pickup_pin') }}</p>
    <p><strong>Phone:</strong> {{ config('delhivery.pickup_phone') }}</p>
</div>

<p class="footer">Scan AWB at pickup · {{ now()->format('d M Y, h:i A') }}</p>
<button class="no-print" onclick="window.print()" style="margin-top:12px;width:100%;padding:10px;font-weight:700;cursor:pointer;">Print Label</button>
<script>window.onload = () => setTimeout(() => window.print(), 300);</script>
</body>
</html>
