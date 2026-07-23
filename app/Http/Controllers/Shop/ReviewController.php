<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Services\ProductReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct(private ProductReviewService $reviews) {}

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'order_item_id' => ['nullable', 'exists:order_items,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:150'],
            'review' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $customer = $this->customer();
        $this->reviews->store($customer, $data);

        $product = Product::findOrFail($data['product_id']);

        return back()->with('success', 'Thank you! Your review has been published.');
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
