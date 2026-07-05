@extends('shop.pages._layout')

@section('policy-content')
<p>We want you to shop with confidence. This Return & Refund Policy explains when and how you can return products purchased from {{ config('app.name') }}.</p>

<h2>1. Return Eligibility</h2>
<p>You may request a return within <strong>7 days</strong> of delivery if:</p>
<ul>
    <li>The product is unused, unworn, and in original condition</li>
    <li>Original packaging, tags, labels, and accessories are intact</li>
    <li>You have proof of purchase (order number / invoice)</li>
</ul>

<h2>2. Non-Returnable Items</h2>
<p>The following are generally <strong>not eligible</strong> for return:</p>
<ul>
    <li>Helmets that have been worn or had the safety seal removed (for safety & hygiene reasons)</li>
    <li>Inner wear, gloves, or personal hygiene products once opened</li>
    <li>Customised, clearance, or final-sale items (if marked non-returnable)</li>
    <li>Products damaged due to misuse, accident, or normal wear and tear</li>
</ul>

<h2>3. How to Initiate a Return</h2>
<ol>
    <li>Email <a href="mailto:{{ config('store.support_email') }}">{{ config('store.support_email') }}</a> or contact us via your <a href="{{ route('orders.index') }}">order details</a> page within 7 days of delivery.</li>
    <li>Provide your order number, product name, reason for return, and photos (if defective/damaged).</li>
    <li>Our team will approve or reject the request within 2 business days.</li>
    <li>If approved, we will arrange reverse pickup or provide return instructions.</li>
</ol>

<h2>4. Defective or Wrong Items</h2>
<p>If you receive a defective, damaged, or wrong product, contact us within <strong>48 hours</strong> with unboxing photos/video. We will arrange a free replacement or full refund including shipping.</p>

<h2>5. Refund Process</h2>
<ul>
    <li>After we receive and inspect the returned item, refunds are processed within <strong>5–7 business days</strong>.</li>
    <li><strong>Online payments:</strong> Refund is credited to the original payment method via Razorpay.</li>
    <li><strong>COD orders:</strong> Refund is issued via bank transfer (UPI/NEFT) — bank details will be requested.</li>
    <li>Shipping charges are non-refundable unless the return is due to our error (wrong/defective item).</li>
</ul>

<h2>6. Exchanges</h2>
<p>Exchanges are subject to stock availability. If the requested size/variant is unavailable, we will issue a refund instead.</p>

<h2>7. Return Shipping</h2>
<p>For approved returns due to change of mind, return shipping may be deducted from the refund or borne by the customer. For defective/wrong items, return shipping is free.</p>

<h2>8. Cancellations Before Delivery</h2>
<p>See our <a href="{{ route('pages.show', 'cancellation-policy') }}">Cancellation Policy</a> for orders cancelled before shipment.</p>

<h2>9. Contact</h2>
<p>Returns & refunds: <a href="mailto:{{ config('store.support_email') }}">{{ config('store.support_email') }}</a> | +91 {{ config('store.support_phone') }}</p>
@endsection
