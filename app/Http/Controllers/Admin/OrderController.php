<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\CancelShipmentJob;
use App\Jobs\CreateShipmentJob;
use App\Jobs\GenerateInvoiceJob;
use App\Jobs\GenerateLabelJob;
use App\Jobs\SchedulePickupJob;
use App\Jobs\TrackShipmentJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\DelhiveryService;
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

    public function createShipment(Order $order): JsonResponse
    {
        if (! in_array($order->status, ['confirmed', 'packing', 'packed'], true)) {
            return response()->json(['success' => false, 'message' => 'Order must be confirmed before creating shipment.'], 422);
        }

        CreateShipmentJob::dispatchSync($order->id, auth()->id());

        return response()->json(['success' => true, 'message' => 'Shipment created.', 'reload' => true]);
    }

    /** @deprecated Use createShipment — kept for backwards compatibility */
    public function createShipmentSync(Order $order): JsonResponse
    {
        return $this->createShipment($order);
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

    public function generateLabel(Order $order): JsonResponse
    {
        $shipment = $order->shipment ?? $order->shipmentRecord;

        if (! $shipment) {
            return response()->json(['success' => false, 'message' => 'Create shipment first.'], 422);
        }

        $waybill = trim((string) ($shipment->waybill ?: $shipment->tracking_number));
        if ($waybill === '') {
            return response()->json([
                'success' => false,
                'message' => 'AWB / waybill is missing. Create the Delhivery shipment again, then print the label.',
            ], 422);
        }

        try {
            GenerateLabelJob::dispatchSync($shipment->id, auth()->id());
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'message' => 'Label generated.', 'reload' => true]);
    }

    public function printLabel(Order $order, DelhiveryService $delhivery)
    {
        $shipment = $order->shipment ?? $order->shipmentRecord;

        if (! $shipment) {
            abort(404, 'No shipment found. Create shipment first.');
        }

        $waybill = trim((string) ($shipment->waybill ?: $shipment->tracking_number));
        if ($waybill === '') {
            abort(422, 'AWB / waybill is missing. Create the Delhivery shipment again, then print the label.');
        }

        try {
            if (! $shipment->shipping_label || ! Storage::disk('public')->exists($shipment->shipping_label)) {
                GenerateLabelJob::dispatchSync($shipment->id, auth()->id());
                $shipment->refresh();
            }
        } catch (\Throwable $e) {
            abort(422, $e->getMessage());
        }

        $path = $shipment->shipping_label;

        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404, 'Label not ready yet. Try again in a moment.');
        }

        // Old failed runs may have saved Delhivery JSON error as a "pdf"
        $contents = Storage::disk('public')->get($path);
        if (str_starts_with(ltrim($contents), '{') || str_contains($contents, 'wbns key missing')) {
            Storage::disk('public')->delete($path);
            $shipment->update(['shipping_label' => null]);

            try {
                GenerateLabelJob::dispatchSync($shipment->id, auth()->id());
                $shipment->refresh();
                $path = $shipment->shipping_label;
            } catch (\Throwable $e) {
                abort(422, $e->getMessage());
            }

            if (! $path || ! Storage::disk('public')->exists($path)) {
                abort(422, 'Could not regenerate shipping label.');
            }

            $contents = Storage::disk('public')->get($path);
        }

        if (str_ends_with($path, '.html')) {
            return response($contents)->header('Content-Type', 'text/html');
        }

        return response()->file(Storage::disk('public')->path($path));
    }

    public function schedulePickup(Order $order): JsonResponse
    {
        $shipment = $order->shipment ?? $order->shipmentRecord;

        if (! $shipment) {
            return response()->json(['success' => false, 'message' => 'Create shipment first.'], 422);
        }

        SchedulePickupJob::dispatchSync($shipment->id, auth()->id());

        return response()->json(['success' => true, 'message' => 'Pickup scheduled.', 'reload' => true]);
    }

    public function trackShipment(Order $order): JsonResponse
    {
        $shipment = $order->shipment ?? $order->shipmentRecord;

        if (! $shipment) {
            return response()->json(['success' => false, 'message' => 'No shipment found.'], 404);
        }

        TrackShipmentJob::dispatchSync($shipment->id);

        return response()->json([
            'success' => true,
            'message' => 'Tracking updated.',
            'reload' => true,
            'tracking_url' => $shipment->tracking_url,
            'waybill' => $shipment->waybill,
        ]);
    }

    public function cancelShipment(Order $order): JsonResponse
    {
        $shipment = $order->shipment ?? $order->shipmentRecord;

        if (! $shipment) {
            return response()->json(['success' => false, 'message' => 'No shipment found.'], 404);
        }

        CancelShipmentJob::dispatchSync($shipment->id, $order->id, auth()->id());

        return response()->json(['success' => true, 'message' => 'Shipment cancelled.', 'reload' => true]);
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

    public function returnOrder(Order $order, OrderService $orders, DelhiveryService $delhivery): JsonResponse
    {
        $orders->updateStatus($order, 'returned', 'Return Initiated', 'Reverse pickup to be scheduled', 'admin');
        $delhivery->handleWebhook([
            'waybill' => $order->shipmentRecord?->waybill,
            'status' => 'returned',
            'remarks' => 'Return requested by admin',
        ]);
        ActivityLogger::log('updated', 'orders', $order, "Return initiated for {$order->order_number}");

        return response()->json(['success' => true, 'message' => 'Return initiated.']);
    }
}
