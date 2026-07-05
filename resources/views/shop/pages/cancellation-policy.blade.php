@extends('shop.pages._layout')

@section('policy-content')
<p>This Cancellation Policy explains how you can cancel an order placed on {{ config('app.name') }} and what happens to your payment.</p>

<h2>1. Cancellation Before Shipment</h2>
<p>You may cancel your order <strong>before it is shipped</strong> by contacting us at <a href="mailto:{{ config('store.support_email') }}">{{ config('store.support_email') }}</a> with your order number, or from your account under <a href="{{ route('orders.index') }}">My Orders</a> (if cancellation option is available).</p>

<h2>2. Online Payment Orders</h2>
<ul>
    <li>If cancelled before dispatch, a <strong>full refund</strong> will be initiated to your original payment method within 5–7 business days.</li>
    <li>If payment was completed but order is not yet processed, contact us immediately for fastest cancellation.</li>
</ul>

<h2>3. Cash on Delivery (COD) Orders</h2>
<p>COD orders can be cancelled before dispatch at no charge. Simply refuse delivery if the order has already shipped and contact us to confirm cancellation.</p>

<h2>4. Orders That Cannot Be Cancelled</h2>
<p>Orders cannot be cancelled once they have been:</p>
<ul>
    <li>Handed over to the courier for delivery</li>
    <li>Marked as "Shipped" or "Out for Delivery"</li>
    <li>Customised or made-to-order (if applicable)</li>
</ul>
<p>In such cases, you may refuse delivery or follow our <a href="{{ route('pages.show', 'return-refund-policy') }}">Return & Refund Policy</a> after delivery.</p>

<h2>5. Cancellation by {{ config('app.name') }}</h2>
<p>We reserve the right to cancel orders due to:</p>
<ul>
    <li>Product unavailability or pricing errors</li>
    <li>Non-serviceable delivery pincode</li>
    <li>Failed payment verification or suspected fraud</li>
    <li>Bulk or reseller orders without prior approval</li>
</ul>
<p>If we cancel your order, you will receive a full refund for any amount paid.</p>

<h2>6. Partial Cancellation</h2>
<p>If your order contains multiple items and one item is unavailable, we may ship available items and refund the unavailable item, or cancel the entire order based on your preference.</p>

<h2>7. Refund Timeline</h2>
<p>Approved cancellation refunds are processed within <strong>5–7 business days</strong>. Bank processing times may vary depending on your payment provider.</p>

<h2>8. Contact</h2>
<p>Cancellation requests: <a href="mailto:{{ config('store.support_email') }}">{{ config('store.support_email') }}</a> | +91 {{ config('store.support_phone') }}</p>
@endsection
