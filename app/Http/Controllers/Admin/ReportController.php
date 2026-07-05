<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private ReportService $reports) {}

    public function index(Request $request): View
    {
        $range = $this->reports->parseDateRange($request);
        $overview = $this->reports->overviewStats($range['from'], $range['to']);

        return view('admin.reports.index', [
            'reportTypes' => ReportService::REPORT_TYPES,
            'range' => $range,
            'overview' => $overview,
        ]);
    }

    public function show(Request $request, string $type): View
    {
        abort_unless(isset(ReportService::REPORT_TYPES[$type]), 404);

        $range = $this->reports->parseDateRange($request);
        $meta = ReportService::REPORT_TYPES[$type];
        $data = $this->reportData($type, $range['from'], $range['to']);

        return view("admin.reports.{$type}", [
            'type' => $type,
            'meta' => $meta,
            'range' => $range,
            'data' => $data,
        ]);
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        abort_unless(isset(ReportService::REPORT_TYPES[$type]), 404);

        $range = $this->reports->parseDateRange($request);
        $data = $this->reportData($type, $range['from'], $range['to']);
        $filename = "{$type}-report-".now()->format('Y-m-d').'.csv';

        return Response::streamDownload(function () use ($type, $data) {
            $handle = fopen('php://output', 'w');
            $this->writeCsv($handle, $type, $data);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function reportData(string $type, $from, $to): array
    {
        return match ($type) {
            'sales' => $this->reports->salesReport($from, $to),
            'orders' => $this->reports->ordersReport($from, $to),
            'products' => $this->reports->productsReport($from, $to),
            'customers' => $this->reports->customersReport($from, $to),
            'coupons' => $this->reports->couponsReport($from, $to),
            'payments' => $this->reports->paymentsReport($from, $to),
            'inventory' => $this->reports->inventoryReport(),
            'shipping' => $this->reports->shippingReport($from, $to),
            default => [],
        };
    }

    /** @param resource $handle */
    private function writeCsv($handle, string $type, array $data): void
    {
        match ($type) {
            'sales' => $this->exportSales($handle, $data),
            'orders' => $this->exportOrders($handle, $data),
            'products' => $this->exportProducts($handle, $data),
            'customers' => $this->exportCustomers($handle, $data),
            'coupons' => $this->exportCoupons($handle, $data),
            'payments' => $this->exportPayments($handle, $data),
            'inventory' => $this->exportInventory($handle, $data),
            'shipping' => $this->exportShipping($handle, $data),
            default => fputcsv($handle, ['No data']),
        };
    }

    /** @param resource $handle */
    private function exportSales($handle, array $data): void
    {
        fputcsv($handle, ['Metric', 'Value']);
        foreach ($data['summary'] as $key => $value) {
            fputcsv($handle, [ucwords(str_replace('_', ' ', $key)), $value]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['Date', 'Orders', 'Revenue']);
        foreach ($data['daily'] as $row) {
            fputcsv($handle, [$row['date'], $row['orders'], $row['revenue']]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['Payment Method', 'Orders', 'Revenue']);
        foreach ($data['by_payment_method'] as $row) {
            fputcsv($handle, [$row['label'], $row['orders'], $row['revenue']]);
        }
    }

    /** @param resource $handle */
    private function exportOrders($handle, array $data): void
    {
        fputcsv($handle, ['Order #', 'Date', 'Customer', 'Email', 'Status', 'Payment Method', 'Payment Status', 'Items', 'Total']);
        foreach ($data['rows'] as $row) {
            fputcsv($handle, [
                $row['order_number'], $row['date'], $row['customer'], $row['email'],
                $row['status'], $row['payment_method'], $row['payment_status'],
                $row['items'], $row['total'],
            ]);
        }
    }

    /** @param resource $handle */
    private function exportProducts($handle, array $data): void
    {
        fputcsv($handle, ['Product', 'SKU', 'Category', 'Qty Sold', 'Revenue', 'Orders']);
        foreach ($data['best_sellers'] as $row) {
            fputcsv($handle, [
                $row['name'], $row['sku'], $row['category'],
                $row['quantity_sold'], $row['revenue'], $row['orders'],
            ]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['Category', 'Qty Sold', 'Revenue', 'Products']);
        foreach ($data['by_category'] as $row) {
            fputcsv($handle, [$row['category'], $row['quantity_sold'], $row['revenue'], $row['products']]);
        }
    }

    /** @param resource $handle */
    private function exportCustomers($handle, array $data): void
    {
        fputcsv($handle, ['Code', 'Name', 'Email', 'Mobile', 'Registered', 'Orders', 'Total Spend']);
        foreach ($data['top_customers'] as $row) {
            fputcsv($handle, [
                $row['code'], $row['name'], $row['email'], $row['mobile'],
                $row['registered'], $row['orders'], $row['total_spend'],
            ]);
        }
    }

    /** @param resource $handle */
    private function exportCoupons($handle, array $data): void
    {
        fputcsv($handle, ['Code', 'Type', 'Value', 'Usages', 'Discount Given', 'Order Revenue']);
        foreach ($data['by_coupon'] as $row) {
            fputcsv($handle, [
                $row['code'], $row['type'], $row['value'],
                $row['usage_count'], $row['discount_given'], $row['order_revenue'],
            ]);
        }
    }

    /** @param resource $handle */
    private function exportPayments($handle, array $data): void
    {
        fputcsv($handle, ['Order #', 'Date', 'Method', 'Status', 'Razorpay ID', 'Paid At', 'Amount']);
        foreach ($data['rows'] as $row) {
            fputcsv($handle, [
                $row['order_number'], $row['date'], $row['method'], $row['status'],
                $row['razorpay_id'], $row['paid_at'], $row['amount'],
            ]);
        }
    }

    /** @param resource $handle */
    private function exportInventory($handle, array $data): void
    {
        fputcsv($handle, ['SKU', 'Product', 'Category', 'Brand', 'Stock', 'Reserved', 'Available', 'Alert Level', 'Cost Value', 'Retail Value', 'Status']);
        foreach ($data['rows'] as $row) {
            fputcsv($handle, [
                $row['sku'], $row['name'], $row['category'], $row['brand'],
                $row['stock'], $row['reserved'], $row['available'], $row['alert_level'],
                $row['cost_value'], $row['retail_value'], $row['status'],
            ]);
        }
    }

    /** @param resource $handle */
    private function exportShipping($handle, array $data): void
    {
        fputcsv($handle, ['Order #', 'Waybill', 'Courier', 'Status', 'Shipping Cost', 'Charged', 'Pickup', 'Est. Delivery', 'Created']);
        foreach ($data['rows'] as $row) {
            fputcsv($handle, [
                $row['order_number'], $row['waybill'], $row['courier'], $row['status'],
                $row['shipping_cost'], $row['charged'], $row['pickup_date'],
                $row['estimated_delivery'], $row['created'],
            ]);
        }
    }
}
