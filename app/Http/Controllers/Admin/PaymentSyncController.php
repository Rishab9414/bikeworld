<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSyncController extends Controller
{
    public function index(Request $request, RazorpayService $razorpay): View
    {
        $limit = min(max((int) $request->query('limit', 100), 1), 500);
        $days = min(max((int) $request->query('days', 60), 1), 365);

        $results = $razorpay->syncAllPendingPayments($limit, $days);
        $ranAt = now()->format('d M Y, H:i:s');
        $mockMode = $razorpay->usesMockMode();

        return view('admin.payment-sync.index', compact('results', 'ranAt', 'limit', 'days', 'mockMode'));
    }
}
