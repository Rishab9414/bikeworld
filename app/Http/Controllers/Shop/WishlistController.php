<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private WishlistService $wishlist) {}

    public function toggle(Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);

        $result = $this->wishlist->toggle(auth()->user(), $product);

        return response()->json([
            'success' => true,
            'wishlisted' => $result['wishlisted'],
            'count' => $result['count'],
            'message' => $result['message'],
        ]);
    }

    public function destroy(Product $product, Request $request): JsonResponse|RedirectResponse
    {
        $this->wishlist->remove(auth()->user(), $product);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'wishlisted' => false,
                'count' => $this->wishlist->countForUser(auth()->user()),
                'message' => 'Removed from wishlist.',
            ]);
        }

        return back()->with('success', 'Removed from wishlist.');
    }
}
