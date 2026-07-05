<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\TaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CouponService $coupons,
        private TaxService $tax,
    ) {}

    public function apply(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:50']]);

        $items = $this->cart->items();
        if ($items->isEmpty()) {
            return $this->respond($request, false, 'Your cart is empty.');
        }

        $taxSummary = $this->tax->summarizeCart($items);

        try {
            $result = $this->coupons->apply(
                $request->input('code'),
                Auth::user(),
                $items,
                $taxSummary['items_total']
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->respond($request, false, $e->validator->errors()->first('code'));
        }

        return $this->respond($request, true, $result['message'], $result);
    }

    public function remove(Request $request): RedirectResponse|JsonResponse
    {
        $this->coupons->remove();

        return $this->respond($request, true, 'Coupon removed.');
    }

    private function respond(Request $request, bool $success, string $message, ?array $data = null): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            $items = $this->cart->items();
            $taxSummary = $this->tax->summarizeCart($items);

            if ($success && $data) {
                $coupon = $data['coupon'];
                $discount = $data['discount'];
            } else {
                $resolved = $this->coupons->resolve(Auth::user(), $items, $taxSummary['items_total']);
                $coupon = $resolved['coupon'] ?? null;
                $discount = $resolved['discount'] ?? 0;
            }

            return response()->json([
                'success' => $success,
                'message' => $message,
                'coupon_code' => $coupon?->code,
                'coupon_discount' => round((float) $discount, 2),
                'items_total' => $taxSummary['items_total'],
                'payable_total' => max(0, $taxSummary['items_total'] - (float) $discount),
            ], $success ? 200 : 422);
        }

        return redirect()->back()->with($success ? 'success' : 'error', $message);
    }
}
