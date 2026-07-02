<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\CreateShipmentJob;
use App\Jobs\GenerateInvoiceJob;
use App\Jobs\GenerateLabelJob;
use App\Jobs\SyncTrackingJob;
use App\Models\Order;
use App\Services\ActivityLogger;
use App\Services\DelhiveryService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orders,
        private DelhiveryService $delhivery,
    ) {}

    public function index(): View
    {
        return view('admin.orders.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Order::with(['customer', 'user', 'items'])->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('order_number', 'like', "%{$s}%")
                ->orWhereHas('customer', fn ($c) => $c->where('full_name', 'like', "%{$s}%")->orWhere('mobile', 'like', "%{$s}%"))
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")));
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

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20)->through(fn (Order $o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'customer_name' => $o->customer?->full_name ?? $o->user?->name ?? 'Guest',
                'mobile' => $o->customer?->mobile ?? $o->user?->phone ?? '—',
                'email' => $o->customer?->email ?? $o->user?->email ?? '—',
                'items_count' => $o->items->count(),
                'grand_total' => $o->displayTotal(),
                'payment_status' => $o->payment_status,
                'status' => $o->status,
                'created_at' => $o->created_at?->format('M d, Y H:i'),
            ]),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load([
            'customer', 'user', 'items.product', 'shipment.tracking',
            'shipmentRecord.tracking', 'invoiceRecord', 'statusLogs',
        ]);

        return view('admin.orders.show', [
            'order' => $order,
            'timeline' => $this->orders->timeline($order),
        ]);
    }

    public function confirm(Order $order): JsonResponse
    {
        $this->orders->confirm($order, 'admin');
        ActivityLogger::log('updated', 'orders', $order, "Order {$order->order_number} confirmed");

        return response()->json(['success' => true, 'message' => 'Order confirmed and stock reserved.', 'status' => $order->fresh()->status]);
    }

    public function createShipment(Order $order): JsonResponse
    {
        if (! in_array($order->status, ['confirmed', 'packing', 'packed'], true)) {
            return response()->json(['success' => false, 'message' => 'Order must be confirmed before creating shipment.'], 422);
        }

        CreateShipmentJob::dispatch($order);

        return response()->json(['success' => true, 'message' => 'Shipment creation queued. Refresh in a moment.']);
    }

    public function createShipmentSync(Order $order): JsonResponse
    {
        $shipment = $this->delhivery->createShipment($order->fresh(['items', 'customer']));
        ActivityLogger::log('updated', 'orders', $order, "Shipment created for {$order->order_number}");

        return response()->json([
            'success' => true,
            'message' => 'Shipment created.',
            'waybill' => $shipment->waybill,
            'tracking_url' => $shipment->tracking_url,
        ]);
    }

    public function generateInvoice(Order $order): JsonResponse
    {
        GenerateInvoiceJob::dispatch($order);
        $invoice = $this->orders->generateInvoice($order->fresh('items'));

        return response()->json([
            'success' => true,
            'message' => 'Invoice generated.',
            'url' => asset('storage/'.$invoice->invoice_pdf),
        ]);
    }

    public function printInvoice(Order $order): View
    {
        $order->load('items', 'customer', 'invoiceRecord');
        if (! $order->invoiceRecord) {
            $this->orders->generateInvoice($order);
            $order->load('invoiceRecord');
        }

        return view('admin.orders.invoice', compact('order'));
    }

    public function printLabel(Order $order)
    {
        $shipment = $order->shipment ?? $order->shipmentRecord;
        if (! $shipment) {
            abort(404, 'No shipment found. Create shipment first.');
        }

        $path = $this->delhivery->generateLabel($shipment);
        if (str_ends_with($path, '.html')) {
            return response(Storage::disk('public')->get($path))->header('Content-Type', 'text/html');
        }

        return response()->file(Storage::disk('public')->path($path));
    }

    public function schedulePickup(Order $order): JsonResponse
    {
        $shipment = $order->shipment ?? $order->shipmentRecord;
        if (! $shipment) {
            return response()->json(['success' => false, 'message' => 'Create shipment first.'], 422);
        }

        $this->delhivery->schedulePickup($shipment);
        ActivityLogger::log('updated', 'orders', $order, "Pickup scheduled for {$order->order_number}");

        return response()->json(['success' => true, 'message' => 'Pickup scheduled successfully.']);
    }

    public function trackShipment(Order $order): JsonResponse
    {
        $shipment = $order->shipment ?? $order->shipmentRecord;
        if (! $shipment) {
            return response()->json(['success' => false, 'message' => 'No shipment found.'], 404);
        }

        SyncTrackingJob::dispatch($shipment);

        return response()->json([
            'success' => true,
            'message' => 'Tracking sync queued.',
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

        $this->delhivery->cancelShipment($shipment);
        $this->orders->updateStatus($order, 'cancelled', 'Shipment Cancelled', null, 'admin');

        return response()->json(['success' => true, 'message' => 'Shipment cancelled.']);
    }

    public function cancel(Order $order): JsonResponse
    {
        $this->orders->cancel($order);
        ActivityLogger::log('updated', 'orders', $order, "Order {$order->order_number} cancelled");

        return response()->json(['success' => true, 'message' => 'Order cancelled.']);
    }

    public function refund(Order $order): JsonResponse
    {
        $this->orders->refund($order);
        ActivityLogger::log('updated', 'orders', $order, "Order {$order->order_number} refunded");

        return response()->json(['success' => true, 'message' => 'Refund processed.']);
    }

    public function returnOrder(Order $order): JsonResponse
    {
        $this->orders->updateStatus($order, 'returned', 'Return Initiated', 'Reverse pickup to be scheduled', 'admin');
        $this->delhivery->handleWebhook([
            'waybill' => $order->shipmentRecord?->waybill,
            'status' => 'returned',
            'remarks' => 'Return requested by admin',
        ]);
        ActivityLogger::log('updated', 'orders', $order, "Return initiated for {$order->order_number}");

        return response()->json(['success' => true, 'message' => 'Return initiated.']);
    }
}
