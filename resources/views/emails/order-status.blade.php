@component('emails.layout')
@php
    $waybill = $order->shipmentRecord?->waybill ?? $order->shipment?->waybill;
    $trackingUrl = $order->shipmentRecord?->tracking_url ?? $order->shipment?->tracking_url;
    $customerName = $order->customer?->full_name ?? $order->user?->name ?? 'Customer';
@endphp

{{-- Status hero --}}
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td style="padding:32px 32px 24px;text-align:center;border-bottom:1px solid #F4F4F5;">
            <span style="display:inline-block;padding:6px 14px;border-radius:999px;background-color:{{ $badgeBg }};color:{{ $badgeColor }};font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;">
                {{ $badge }}
            </span>
            <h1 style="margin:16px 0 8px;font-size:26px;font-weight:900;color:#0A0A0A;line-height:1.2;letter-spacing:-0.5px;">
                {{ $headline }}
            </h1>
            <p style="margin:0;font-size:14px;color:#71717A;">
                Order <strong style="color:#0A0A0A;">{{ $order->order_number }}</strong>
                &nbsp;·&nbsp; {{ $order->created_at?->format('d M Y') }}
            </p>
        </td>
    </tr>

    {{-- Message --}}
    <tr>
        <td style="padding:24px 32px;">
            <p style="margin:0;font-size:15px;color:#3F3F46;line-height:1.7;">
                {{ $messageText }}
            </p>
        </td>
    </tr>

    {{-- Order summary box --}}
    <tr>
        <td style="padding:0 32px 24px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#FAFAFA;border:1px solid #F4F4F5;border-radius:12px;overflow:hidden;">
                <tr>
                    <td style="padding:16px 20px;border-bottom:1px solid #F4F4F5;">
                        <span style="font-size:13px;font-weight:700;color:#0A0A0A;text-transform:uppercase;letter-spacing:0.5px;">Order Summary</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 20px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:6px 0;font-size:14px;color:#71717A;">Customer</td>
                                <td align="right" style="padding:6px 0;font-size:14px;color:#0A0A0A;font-weight:600;">{{ $customerName }}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px 0;font-size:14px;color:#71717A;">Status</td>
                                <td align="right" style="padding:6px 0;font-size:14px;color:#0A0A0A;font-weight:600;">{{ $order->statusLabel() }}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px 0;font-size:14px;color:#71717A;">Payment</td>
                                <td align="right" style="padding:6px 0;font-size:14px;color:#0A0A0A;font-weight:600;">{{ $order->paymentStatusLabel() }} · {{ strtoupper($order->payment_method ?? 'COD') }}</td>
                            </tr>
                            @if($waybill)
                            <tr>
                                <td style="padding:6px 0;font-size:14px;color:#71717A;">AWB / Tracking</td>
                                <td align="right" style="padding:6px 0;font-size:14px;color:#0A0A0A;font-weight:600;">{{ $waybill }}</td>
                            </tr>
                            @endif
                            @if($order->coupon_code)
                            <tr>
                                <td style="padding:6px 0;font-size:14px;color:#71717A;">Coupon</td>
                                <td align="right" style="padding:6px 0;font-size:14px;color:#059669;font-weight:600;">{{ $order->coupon_code }} (−₹{{ number_format($order->coupon_discount, 2) }})</td>
                            </tr>
                            @endif
                            <tr>
                                <td style="padding:6px 0;font-size:14px;color:#71717A;">Subtotal</td>
                                <td align="right" style="padding:6px 0;font-size:14px;color:#0A0A0A;">₹{{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            @if($order->shipping_charge > 0)
                            <tr>
                                <td style="padding:6px 0;font-size:14px;color:#71717A;">Shipping</td>
                                <td align="right" style="padding:6px 0;font-size:14px;color:#0A0A0A;">₹{{ number_format($order->shipping_charge, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="2" style="padding:12px 0 4px;border-top:1px solid #E4E4E7;"></td>
                            </tr>
                            <tr>
                                <td style="padding:4px 0;font-size:16px;color:#0A0A0A;font-weight:700;">Total</td>
                                <td align="right" style="padding:4px 0;font-size:18px;color:#E31E24;font-weight:900;">₹{{ number_format($order->displayTotal(), 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Items --}}
    @if($order->items->isNotEmpty())
    <tr>
        <td style="padding:0 32px 24px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #F4F4F5;border-radius:12px;overflow:hidden;">
                <tr>
                    <td style="padding:14px 20px;background-color:#FAFAFA;border-bottom:1px solid #F4F4F5;">
                        <span style="font-size:13px;font-weight:700;color:#0A0A0A;text-transform:uppercase;letter-spacing:0.5px;">Items ({{ $order->items->count() }})</span>
                    </td>
                </tr>
                @foreach($order->items as $item)
                <tr>
                    <td style="padding:14px 20px;border-bottom:1px solid #F4F4F5;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="font-size:14px;color:#0A0A0A;font-weight:600;">{{ $item->product_name }}</td>
                                <td align="right" style="font-size:14px;color:#0A0A0A;font-weight:700;white-space:nowrap;">₹{{ number_format($item->lineTotal(), 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="padding-top:4px;font-size:12px;color:#A1A1AA;">
                                    Qty: {{ $item->quantity }} · SKU: {{ $item->sku }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endforeach
            </table>
        </td>
    </tr>
    @endif

    {{-- Shipping address --}}
    @if($order->shipping_address)
    <tr>
        <td style="padding:0 32px 24px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#FAFAFA;border:1px solid #F4F4F5;border-radius:12px;">
                <tr>
                    <td style="padding:16px 20px;">
                        <p style="margin:0 0 6px;font-size:12px;font-weight:700;color:#71717A;text-transform:uppercase;letter-spacing:0.5px;">Delivery Address</p>
                        <p style="margin:0;font-size:14px;color:#3F3F46;line-height:1.6;white-space:pre-line;">{{ $order->shipping_address }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    @endif

    {{-- CTA buttons --}}
    <tr>
        <td style="padding:8px 32px 36px;text-align:center;">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                <tr>
                    <td style="border-radius:10px;background-color:#E31E24;">
                        <a href="{{ $orderUrl }}" target="_blank" style="display:inline-block;padding:14px 32px;font-size:14px;font-weight:700;color:#FFFFFF;text-decoration:none;border-radius:10px;">
                            View Order Details
                        </a>
                    </td>
                    @if($trackingUrl)
                    <td style="padding-left:12px;">
                        <a href="{{ $trackingUrl }}" target="_blank" style="display:inline-block;padding:13px 24px;font-size:14px;font-weight:700;color:#0A0A0A;text-decoration:none;border-radius:10px;border:2px solid #E4E4E7;background-color:#FFFFFF;">
                            Track Shipment
                        </a>
                    </td>
                    @endif
                </tr>
            </table>
        </td>
    </tr>
</table>
@endcomponent
