<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;

class WishlistService
{
    /** @return array<int, int> */
    public function productIdsForUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $customer = Customer::where('user_id', $user->id)->first();

        if (! $customer) {
            return [];
        }

        return Wishlist::where('customer_id', $customer->id)->pluck('product_id')->all();
    }

    public function countForUser(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        $customer = Customer::where('user_id', $user->id)->first();

        return $customer ? $customer->wishlists()->count() : 0;
    }

    public function isWishlisted(?User $user, Product $product): bool
    {
        return in_array($product->id, $this->productIdsForUser($user), true);
    }

    /** @return array{wishlisted: bool, count: int, message: string} */
    public function toggle(User $user, Product $product): array
    {
        $customer = $this->customerForUser($user);

        $existing = Wishlist::where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->whereNull('variant_id')
            ->first();

        if ($existing) {
            $existing->delete();

            return [
                'wishlisted' => false,
                'count' => $customer->wishlists()->count(),
                'message' => 'Removed from wishlist.',
            ];
        }

        Wishlist::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        return [
            'wishlisted' => true,
            'count' => $customer->wishlists()->count(),
            'message' => 'Added to wishlist.',
        ];
    }

    public function remove(User $user, Product $product): void
    {
        $customer = $this->customerForUser($user);

        Wishlist::where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->delete();
    }

    private function customerForUser(User $user): Customer
    {
        $customer = Customer::where('user_id', $user->id)->first();

        if (! $customer) {
            $customer = Customer::fromUser($user);
        }

        return $customer;
    }
}
