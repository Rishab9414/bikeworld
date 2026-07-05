<?php

return [

    'site_name' => env('SEO_SITE_NAME', env('APP_NAME', 'BikeWorld')),

    'default_title' => env(
        'SEO_DEFAULT_TITLE',
        'BikeWorld — Buy Bike Accessories, Helmets & Riding Gear Online India'
    ),

    'default_description' => env(
        'SEO_DEFAULT_DESCRIPTION',
        'Shop premium bike accessories, helmets, gloves, riding jackets & motorcycle gear at BikeWorld. Genuine products, pan-India delivery, COD & easy returns. Order online today!'
    ),

    'default_keywords' => env(
        'SEO_DEFAULT_KEYWORDS',
        'bike accessories online india, motorcycle accessories, buy bike gear online, helmet online india, riding gloves, riding jacket, bike parts online, motorcycle helmet, bike care products, Royal Enfield accessories, KTM accessories, Bajaj Pulsar accessories, bike chain lube, COD bike accessories, pan india bike delivery, BikeWorld, BikeWorld India'
    ),

    'twitter_handle' => env('SEO_TWITTER_HANDLE', ''),

    'og_default_image' => env('SEO_OG_IMAGE', '/images/logo.png'),

    'locale' => env('SEO_LOCALE', 'en_IN'),

    'robots' => env('SEO_ROBOTS', 'index,follow'),

    'google_site_verification' => env('SEO_GOOGLE_SITE_VERIFICATION', ''),

    'noindex_routes' => [
        'cart.*',
        'checkout.*',
        'orders.*',
        'dashboard',
        'account.*',
        'login',
        'register',
        'password.*',
        'verification.*',
    ],

    'pages' => [
        'home' => [
            'title' => 'BikeWorld — Premium Bike Accessories & Riding Gear Online India',
            'description' => 'Discover helmets, gloves, jackets, chain lube & bike care at BikeWorld. Top brands, best prices, fast pan-India shipping & COD. Your one-stop motorcycle accessories store.',
            'keywords' => 'bike accessories online india, motorcycle gear shop, buy helmet online, riding accessories india, bike world, motorcycle parts online, bike gloves jacket helmet',
        ],
        'products' => [
            'title' => 'Shop Bike Accessories Online — Helmets, Gear & Parts | BikeWorld',
            'description' => 'Browse 100+ bike accessories — helmets, gloves, riding jackets, chain lube, bike care & more. Filter by category or bike model. Free delivery on select orders.',
            'keywords' => 'shop bike accessories, motorcycle accessories catalog, helmet gloves jacket online, bike gear india, bike parts shop online',
        ],
        'privacy-policy' => [
            'title' => 'Privacy Policy | BikeWorld India',
            'description' => 'Read how BikeWorld collects, uses and protects your personal data when you shop for bike accessories on our website.',
            'keywords' => 'BikeWorld privacy policy, data protection, bike accessories store privacy',
        ],
        'terms-and-conditions' => [
            'title' => 'Terms & Conditions | BikeWorld India',
            'description' => 'Terms and conditions for shopping bike accessories, helmets and riding gear at BikeWorld. Orders, payments, delivery and user obligations.',
            'keywords' => 'BikeWorld terms and conditions, online bike shop terms, motorcycle accessories purchase terms',
        ],
        'shipping-policy' => [
            'title' => 'Shipping Policy — Pan-India Delivery | BikeWorld',
            'description' => 'Shipping timelines, delivery partners, tracking and serviceable pincodes for bike accessories orders placed at BikeWorld across India.',
            'keywords' => 'bike accessories shipping india, motorcycle gear delivery, pan india bike delivery, BikeWorld shipping policy',
        ],
        'return-refund-policy' => [
            'title' => 'Return & Refund Policy | BikeWorld India',
            'description' => 'Easy returns and refunds on eligible bike accessories and riding gear purchased from BikeWorld. Know eligibility, timelines and process.',
            'keywords' => 'bike accessories return policy, helmet return online, BikeWorld refund policy, motorcycle gear exchange',
        ],
        'cancellation-policy' => [
            'title' => 'Cancellation Policy | BikeWorld India',
            'description' => 'How to cancel your bike accessories order at BikeWorld before dispatch. Cancellation rules, timelines and refund details.',
            'keywords' => 'order cancellation BikeWorld, cancel bike accessories order, online motorcycle shop cancellation',
        ],
    ],

    'templates' => [
        'product_title' => '{name} — Buy Online at {site}',
        'product_description' => 'Buy {name} online at BikeWorld. {category} · ₹{price}. Genuine quality, fast delivery across India. {stock}',
        'product_keywords' => '{name}, buy {name} online, {category} india, {brand} bike accessories, motorcycle gear online, BikeWorld',
        'category_title' => '{name} — Shop Online | BikeWorld India',
        'category_description' => 'Shop {name} online at BikeWorld. Best prices on quality motorcycle {name_lower} with pan-India delivery and COD on eligible orders.',
        'category_keywords' => '{name} online india, buy {name_lower}, motorcycle {name_lower}, bike {name_lower} shop, BikeWorld',
        'vehicle_brand_title' => '{name} Bike Accessories — Shop by Model | BikeWorld',
        'vehicle_brand_description' => 'Find accessories compatible with {name} bikes. Helmets, gloves, riding gear & parts for all {name} models at BikeWorld India.',
        'vehicle_brand_keywords' => '{name} accessories online, {name} bike parts, {name} riding gear, {name} helmet gloves, BikeWorld',
        'bike_model_title' => '{brand} {name} Accessories — Compatible Gear | BikeWorld',
        'bike_model_description' => 'Shop accessories made for {brand} {name}. Helmets, gloves, jackets & bike care products with fast delivery from BikeWorld.',
        'bike_model_keywords' => '{brand} {name} accessories, {name} bike gear, {name} compatible parts, motorcycle accessories india, BikeWorld',
    ],

];
