<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\User;

class CheckoutCustomerService
{
    public function syncFromCheckout(User $user, array $profile, array $shipping, ?array $billing = null, bool $sameBilling = true): Customer
    {
        $customer = Customer::where('user_id', $user->id)->first();

        if (! $customer) {
            $customer = Customer::fromUser($user, [
                'first_name' => $profile['first_name'],
                'last_name' => $profile['last_name'] ?? null,
                'email' => $profile['email'],
                'mobile' => $profile['mobile'],
                'country_code' => $profile['country_code'] ?? '+91',
                'gender' => $profile['gender'] ?? null,
                'newsletter_subscription' => $profile['newsletter_subscription'] ?? false,
            ]);
        } else {
            $customer->update([
                'first_name' => $profile['first_name'],
                'last_name' => $profile['last_name'] ?? null,
                'email' => $profile['email'],
                'mobile' => $profile['mobile'],
                'country_code' => $profile['country_code'] ?? '+91',
                'gender' => $profile['gender'] ?? null,
                'newsletter_subscription' => $profile['newsletter_subscription'] ?? false,
            ]);
        }

        if ($user->name !== $customer->full_name || ($user->phone ?? '') !== $customer->mobile) {
            $user->update([
                'name' => $customer->full_name,
                'phone' => $customer->mobile,
            ]);
        }

        $this->upsertAddress($customer, $shipping, shipping: true, billing: $sameBilling);

        if (! $sameBilling && $billing) {
            $this->upsertAddress($customer, $billing, shipping: false, billing: true);
        }

        return $customer->fresh(['addresses']);
    }

    public function buildOrderAddresses(array $shipping, ?array $billing = null, bool $sameBilling = true): array
    {
        $shippingText = $this->formatAddressText($shipping);
        $shippingJson = $this->buildAddressJson($shipping);

        if ($sameBilling || ! $billing) {
            return [
                'shipping_address' => $shippingText,
                'billing_address' => $shippingText,
                'shipping_address_json' => $shippingJson,
                'billing_address_json' => $shippingJson,
            ];
        }

        return [
            'shipping_address' => $shippingText,
            'billing_address' => $this->formatAddressText($billing),
            'shipping_address_json' => $shippingJson,
            'billing_address_json' => $this->buildAddressJson($billing),
        ];
    }

    public function formatAddressText(array $addr): string
    {
        $lines = array_filter([
            $addr['full_name'] ?? null,
            $addr['address_line_1'] ?? null,
            $addr['address_line_2'] ?? null,
            $addr['landmark'] ?? null,
            isset($addr['city'], $addr['state'])
                ? trim($addr['city'].', '.$addr['state'].($addr['district'] ? ' ('.$addr['district'].')' : ''))
                : null,
            $addr['pincode'] ?? null,
            isset($addr['country']) ? $addr['country'] : null,
            $addr['mobile'] ?? null,
        ]);

        return implode("\n", $lines);
    }

    public function buildAddressJson(array $addr): array
    {
        return [
            'name' => $addr['full_name'] ?? '',
            'line_1' => $addr['address_line_1'] ?? '',
            'line_2' => $addr['address_line_2'] ?? '',
            'landmark' => $addr['landmark'] ?? '',
            'city' => $addr['city'] ?? '',
            'district' => $addr['district'] ?? '',
            'state' => $addr['state'] ?? '',
            'pincode' => $addr['pincode'] ?? '',
            'country' => $addr['country'] ?? 'India',
            'phone' => $addr['mobile'] ?? '',
            'full_address' => $this->formatAddressText($addr),
        ];
    }

    private function upsertAddress(Customer $customer, array $data, bool $shipping, bool $billing): CustomerAddress
    {
        if ($shipping) {
            $customer->addresses()->update(['is_default_shipping' => false]);
        }
        if ($billing) {
            $customer->addresses()->update(['is_default_billing' => false]);
        }

        $existing = $customer->addresses()
            ->where('pincode', $data['pincode'])
            ->where('address_line_1', $data['address_line_1'])
            ->first();

        $payload = [
            'address_type' => $data['address_type'] ?? 'home',
            'full_name' => $data['full_name'],
            'mobile' => $data['mobile'],
            'address_line_1' => $data['address_line_1'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'landmark' => $data['landmark'] ?? null,
            'city' => $data['city'],
            'district' => $data['district'] ?? null,
            'state' => $data['state'],
            'country' => $data['country'] ?? 'India',
            'pincode' => $data['pincode'],
            'is_default_shipping' => $shipping,
            'is_default_billing' => $billing,
            'status' => 'active',
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return $customer->addresses()->create($payload);
    }
}
