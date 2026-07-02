<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Shipping Label {{ $shipment->waybill }}</title>
<style>body{font-family:Arial,sans-serif;width:400px;margin:20px auto;border:2px solid #000;padding:20px}h2{margin:0}.barcode{font-family:monospace;font-size:24px;font-weight:bold;letter-spacing:2px;margin:10px 0}</style>
</head><body>
<h2>Delhivery</h2>
<p><strong>AWB:</strong></p>
<div class="barcode">{{ $shipment->waybill }}</div>
<p><strong>To:</strong> {{ $order->customer?->full_name ?? 'Customer' }}</p>
<p>{{ $order->shipping_address }}</p>
<p><strong>Phone:</strong> {{ $order->customer?->mobile ?? '—' }}</p>
<p><strong>Order:</strong> {{ $order->order_number }}</p>
<p><strong>Payment:</strong> {{ strtoupper($order->payment_method) }}</p>
<p><strong>Weight:</strong> {{ $order->items->sum(fn($i)=>($i->weight??0.5)*$i->quantity) }} kg</p>
@if(config('delhivery.mock'))<p style="color:red;font-size:11px">MOCK LABEL — Set DELHIVERY_MOCK=false with API token for live labels</p>@endif
<script>window.onload=()=>window.print()</script>
</body></html>
