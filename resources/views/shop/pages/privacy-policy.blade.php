@extends('shop.pages._layout')

@section('policy-content')
<p>At {{ config('app.name') }} ("we", "us", "our"), we respect your privacy and are committed to protecting your personal data. This Privacy Policy explains how we collect, use, store, and safeguard your information when you use our website and purchase bike accessories, helmets, riding gear, and related products.</p>

<h2>1. Information We Collect</h2>
<p>We may collect the following information when you register, place an order, or contact us:</p>
<ul>
    <li><strong>Personal details:</strong> Name, email address, mobile number, gender (optional)</li>
    <li><strong>Delivery & billing addresses:</strong> Full address, city, state, pincode, landmark</li>
    <li><strong>Order information:</strong> Products purchased, payment method, transaction references</li>
    <li><strong>Account data:</strong> Login credentials, order history, wishlist, wallet/loyalty points (if applicable)</li>
    <li><strong>Technical data:</strong> IP address, browser type, device information, cookies, and usage data</li>
</ul>

<h2>2. How We Use Your Information</h2>
<p>We use your data to:</p>
<ul>
    <li>Process and deliver your orders</li>
    <li>Calculate shipping charges and verify serviceable pincodes</li>
    <li>Process online payments via Razorpay and Cash on Delivery (COD) orders</li>
    <li>Send order confirmations, shipping updates, and customer support responses</li>
    <li>Apply coupons, offers, and free-shipping eligibility</li>
    <li>Improve our website, products, and customer experience</li>
    <li>Comply with legal and tax obligations (including GST)</li>
</ul>

<h2>3. Payment Information</h2>
<p>Online payments are processed securely through <strong>Razorpay</strong>. We do not store your full card, UPI PIN, or net banking credentials on our servers. Payment data is handled according to Razorpay's privacy and security standards.</p>

<h2>4. Shipping & Logistics</h2>
<p>We share necessary delivery details (name, address, phone, order value, payment mode) with our logistics partners such as <strong>Delhivery</strong> to fulfil shipments. These partners are required to use your data only for delivery purposes.</p>

<h2>5. Cookies</h2>
<p>We use cookies and similar technologies to keep you logged in, remember cart items, and analyse site traffic. You can disable cookies in your browser settings, but some features may not work properly.</p>

<h2>6. Data Sharing</h2>
<p>We do not sell your personal data. We may share information with:</p>
<ul>
    <li>Payment gateways (Razorpay)</li>
    <li>Courier and logistics partners (Delhivery)</li>
    <li>Technology service providers hosting our platform</li>
    <li>Law enforcement or regulators when required by law</li>
</ul>

<h2>7. Data Retention</h2>
<p>We retain your information for as long as your account is active or as needed to fulfil orders, resolve disputes, and meet legal requirements.</p>

<h2>8. Your Rights</h2>
<p>You may request access, correction, or deletion of your personal data by contacting us at <a href="mailto:{{ config('store.support_email') }}">{{ config('store.support_email') }}</a>. You may also update your profile and addresses from your account dashboard.</p>

<h2>9. Security</h2>
<p>We implement reasonable technical and organisational measures to protect your data. However, no method of transmission over the internet is 100% secure.</p>

<h2>10. Changes to This Policy</h2>
<p>We may update this Privacy Policy from time to time. Changes will be posted on this page with an updated date.</p>

<h2>11. Contact Us</h2>
<p>For privacy-related questions, contact:<br>
<strong>{{ config('app.name') }}</strong><br>
Email: <a href="mailto:{{ config('store.support_email') }}">{{ config('store.support_email') }}</a><br>
Phone: +91 {{ config('store.support_phone') }}<br>
Mumbai, Maharashtra, India</p>
@endsection
