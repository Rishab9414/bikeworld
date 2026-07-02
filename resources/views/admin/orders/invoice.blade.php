<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Invoice {{ $order->invoiceRecord?->invoice_no ?? $order->order_number }}</title>
<style>body{font-family:Arial,sans-serif;max-width:800px;margin:40px auto;padding:20px;color:#1e293b}table{width:100%;border-collapse:collapse;margin:20px 0}th,td{border:1px solid #e2e8f0;padding:8px;text-align:left}th{background:#f8fafc}.header{display:flex;justify-content:space-between;margin-bottom:30px}.total{text-align:right;font-size:18px;font-weight:bold}</style>
</head><body>
<div class="header">
    <div><h1>{{ config('app.name') }}</h1><p>Tax Invoice</p></div>
    <div style="text-align:right"><p><strong>Invoice:</strong> {{ $order->invoiceRecord?->invoice_no ?? 'DRAFT' }}</p><p><strong>Order:</strong> {{ $order->order_number }}</p><p><strong>Date:</strong> {{ ($order->invoiceRecord?->invoice_date ?? now())->format('d M Y') }}</p></div>
</div>
<p><strong>Bill To:</strong><br>{{ $order->customer?->full_name ?? $order->user?->name }}<br>{{ $order->billing_address }}</p>
<table><thead><tr><th>Product</th><th>SKU</th><th>Qty</th><th>Price</th><th>GST</th><th>Total</th></tr></thead>
<tbody>@foreach($order->items as $item)<tr><td>{{ $item->product_name }}</td><td>{{ $item->sku }}</td><td>{{ $item->quantity }}</td><td>₹{{ number_format($item->price,2) }}</td><td>₹{{ number_format($item->gst,2) }}</td><td>₹{{ number_format($item->lineTotal(),2) }}</td></tr>@endforeach</tbody></table>
<div class="total">
    <p>Subtotal: ₹{{ number_format($order->subtotal,2) }}</p>
    <p>Shipping: ₹{{ number_format($order->shipping_charge,2) }}</p>
    <p>Tax: ₹{{ number_format($order->tax_amount,2) }}</p>
    <p>Grand Total: ₹{{ number_format($order->displayTotal(),2) }}</p>
</div>
<script>window.onload=()=>window.print()</script>
</body></html>
