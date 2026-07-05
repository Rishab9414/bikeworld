<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public const SESSION_KEY = 'applied_coupon_code';

    public function enabled(): bool
    {
        return Setting::couponsEnabled();
    }

    public function getAppliedCode(): ?string
    {
        $code = Session::get(self::SESSION_KEY);

        return $code ? strtoupper(trim((string) $code)) : null;
    }

    public function apply(string $code, ?User $user, Collection $cartItems, float $itemsTotal): array
    {
        if (! $this->enabled()) {
            throw ValidationException::withMessages(['code' => 'Coupons are currently disabled.']);
        }

        $coupon = $this->findByCode($code);
        $this->validateForCart($coupon, $user, $cartItems, $itemsTotal);
        $discount = $this->calculateDiscount($coupon, $cartItems, $itemsTotal);

        Session::put(self::SESSION_KEY, $coupon->code);

        return [
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => "Coupon {$coupon->code} applied. You save ₹".number_format($discount, 2).'.',
        ];
    }

    public function remove(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function resolve(?User $user, Collection $cartItems, float $itemsTotal): ?array
    {
        if (! $this->enabled()) {
            $this->remove();

            return null;
        }

        $code = $this->getAppliedCode();
        if (! $code) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->first();
        if (! $coupon) {
            $this->remove();

            return null;
        }

        try {
            $this->validateForCart($coupon, $user, $cartItems, $itemsTotal);
        } catch (ValidationException) {
            $this->remove();

            return null;
        }

        return [
            'coupon' => $coupon,
            'discount' => $this->calculateDiscount($coupon, $cartItems, $itemsTotal),
        ];
    }

    public function validateForCheckout(Coupon $coupon, User $user, Collection $cartItems, float $itemsTotal): float
    {
        if (! $this->enabled()) {
            throw ValidationException::withMessages(['code' => 'Coupons are currently disabled.']);
        }

        $this->validateForCart($coupon, $user, $cartItems, $itemsTotal);

        return $this->calculateDiscount($coupon, $cartItems, $itemsTotal);
    }

    public function recordUsage(Coupon $coupon, User $user, Order $order, float $discountAmount): void
    {
        $customerId = Customer::where('user_id', $user->id)->value('id');

        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'customer_id' => $customerId,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'discount_amount' => $discountAmount,
        ]);
    }

    public function calculateDiscount(Coupon $coupon, Collection $cartItems, float $itemsTotal): float
    {
        $baseAmount = $this->eligibleAmount($coupon, $cartItems, $itemsTotal);

        if ($baseAmount <= 0) {
            return 0;
        }

        $discount = $coupon->type === 'percent'
            ? round($baseAmount * ((float) $coupon->value / 100), 2)
            : min((float) $coupon->value, $baseAmount);

        if ($coupon->type === 'percent' && $coupon->max_discount) {
            $discount = min($discount, (float) $coupon->max_discount);
        }

        return min($discount, $itemsTotal);
    }

    private function findByCode(string $code): Coupon
    {
        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['code' => 'Invalid coupon code.']);
        }

        return $coupon;
    }

    private function validateForCart(Coupon $coupon, ?User $user, Collection $cartItems, float $itemsTotal): void
    {
        if (! $coupon->isCurrentlyActive()) {
            $status = $coupon->adminStatus();
            throw ValidationException::withMessages([
                'code' => match ($status['key']) {
                    'expired' => 'This coupon has expired.',
                    'scheduled' => 'This coupon is not valid yet.',
                    'disabled' => 'This coupon is not active.',
                    default => 'This coupon is not active or has expired.',
                },
            ]);
        }

        if ($coupon->usage_limit && $coupon->totalUsageCount() >= $coupon->usage_limit) {
            throw ValidationException::withMessages(['code' => 'This coupon has reached its usage limit.']);
        }

        if ($user && $coupon->usage_per_customer) {
            $customerId = Customer::where('user_id', $user->id)->value('id');
            $used = $coupon->customerUsageCount($customerId, $user->id);

            if ($used >= $coupon->usage_per_customer) {
                throw ValidationException::withMessages(['code' => 'You have already used this coupon.']);
            }
        }

        $eligibleAmount = $this->eligibleAmount($coupon, $cartItems, $itemsTotal);

        if ($eligibleAmount <= 0) {
            throw ValidationException::withMessages(['code' => 'This coupon does not apply to items in your cart.']);
        }

        $minOrder = (float) ($coupon->min_order_amount ?? 0);
        if ($minOrder > 0 && $eligibleAmount < $minOrder) {
            throw ValidationException::withMessages([
                'code' => 'Minimum order amount for this coupon is ₹'.number_format($minOrder, 2).'.',
            ]);
        }
    }

    private function eligibleAmount(Coupon $coupon, Collection $cartItems, float $itemsTotal): float
    {
        if (! $coupon->category_id) {
            return $itemsTotal;
        }

        return $cartItems
            ->filter(fn ($item) => (int) $item->product->category_id === (int) $coupon->category_id)
            ->sum(fn ($item) => $item->subtotal());
    }
}
