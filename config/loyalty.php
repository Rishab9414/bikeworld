<?php

return [
    'point_value' => 1,
    'min_redeem_points' => 100,
    'max_order_percent' => 20,
    'expiry_months' => 12,

    'earning' => [
        'registration' => 100,
        'first_order' => 200,
        'per_100_purchase' => 10,
        'product_review' => 20,
        'referral_signup' => 100,
        'referral_purchase' => 300,
        'birthday_bonus' => 200,
    ],

    'tiers' => [
        'bronze' => ['min_spend' => 0, 'label' => 'Bronze'],
        'silver' => ['min_spend' => 5000, 'label' => 'Silver'],
        'gold' => ['min_spend' => 15000, 'label' => 'Gold'],
        'platinum' => ['min_spend' => 50000, 'label' => 'Platinum'],
    ],
];
