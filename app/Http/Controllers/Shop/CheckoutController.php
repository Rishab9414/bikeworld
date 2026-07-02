<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\CheckoutCustomerService;
use App\Services\DelhiveryService;
use App\Services\OrderService;
use App\Services\RazorpayService;
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
        private DelhiveryService $delhivery,
        private TaxService $tax,
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
        $shippingCharge = $subtotal >= 2000 ? 0 : 99;
        $tax = $taxSummary['tax_amount'];
        $taxLabel = $this->tax->taxLabel($items);
        $grandTotal = $taxSummary['items_total'] + $shippingCharge;
        $codEnabled = Setting::codEnabled();

        return view('shop.checkout.index', compact(
            'items',
            'subtotal',
            'shippingCharge',
            'tax',
            'taxLabel',
            'taxSummary',
            'grandTotal',
            'customer',
            'defaultAddress',
            'user',
            'codEnabled',
        ));
    }

    public function checkPincode(Request $request): JsonResponse
    {
        $request->validate(['pincode' => ['required', 'digits:6']]);

        return response()->json($this->delhivery->checkPincode($request->pincode));
    }

    public function store(Request $request)
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $sameBilling = $request->boolean('same_billing', true);
        $customerId = Customer::where('user_id', Auth::id())->value('id');

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
            'payment_method' => ['required', 'in:'.(Setting::codEnabled() ? 'cod,online' : 'online')],
        ]);

        if ($validated['payment_method'] === 'cod' && ! Setting::codEnabled()) {
            return back()->withInput()->with('error', 'Cash on Delivery is currently unavailable. Please pay online.');
        }

        $pincodeCheck = $this->delhivery->checkPincode($validated['shipping']['pincode']);
        if (! ($pincodeCheck['serviceable'] ?? false)) {
            return back()->withInput()->with('error', $pincodeCheck['message'] ?? 'Delivery is not available for this pincode.');
        }

        if ($validated['payment_method'] === 'cod' && ($pincodeCheck['cod_available'] ?? true) === false) {
            return back()->withInput()->with('error', 'Cash on Delivery is not available for this pincode. Please choose online payment.');
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
            $itemsTotal = $this->cart->subtotal();
            $shippingCharge = $itemsTotal >= 2000 ? 0 : 99;

            $orderData = array_merge($addresses, [
                'notes' => $validated['notes'] ?? null,
                'shipping_charge' => $shippingCharge,
                'expected_delivery' => now()->addDays($pincodeCheck['estimated_delivery_days'] ?? 5)->toDateString(),
            ]);

            $order = $this->orders->createFromCart(
                Auth::user(),
                $items,
                $orderData,
                $validated['payment_method']
            );

            $order->update(['customer_id' => $customer->id]);

            if ($validated['payment_method'] === 'online') {
                $this->razorpay->createRazorpayOrder($order);
                $this->cart->clear();

                return redirect()
                    ->route('orders.payment', $order)
                    ->with('success', 'Order created. Please complete payment.');
            }

            $this->cart->clear();
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully!');
    }
}
