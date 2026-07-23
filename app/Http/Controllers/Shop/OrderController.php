<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Services\ProductReviewService;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private ProductReviewService $reviews) {}

    public function index()
    {
        $orders = auth()->user()->orders()->latest()->paginate(10);

        return view('shop.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $order->load([
            'items.product',
            'shipment.tracking',
            'shipmentRecord.tracking',
        ]);
        $customer = Customer::where('user_id', auth()->id())->first();
        $reviewableItems = $customer
            ? $this->reviews->reviewableItemsForOrder($order, $customer)
            : collect();

        $shipment = $order->shipment ?? $order->shipmentRecord;

        return view('shop.orders.show', compact('order', 'reviewableItems', 'shipment'));
    }

    public function confirmation(Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $order->load('items');

        return view('shop.orders.confirmation', compact('order'));
    }
}
