<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>{{ $mailSubject ?? 'Order Update' }}</title></head>
<body style="font-family:Arial,sans-serif;background:#f8fafc;padding:24px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;padding:32px;border:1px solid #e2e8f0;">
    <h2 style="color:#ea580c;margin:0 0 8px;">{{ config('app.name') }}</h2>
    <p style="color:#64748b;font-size:14px;margin:0 0 24px;">Order {{ $order->order_number }}</p>
    <p style="color:#1e293b;font-size:16px;line-height:1.6;">{{ $messageText }}</p>
    <div style="margin-top:24px;padding:16px;background:#f8fafc;border-radius:8px;font-size:14px;">
        <p style="margin:4px 0;"><strong>Status:</strong> {{ $order->statusLabel() }}</p>
        <p style="margin:4px 0;"><strong>Payment:</strong> {{ $order->paymentStatusLabel() }}</p>
        <p style="margin:4px 0;"><strong>Total:</strong> ₹{{ number_format($order->displayTotal(), 2) }}</p>
    </div>
    <p style="margin-top:24px;font-size:13px;color:#94a3b8;">Thank you for shopping with {{ config('app.name') }}.</p>
</div>
</body>
</html>
