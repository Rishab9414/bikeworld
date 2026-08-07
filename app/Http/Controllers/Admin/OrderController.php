<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateInvoiceJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\ManualShippingService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('admin.orders.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Order::query()
            ->select([
                'id', 'order_number', 'customer_id', 'user_id',
                'status', 'payment_status', 'grand_total', 'total', 'created_at',
            ])
            ->with([
                'customer:id,full_name,mobile,email',
                'user:id,name,email,phone',
            ])
            ->withCount('items');

        if ($request->filled('search')) {
            $s = '%'.$request->search.'%';
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', $s)
                    ->orWhereIn('customer_id', Customer::query()
                        ->where(function ($c) use ($s) {
                            $c->where('full_name', 'like', $s)->orWhere('mobile', 'like', $s);
                        })
                        ->select('id'))
                    ->orWhereIn('user_id', User::query()
                        ->where(function ($u) use ($s) {
                            $u->where('name', 'like', $s)->orWhere('email', 'like', $s);
                        })
                        ->select('id'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = min(max((int) $request->input('per_page', 20), 10), 50);

        $paginator = $query->latest('id')->simplePaginate($perPage);

        $paginator->getCollection()->transform(fn (Order $o) => [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'customer_name' => $o->customer?->full_name ?? $o->user?->name ?? 'Guest',
            'mobile' => $o->customer?->mobile ?? $o->user?->phone ?? '—',
            'email' => $o->customer?->email ?? $o->user?->email ?? '—',
            'items_count' => $o->items_count,
            'grand_total' => $o->displayTotal(),
            'payment_status' => $o->payment_status,
            'status' => $o->status,
            'created_at' => $o->created_at?->format('M d, Y H:i'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $paginator,
        ]);
    }

    public function show(Order $order, OrderService $orders): View
    {
        $order->load([
            'customer', 'user', 'items.product', 'shipment.tracking',
            'shipmentRecord.tracking', 'invoiceRecord', 'statusLogs',
        ]);

        return view('admin.orders.show', [
            'order' => $order,
            'timeline' => $orders->timeline($order),
        ]);
    }

    public function confirm(Order $order, OrderService $orders): JsonResponse
    {
        $orders->confirm($order, 'admin');
        ActivityLogger::log('updated', 'orders', $order, "Order {$order->order_number} confirmed");

        return response()->json(['success' => true, 'message' => 'Order confirmed and stock reserved.', 'status' => $order->fresh()->status]);
    }

    public function updateStatus(Request $request, Order $order, OrderService $orders): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,packing,packed,shipped,out_for_delivery,delivered,cancelled,returned,refunded,completed'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $label = ucwords(str_replace('_', ' ', $data['status']));
        $orders->updateStatus($order, $data['status'], $label, $data['remarks'] ?? null, 'admin');
        ActivityLogger::log('updated', 'orders', $order, "Order {$order->order_number} status manually set to {$data['status']}");

        return response()->json([
            'success' => true,
            'message' => "Order status updated to {$label}.",
            'status' => $order->fresh()->status,
        ]);
    }

    public function saveShipment(Request $request, Order $order, ManualShippingService $shipping, OrderService $orders): JsonResponse
    {
        if (! in_array($order->status, ['confirmed', 'packing', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'completed'], true)) {
            return response()->json(['success' => false, 'message' => 'Confirm the order before adding shipment details.'], 422);
        }

        $data = $request->validate([
            'courier_name' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['required', 'string', 'max:100'],
            'tracking_url' => ['nullable', 'url', 'max:500'],
            'estimated_delivery' => ['nullable', 'date'],
            'shipment_status' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $shipment = $shipping->createOrUpdateShipment($order, array_merge($data, [
            'add_tracking_scan' => true,
            'remarks' => $data['remarks'] ?? 'Shipment details saved manually',
        ]));

        if (in_array($order->status, ['confirmed', 'packing', 'packed'], true)) {
            $orders->updateStatus($order, 'shipped', 'Shipped', 'Tracking ID added manually', 'admin');
        }

        ActivityLogger::log('updated', 'orders', $order, "Manual shipment saved for {$order->order_number} — AWB {$shipment->tracking_number}");

        return response()->json([
            'success' => true,
            'message' => 'Shipment details saved.',
            'reload' => true,
        ]);
    }

    public function generateInvoice(Order $order): JsonResponse
    {
        GenerateInvoiceJob::dispatchSync($order->id, auth()->id());

        return response()->json(['success' => true, 'message' => 'Invoice generated.', 'reload' => true]);
    }

    public function printInvoice(Order $order, OrderService $orders): View
    {
        $order->load('items', 'customer', 'invoiceRecord');

        if (! $order->invoiceRecord) {
            GenerateInvoiceJob::dispatchSync($order->id, auth()->id());
            $order->load('invoiceRecord');
        }

        return view('admin.orders.invoice', compact('order'));
    }

    public function generateLabel(Order $order, ManualShippingService $shipping): JsonResponse
    {
        $shipment = $order->shipment ?? $order->shipmentRecord;

        if (! $shipment) {
            return response()->json(['success' => false, 'message' => 'Save shipment details with a tracking ID first.'], 422);
        }

        $waybill = trim((string) ($shipment->waybill ?: $shipment->tracking_number));
        if ($waybill === '') {
            return response()->json([
                'success' => false,
                'message' => 'Tracking ID is missing. Save shipment details first.',
            ], 422);
        }

        try {
            $shipping->generateLabel($shipment);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        ActivityLogger::log('updated', 'orders', $order, "Shipping label generated for {$order->order_number}");

        return response()->json(['success' => true, 'message' => 'Label generated.', 'reload' => true]);
    }

    public function printLabel(Order $order, ManualShippingService $shipping)
    {
        $shipment = $order->shipment ?? $order->shipmentRecord;

        if (! $shipment) {
            abort(404, 'No shipment found. Save shipment details first.');
        }

        $waybill = trim((string) ($shipment->waybill ?: $shipment->tracking_number));
        if ($waybill === '') {
            abort(422, 'Tracking ID is missing. Save shipment details first.');
        }

        try {
            if (! $shipment->shipping_label || ! Storage::disk('public')->exists($shipment->shipping_label)) {
                $shipping->generateLabel($shipment);
                $shipment->refresh();
            }
        } catch (\Throwable $e) {
            abort(422, $e->getMessage());
        }

        $path = $shipment->shipping_label;

        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404, 'Label not ready yet. Try again in a moment.');
        }

        $contents = Storage::disk('public')->get($path);

        if (str_ends_with($path, '.html')) {
            return response($contents)->header('Content-Type', 'text/html');
        }

        return response()->file(Storage::disk('public')->path($path));
    }

    public function cancel(Order $order, OrderService $orders): JsonResponse
    {
        $orders->cancel($order);
        ActivityLogger::log('updated', 'orders', $order, "Order {$order->order_number} cancelled");

        return response()->json(['success' => true, 'message' => 'Order cancelled.']);
    }

    public function refund(Order $order, OrderService $orders): JsonResponse
    {
        $orders->refund($order);
        ActivityLogger::log('updated', 'orders', $order, "Order {$order->order_number} refunded");

        return response()->json(['success' => true, 'message' => 'Refund processed.']);
    }

    public function returnOrder(Order $order, OrderService $orders): JsonResponse
    {
        $orders->updateStatus($order, 'returned', 'Return Initiated', 'Return marked manually', 'admin');
        ActivityLogger::log('updated', 'orders', $order, "Return initiated for {$order->order_number}");

        return response()->json(['success' => true, 'message' => 'Return initiated.']);
    }
}
