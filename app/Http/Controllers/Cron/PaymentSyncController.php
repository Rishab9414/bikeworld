<?php

namespace App\Http\Controllers\Cron;

use App\Http\Controllers\Controller;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSyncController extends Controller
{
    public function run(Request $request, RazorpayService $razorpay): View
    {
        $token = (string) $request->query('token', '');
        $expected = (string) config('cron.sync_token', '');

        if ($expected === '' || ! hash_equals($expected, $token)) {
            abort(403, 'Invalid or missing sync token.');
        }

        $limit = min(max((int) $request->query('limit', 100), 1), 500);
        $days = min(max((int) $request->query('days', 60), 1), 365);

        $results = $razorpay->syncAllPendingPayments($limit, $days);
        $ranAt = now()->format('d M Y, H:i:s');

        return view('cron.payment-sync', compact('results', 'ranAt', 'limit', 'days'));
    }
}
