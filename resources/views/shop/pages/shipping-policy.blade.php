@extends('shop.pages._layout')

@section('policy-content')
<p>This Shipping Policy explains how {{ config('app.name') }} delivers orders across India, how shipping charges are calculated, and what you can expect after placing an order.</p>

<h2>1. Delivery Coverage</h2>
<p>We deliver across India. Enter your complete delivery address and pincode at checkout.</p>

<h2>2. Shipping Charges</h2>
<ul>
    <li>Shipping cost is calculated at <strong>checkout</strong> based on each product's shipping charge in your cart.</li>
    <li>If a product does not have a shipping charge set, a default of <strong>₹{{ number_format(config('shipping.default_product_shipping', 99), 0) }}</strong> per unit applies.</li>
    <li>Individual products marked as <strong>free shipping</strong> ship at no extra cost.</li>
    <li><strong>Free shipping on minimum order:</strong> When enabled on our store, orders above the advertised amount (e.g. ₹5,000) qualify for free standard shipping.</li>
</ul>

<h2>3. Order Processing Time</h2>
<ul>
    <li>Orders are typically processed within <strong>1–2 business days</strong> after confirmation.</li>
    <li>Online payment orders are processed after successful payment.</li>
    <li>COD orders are processed after order verification.</li>
</ul>

<h2>4. Estimated Delivery</h2>
<p>Delivery timelines vary by location. Typical delivery is <strong>3–7 business days</strong> for metro cities and <strong>5–10 business days</strong> for other areas. Tracking details are shared once your order is dispatched.</p>

<h2>5. Cash on Delivery (COD)</h2>
<p>COD is available where enabled at checkout. If COD is not available, please choose online payment.</p>

<h2>6. Order Tracking</h2>
<p>Once shipped, you will receive tracking details via email/SMS (where enabled). You can also view order status and tracking ID from <a href="{{ route('orders.index') }}">My Orders</a> in your account.</p>

<h2>7. Delivery Attempts</h2>
<p>Delivery will be attempted at the address provided. Please ensure your phone number and address are correct. Failed delivery attempts may result in return of the package to us.</p>

<h2>8. Damaged or Missing Items</h2>
<p>If your package arrives damaged or items are missing, contact us within <strong>48 hours</strong> of delivery with photos and your order number. See our <a href="{{ route('pages.show', 'return-refund-policy') }}">Return & Refund Policy</a>.</p>

<h2>9. International Shipping</h2>
<p>We currently ship within India only.</p>

<h2>10. Contact</h2>
<p>Shipping queries: <a href="mailto:{{ config('store.support_email') }}">{{ config('store.support_email') }}</a> | +91 {{ config('store.support_phone') }}</p>
@endsection
