<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use App\Services\TaxService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart,
        private TaxService $tax,
    ) {}

    public function index()
    {
        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal();
        $taxSummary = $this->tax->summarizeCart($items);
        $taxLabel = $this->tax->taxLabel($items);
        $shippingCharge = $subtotal >= 2000 ? 0 : 99;
        $grandTotal = $taxSummary['items_total'] + $shippingCharge;

        return view('shop.cart.index', compact(
            'items',
            'subtotal',
            'taxSummary',
            'taxLabel',
            'shippingCharge',
            'grandTotal',
        ));
    }

    public function store(Request $request, Product $product)
    {
        abort_unless($product->is_active && $product->isInStock(), 404);

        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$product->stock],
        ]);

        $this->cart->add($product, (int) $request->quantity);

        return redirect()->route('cart.index')->with('success', 'Product added to cart.');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $this->authorizeCartItem($cartItem);

        $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:'.$cartItem->product->stock],
        ]);

        $this->cart->update($cartItem, (int) $request->quantity);

        return redirect()->route('cart.index')->with('success', 'Cart updated.');
    }

    public function destroy(CartItem $cartItem)
    {
        $this->authorizeCartItem($cartItem);

        $this->cart->remove($cartItem);

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    private function authorizeCartItem(CartItem $cartItem): void
    {
        if (auth()->check()) {
            abort_unless($cartItem->user_id === auth()->id(), 403);
        } else {
            abort_unless($cartItem->session_id === session()->getId(), 403);
        }
    }
}
