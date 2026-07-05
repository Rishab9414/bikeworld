@extends('shop.pages._layout')

@section('policy-content')
<p>Welcome to {{ config('app.name') }}. By accessing our website and placing an order, you agree to these Terms & Conditions. Please read them carefully before using our services.</p>

<h2>1. About Us</h2>
<p>{{ config('app.name') }} is an online store selling genuine bike accessories, helmets, riding jackets, gloves, luggage, and related motorcycle gear across India.</p>

<h2>2. Eligibility</h2>
<p>You must be at least 18 years old and capable of entering into a legally binding contract to purchase from our website. By registering, you confirm that the information provided is accurate and complete.</p>

<h2>3. Products & Pricing</h2>
<ul>
    <li>All product images are for representation; slight colour or packaging variations may occur.</li>
    <li>Prices are listed in Indian Rupees (INR) and may include or exclude GST as indicated on the product page.</li>
    <li>We reserve the right to change prices, offers, and product availability without prior notice.</li>
    <li>Orders are subject to stock availability. If an item is unavailable after payment, we will offer a refund or alternative.</li>
</ul>

<h2>4. Orders & Payments</h2>
<ul>
    <li>Placing an order constitutes an offer to purchase, subject to acceptance and confirmation.</li>
    <li>We accept <strong>online payments</strong> (UPI, cards, net banking via Razorpay) and <strong>Cash on Delivery (COD)</strong> where available.</li>
    <li>COD availability depends on your delivery pincode and admin settings.</li>
    <li>We may cancel orders suspected of fraud, incorrect pricing, or bulk/reseller abuse.</li>
</ul>

<h2>5. Shipping</h2>
<p>Shipping charges are calculated at checkout based on your pincode, order weight, and payment method. Free shipping may apply on orders above the minimum amount shown on our website. See our <a href="{{ route('pages.show', 'shipping-policy') }}">Shipping Policy</a> for details.</p>

<h2>6. Returns & Refunds</h2>
<p>Returns and refunds are governed by our <a href="{{ route('pages.show', 'return-refund-policy') }}">Return & Refund Policy</a>.</p>

<h2>7. User Account</h2>
<p>You are responsible for maintaining the confidentiality of your login credentials and for all activity under your account. Notify us immediately of any unauthorised use.</p>

<h2>8. Intellectual Property</h2>
<p>All content on this website — including logos, text, images, and design — is owned by {{ config('app.name') }} or its licensors and may not be copied or reproduced without permission.</p>

<h2>9. Limitation of Liability</h2>
<p>We are not liable for indirect, incidental, or consequential damages arising from use of our website or products. Our liability is limited to the value of the order in dispute, to the extent permitted by law.</p>

<h2>10. Governing Law</h2>
<p>These terms are governed by the laws of India. Disputes shall be subject to the jurisdiction of courts in Mumbai, Maharashtra.</p>

<h2>11. Contact</h2>
<p>Email: <a href="mailto:{{ config('store.support_email') }}">{{ config('store.support_email') }}</a> | Phone: +91 {{ config('store.support_phone') }}</p>
@endsection
