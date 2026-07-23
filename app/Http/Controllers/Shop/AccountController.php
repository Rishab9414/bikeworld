<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Services\CustomerStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(private CustomerStatsService $stats) {}

    public function dashboard(): View
    {
        $customer = $this->customer();
        $stats = $this->stats->stats($customer);

        $recentOrders = Order::query()
            ->where(function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
                if ($customer->user_id) {
                    $q->orWhere('user_id', $customer->user_id);
                }
            })
            ->latest()->limit(5)->get();

        return view('shop.account.dashboard', compact('customer', 'stats', 'recentOrders'));
    }

    public function profile(): View
    {
        return view('shop.account.profile', ['customer' => $this->customer()]);
    }

    public function updateProfile(Request $request)
    {
        $customer = $this->customer();
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'mobile' => ['required', 'string', 'max:20', 'unique:customers,mobile,'.$customer->id],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'anniversary_date' => ['nullable', 'date'],
            'newsletter_subscription' => ['nullable', 'boolean'],
        ]);
        $data['newsletter_subscription'] = $request->boolean('newsletter_subscription');
        $customer->update($data);

        if ($customer->user) {
            $customer->user->update([
                'name' => trim($data['first_name'].' '.($data['last_name'] ?? '')),
                'phone' => $data['mobile'],
            ]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function addresses(): View
    {
        $customer = $this->customer()->load('addresses');

        return view('shop.account.addresses', compact('customer'));
    }

    public function storeAddress(Request $request)
    {
        $customer = $this->customer();
        $data = $this->validateAddress($request);

        $this->applyDefaultFlags($customer, $request, $data);
        $customer->addresses()->create($data);

        return back()->with('success', 'Address added successfully.');
    }

    public function updateAddress(Request $request, int $address)
    {
        $customer = $this->customer();
        $record = $customer->addresses()->findOrFail($address);
        $data = $this->validateAddress($request);

        $this->applyDefaultFlags($customer, $request, $data, $record->id);
        $record->update($data);

        return back()->with('success', 'Address updated successfully.');
    }

    public function destroyAddress(int $address)
    {
        $customer = $this->customer();
        $record = $customer->addresses()->findOrFail($address);
        $record->delete();

        return back()->with('success', 'Address removed.');
    }

    public function defaultAddress(Request $request, int $address)
    {
        $customer = $this->customer();
        $record = $customer->addresses()->findOrFail($address);
        $type = $request->validate(['type' => ['required', 'in:shipping,billing']])['type'];

        if ($type === 'shipping') {
            $customer->addresses()->update(['is_default_shipping' => false]);
            $record->update(['is_default_shipping' => true]);
        } else {
            $customer->addresses()->update(['is_default_billing' => false]);
            $record->update(['is_default_billing' => true]);
        }

        return back()->with('success', 'Default address updated.');
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'address_type' => ['required', 'in:home,office,other'],
            'full_name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:20'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:10'],
            'is_default_shipping' => ['nullable', 'boolean'],
            'is_default_billing' => ['nullable', 'boolean'],
        ]);
    }

    private function applyDefaultFlags(Customer $customer, Request $request, array &$data, ?int $exceptId = null): void
    {
        if ($request->boolean('is_default_shipping')) {
            $customer->addresses()->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->update(['is_default_shipping' => false]);
        }
        if ($request->boolean('is_default_billing')) {
            $customer->addresses()->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->update(['is_default_billing' => false]);
        }

        $data['is_default_shipping'] = $request->boolean('is_default_shipping');
        $data['is_default_billing'] = $request->boolean('is_default_billing');
    }

    public function wishlist(): View
    {
        $customer = $this->customer()->load('wishlists.product');

        return view('shop.account.wishlist', compact('customer'));
    }

    public function wallet(): View
    {
        $customer = $this->customer()->load(['wallet.transactions' => fn ($q) => $q->latest()->limit(20)]);

        return view('shop.account.wallet', compact('customer'));
    }

    public function loyalty(): View
    {
        $customer = $this->customer()->load(['loyaltyPoint.transactions' => fn ($q) => $q->latest()->limit(20)]);

        return view('shop.account.loyalty', compact('customer'));
    }

    public function reviews(): View
    {
        $customer = $this->customer()->load('reviews.product');

        return view('shop.account.reviews', compact('customer'));
    }

    private function customer(): Customer
    {
        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->first();

        if (! $customer) {
            $customer = Customer::fromUser($user);
        }

        return $customer;
    }
}
