<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Sync — {{ config('app.name') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f8fafc; color: #0f172a; margin: 0; padding: 24px; }
        .wrap { max-width: 960px; margin: 0 auto; }
        h1 { font-size: 1.5rem; margin: 0 0 4px; }
        .meta { color: #64748b; font-size: 0.875rem; margin-bottom: 24px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 24px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; }
        .card strong { display: block; font-size: 1.75rem; }
        .card span { font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
        section { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 16px; overflow: hidden; }
        section h2 { font-size: 1rem; margin: 0; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        th, td { padding: 10px 16px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { color: #64748b; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; }
        .ok { color: #059669; font-weight: 600; }
        .warn { color: #d97706; }
        .err { color: #dc2626; }
        .empty { padding: 20px 16px; color: #94a3b8; }
        .refresh { display: inline-block; margin-top: 16px; padding: 10px 16px; background: #4f46e5; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 0.875rem; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>Payment Sync Complete</h1>
    <p class="meta">Ran at {{ $ranAt }} · Last {{ $days }} days · Max {{ $limit }} orders</p>

    <div class="grid">
        <div class="card"><strong>{{ $results['checked'] }}</strong><span>Checked</span></div>
        <div class="card"><strong class="ok">{{ count($results['updated']) }}</strong><span>Updated to Paid</span></div>
        <div class="card"><strong class="warn">{{ count($results['still_pending']) }}</strong><span>Still Pending</span></div>
        <div class="card"><strong class="err">{{ count($results['errors']) }}</strong><span>Errors</span></div>
    </div>

    <section>
        <h2>Updated to Paid</h2>
        @if(empty($results['updated']))
            <p class="empty">No orders were updated.</p>
        @else
            <table>
                <thead><tr><th>Order</th><th>Razorpay Payment ID</th><th>Amount</th><th>Paid At</th></tr></thead>
                <tbody>
                @foreach($results['updated'] as $row)
                    <tr>
                        <td>{{ $row['order_number'] }}</td>
                        <td>{{ $row['razorpay_payment_id'] }}</td>
                        <td>₹{{ number_format($row['amount'], 2) }}</td>
                        <td>{{ $row['paid_at'] ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <section>
        <h2>Still Pending (no successful payment on Razorpay)</h2>
        @if(empty($results['still_pending']))
            <p class="empty">None — all checked orders are paid or none matched.</p>
        @else
            <table>
                <thead><tr><th>Order</th><th>Status</th><th>Razorpay Order ID</th><th>Amount</th><th>Created</th></tr></thead>
                <tbody>
                @foreach($results['still_pending'] as $row)
                    <tr>
                        <td>{{ $row['order_number'] }}</td>
                        <td>{{ $row['payment_status'] }}</td>
                        <td>{{ $row['razorpay_order_id'] }}</td>
                        <td>₹{{ number_format($row['amount'], 2) }}</td>
                        <td>{{ $row['created_at'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </section>

    @if(!empty($results['errors']))
    <section>
        <h2>Errors</h2>
        <table>
            <thead><tr><th>Order</th><th>Message</th></tr></thead>
            <tbody>
            @foreach($results['errors'] as $row)
                <tr>
                    <td>{{ $row['order_number'] }}</td>
                    <td class="err">{{ $row['message'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>
    @endif

    <a class="refresh" href="{{ request()->fullUrl() }}">Run Again</a>
</div>
</body>
</html>
