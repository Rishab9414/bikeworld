<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::with('category')->withCount('usages')->orderByDesc('id')->get();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create(): View
    {
        return view('admin.coupons.form', [
            'coupon' => new Coupon([
                'is_active' => true,
                'type' => 'fixed',
                'usage_per_customer' => 1,
            ]),
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $coupon = Coupon::create($this->validated($request));
        ActivityLogger::log('created', 'coupons', $coupon, "Coupon {$coupon->code} created");

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.form', [
            'coupon' => $coupon,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->validated($request, $coupon));
        ActivityLogger::log('updated', 'coupons', $coupon, "Coupon {$coupon->code} updated");

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $code = $coupon->code;
        $coupon->delete();
        ActivityLogger::log('deleted', 'coupons', null, "Coupon {$code} deleted");

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted.');
    }

    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code,'.($coupon?->id ?? 'NULL')],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:fixed,percent'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_per_customer' => ['nullable', 'integer', 'min:1'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['category_id'] = $request->input('category_id') ?: null;
        $data['min_order_amount'] = $request->filled('min_order_amount') ? $data['min_order_amount'] : null;
        $data['max_discount'] = $request->filled('max_discount') ? $data['max_discount'] : null;
        $data['usage_limit'] = $request->filled('usage_limit') ? $data['usage_limit'] : null;
        $data['usage_per_customer'] = $request->filled('usage_per_customer') ? $data['usage_per_customer'] : null;

        if ($request->filled('starts_at')) {
            $data['starts_at'] = Carbon::parse($data['starts_at'])->startOfDay();
        } else {
            $data['starts_at'] = null;
        }

        if ($request->filled('expires_at')) {
            $data['expires_at'] = Carbon::parse($data['expires_at'])->endOfDay();
        } else {
            $data['expires_at'] = null;
        }

        if ($data['type'] === 'fixed') {
            $data['max_discount'] = null;
        }

        return $data;
    }

    private function categoryOptions()
    {
        $root = Category::where('slug', 'bike-accessories')->first();

        return Category::where('parent_id', $root?->id)
            ->orWhere('id', $root?->id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
