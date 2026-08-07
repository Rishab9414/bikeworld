<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\CheckoutCustomerService;
use App\Services\CouponService;
use App\Services\OrderService;
use App\Services\RazorpayService;
use App\Services\ShippingService;
use App\Services\TaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private OrderService $orders,
        private RazorpayService $razorpay,
        private CheckoutCustomerService $checkoutCustomer,
        private ShippingService $shipping,
        private TaxService $tax,
        private CouponService $coupons,
    ) {}

    public function index()
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->with('addresses')->first();
        $defaultAddress = $customer?->addresses->firstWhere('is_default_shipping', true)
            ?? $customer?->addresses->first();

        $subtotal = $this->cart->subtotal();
        $taxSummary = $this->tax->summarizeCart($items);
        $couponsEnabled = Setting::couponsEnabled();
        $couponData = $couponsEnabled ? $this->coupons->resolve($user, $items, $taxSummary['items_total']) : null;
        $appliedCoupon = $couponData['coupon'] ?? null;
        $couponDiscount = $couponData['discount'] ?? 0;
        $orderAmount = max(0, $taxSummary['items_total'] - $couponDiscount);
        $shippingQuote = $this->shipping->calculateForCart($items, null, 'online', $orderAmount);
        $shippingCharge = $shippingQuote['amount'];
        $tax = $taxSummary['tax_amount'];
        $taxLabel = $this->tax->taxLabel($items);
        $grandTotal = max(0, $orderAmount + $shippingCharge);
        $freeShippingEnabled = Setting::freeShippingEnabled();
        $freeShippingMinAmount = Setting::freeShippingMinAmount();
        $freeShippingQualified = Setting::qualifiesForFreeShipping($orderAmount);
        $freeShippingRemaining = max(0, $freeShippingMinAmount - $orderAmount);
        $codEnabled = Setting::codEnabled();
        $razorpayEnabled = $this->razorpay->isAvailable();
        $razorpayLive = $razorpayEnabled && ! $this->razorpay->usesMockMode();
        $defaultPayment = old('payment_method', $codEnabled ? 'cod' : 'online');

        return view('shop.checkout.index', compact(
            'items',
            'subtotal',
            'shippingCharge',
            'shippingQuote',
            'tax',
            'taxLabel',
            'taxSummary',
            'grandTotal',
            'couponsEnabled',
            'appliedCoupon',
            'couponDiscount',
            'customer',
            'defaultAddress',
            'user',
            'codEnabled',
            'razorpayEnabled',
            'razorpayLive',
            'defaultPayment',
            'freeShippingEnabled',
            'freeShippingMinAmount',
            'freeShippingQualified',
            'freeShippingRemaining',
            'orderAmount',
        ));
    }

    public function checkPincode(Request $request): JsonResponse
    {
        $request->validate([
            'pincode' => ['required', 'digits:6'],
            'payment_method' => ['nullable', 'in:cod,online'],
        ]);

        $items = $this->cart->items();
        $orderAmount = $this->orderAmountForShipping($items);
        $shippingQuote = $this->shipping->calculateForCart(
            $items,
            $request->pincode,
            $request->input('payment_method', 'online'),
            $orderAmount
        );

        return response()->json([
            'success' => true,
            'serviceable' => true,
            'message' => 'Delivery available to this pincode.',
            'cod_available' => Setting::codEnabled(),
            'estimated_delivery_days' => (int) config('shipping.default_delivery_days', 5),
            'shipping_charge' => $shippingQuote['amount'],
            'shipping_source' => $shippingQuote['source'],
            'shipping_note' => $shippingQuote['note'],
        ]);
    }

    public function shippingQuote(Request $request): JsonResponse
    {
        $request->validate([
            'pincode' => ['nullable', 'digits:6'],
            'payment_method' => ['nullable', 'in:cod,online'],
        ]);

        $items = $this->cart->items();
        $taxSummary = $this->tax->summarizeCart($items);
        $orderAmount = $this->orderAmountForShipping($items, $taxSummary);
        $shippingQuote = $this->shipping->calculateForCart(
            $items,
            $request->input('pincode'),
            $request->input('payment_method', 'online'),
            $orderAmount
        );
        $shippingCharge = $shippingQuote['amount'];
        $couponData = Setting::couponsEnabled()
            ? $this->coupons->resolve(Auth::user(), $items, $taxSummary['items_total'])
            : null;
        $couponDiscount = $couponData['discount'] ?? 0;
        $grandTotal = max(0, $orderAmount + $shippingCharge);

        return response()->json(array_merge($shippingQuote, [
            'items_total' => $taxSummary['items_total'],
            'coupon_discount' => round($couponDiscount, 2),
            'coupon_code' => $couponData['coupon']->code ?? null,
            'grand_total' => round($grandTotal, 2),
        ]));
    }

    public function store(Request $request)
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $sameBilling = $request->boolean('same_billing', true);
        $customerId = Customer::where('user_id', Auth::id())->value('id');
        $codEnabled = Setting::codEnabled();
        $razorpayEnabled = $this->razorpay->isAvailable();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email,'.$customerId],
            'country_code' => ['required', 'string', 'max:10'],
            'mobile' => ['required', 'string', 'max:20', 'unique:customers,mobile,'.$customerId],
            'gender' => ['nullable', 'in:male,female,other'],
            'newsletter_subscription' => ['nullable', 'boolean'],

            'shipping.address_type' => ['required', 'in:home,office,other'],
            'shipping.full_name' => ['required', 'string', 'max:255'],
            'shipping.mobile' => ['required', 'string', 'max:20'],
            'shipping.address_line_1' => ['required', 'string', 'max:255'],
            'shipping.address_line_2' => ['nullable', 'string', 'max:255'],
            'shipping.landmark' => ['nullable', 'string', 'max:255'],
            'shipping.city' => ['required', 'string', 'max:100'],
            'shipping.district' => ['nullable', 'string', 'max:100'],
            'shipping.state' => ['required', 'string', 'max:100'],
            'shipping.country' => ['required', 'string', 'max:100'],
            'shipping.pincode' => ['required', 'digits:6'],

            'billing.address_type' => [$sameBilling ? 'nullable' : 'required', 'in:home,office,other'],
            'billing.full_name' => [$sameBilling ? 'nullable' : 'required', 'string', 'max:255'],
            'billing.mobile' => [$sameBilling ? 'nullable' : 'required', 'string', 'max:20'],
            'billing.address_line_1' => [$sameBilling ? 'nullable' : 'required', 'string', 'max:255'],
            'billing.address_line_2' => ['nullable', 'string', 'max:255'],
            'billing.landmark' => ['nullable', 'string', 'max:255'],
            'billing.city' => [$sameBilling ? 'nullable' : 'required', 'string', 'max:100'],
            'billing.district' => ['nullable', 'string', 'max:100'],
            'billing.state' => [$sameBilling ? 'nullable' : 'required', 'string', 'max:100'],
            'billing.country' => [$sameBilling ? 'nullable' : 'required', 'string', 'max:100'],
            'billing.pincode' => [$sameBilling ? 'nullable' : 'required', 'digits:6'],

            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:'.($codEnabled && $razorpayEnabled ? 'cod,online' : ($codEnabled ? 'cod' : 'online'))],
        ]);

        if ($validated['payment_method'] === 'online' && ! $this->razorpay->isAvailable()) {
            return back()->withInput()->with('error', 'Online payment is not available. Please choose Cash on Delivery or contact support.');
        }

        if ($validated['payment_method'] === 'cod' && ! Setting::codEnabled()) {
            return back()->withInput()->with('error', 'Cash on Delivery is currently unavailable. Please pay online.');
        }

        $profile = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'country_code' => $validated['country_code'],
            'gender' => $validated['gender'] ?? null,
            'newsletter_subscription' => $request->boolean('newsletter_subscription'),
        ];

        $shipping = $validated['shipping'];
        $billing = $sameBilling ? null : ($validated['billing'] ?? null);

        try {
            $customer = $this->checkoutCustomer->syncFromCheckout(
                Auth::user(),
                $profile,
                $shipping,
                $billing,
                $sameBilling,
            );

            $addresses = $this->checkoutCustomer->buildOrderAddresses($shipping, $billing, $sameBilling);

            $couponDiscount = 0;
            $coupon = null;
            $taxSummary = $this->tax->summarizeCart($items);
            if (Setting::couponsEnabled() && $this->coupons->getAppliedCode()) {
                $coupon = Coupon::where('code', $this->coupons->getAppliedCode())->first();
                if ($coupon) {
                    $couponDiscount = $this->coupons->validateForCheckout(
                        $coupon,
                        Auth::user(),
                        $items,
                        $taxSummary['items_total']
                    );
                }
            }

            $orderAmount = max(0, $taxSummary['items_total'] - $couponDiscount);
            $shippingQuote = $this->shipping->calculateForCart(
                $items,
                $validated['shipping']['pincode'],
                $validated['payment_method'],
                $orderAmount
            );
            $shippingCharge = $shippingQuote['amount'];

            $orderData = array_merge($addresses, [
                'notes' => $validated['notes'] ?? null,
                'shipping_charge' => $shippingCharge,
                'expected_delivery' => now()->addDays((int) config('shipping.default_delivery_days', 5))->toDateString(),
                'coupon' => $coupon,
                'coupon_discount' => $couponDiscount,
            ]);

            $order = $this->orders->createFromCart(
                Auth::user(),
                $items,
                $orderData,
                $validated['payment_method']
            );

            $order->update(['customer_id' => $customer->id]);

            if ($validated['payment_method'] === 'online') {
                try {
                    $this->razorpay->createRazorpayOrder($order);
                } catch (\Throwable $e) {
                    report($e);

                    return back()->withInput()->with('error', 'Could not start Razorpay payment: '.$e->getMessage());
                }

                $this->cart->clear();
                $this->coupons->remove();

                return redirect()->route('orders.payment', $order);
            }

            $this->cart->clear();
            $this->coupons->remove();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->with('error', $e->validator->errors()->first('code'));
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.confirmation', $order);
    }

    private function orderAmountForShipping($items, ?array $taxSummary = null): float
    {
        $taxSummary ??= $this->tax->summarizeCart($items);
        $couponData = Setting::couponsEnabled()
            ? $this->coupons->resolve(Auth::user(), $items, $taxSummary['items_total'])
            : null;
        $discount = $couponData['discount'] ?? 0;

        return max(0, $taxSummary['items_total'] - $discount);
    }
}
