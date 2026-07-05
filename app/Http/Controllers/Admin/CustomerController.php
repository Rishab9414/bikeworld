<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Services\ActivityLogger;
use App\Services\CustomerStatsService;
use App\Services\LoyaltyService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerStatsService $stats,
        private WalletService $walletService,
        private LoyaltyService $loyaltyService,
    ) {}

    public function index(): View
    {
        return view('admin.customers.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = Customer::query()
            ->select([
                'id', 'user_id', 'customer_code', 'full_name', 'email', 'mobile',
                'country_code', 'account_status', 'loyalty_tier', 'profile_image',
                'last_login', 'created_at',
            ])
            ->with([
                'wallet:id,customer_id,current_balance',
                'loyaltyPoint:id,customer_id,total_points',
            ]);

        if ($request->filled('search')) {
            $s = '%'.$request->search.'%';
            $query->where(fn ($q) => $q->where('full_name', 'like', $s)
                ->orWhere('email', 'like', $s)
                ->orWhere('mobile', 'like', $s)
                ->orWhere('customer_code', 'like', $s));
        }

        if ($request->filled('status')) {
            $query->where('account_status', $request->status);
        }

        if ($request->filled('verified')) {
            $verified = $request->verified === 'yes';
            $query->where('email_verified', $verified)->where('mobile_verified', $verified);
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->filled('loyalty_tier')) {
            $query->where('loyalty_tier', $request->loyalty_tier);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = min(max((int) $request->input('per_page', 20), 10), 50);
        $customers = $query->latest('id')->simplePaginate($perPage);
        $orderStats = $this->stats->listStats($customers->getCollection());

        $customers->getCollection()->transform(function (Customer $c) use ($orderStats) {
            $stats = $orderStats[$c->id] ?? ['total_orders' => 0, 'total_spend' => 0.0];

            return [
                'id' => $c->id,
                'customer_code' => $c->customer_code,
                'name' => $c->full_name,
                'mobile' => $c->country_code.' '.$c->mobile,
                'email' => $c->email,
                'registered_at' => $c->created_at?->format('M d, Y'),
                'last_login' => $c->last_login?->format('M d, Y H:i') ?? '—',
                'total_orders' => $stats['total_orders'],
                'total_spend' => $stats['total_spend'],
                'wallet_balance' => (float) ($c->wallet?->current_balance ?? 0),
                'loyalty_points' => (int) ($c->loyaltyPoint?->total_points ?? 0),
                'status' => $c->account_status,
                'loyalty_tier' => $c->loyalty_tier,
                'profile_image' => $c->profile_image ? asset('storage/'.$c->profile_image) : null,
            ];
        });

        return response()->json(['success' => true, 'data' => $customers]);
    }

    public function create(): View
    {
        return view('admin.customers.form', ['customer' => new Customer([
            'country_code' => '+91',
            'registration_source' => 'admin',
            'login_type' => 'email',
            'account_status' => 'active',
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCustomer($request);
        $data = $this->normalizeBooleans($request, $data);
        $customer = Customer::create($data);

        if ($request->filled('initial_wallet') && (float) $request->initial_wallet > 0) {
            $this->walletService->credit($customer->wallet, (float) $request->initial_wallet, 'Opening balance (admin)');
        }

        if ($request->filled('initial_points') && (int) $request->initial_points > 0) {
            $this->loyaltyService->earn($customer->loyaltyPoint, (int) $request->initial_points, 'Welcome points (admin)');
        }

        ActivityLogger::log('created', 'customers', $customer, "Customer {$customer->customer_code} created");

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'addresses', 'wallet.transactions' => fn ($q) => $q->latest()->limit(10),
            'loyaltyPoint.transactions' => fn ($q) => $q->latest()->limit(10),
            'wishlists.product', 'cartItems.product', 'reviews.product',
            'loginLogs' => fn ($q) => $q->latest()->limit(10),
            'supportTickets' => fn ($q) => $q->latest()->limit(5),
        ]);

        $stats = $this->stats->stats($customer);
        $orders = Order::query()
            ->where(function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
                if ($customer->user_id) {
                    $q->orWhere('user_id', $customer->user_id);
                }
            })
            ->latest()->limit(10)->get();

        return view('admin.customers.show', compact('customer', 'stats', 'orders'));
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validateCustomer($request, $customer->id);
        $data = $this->normalizeBooleans($request, $data);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $customer->update($data);

        ActivityLogger::log('updated', 'customers', $customer, "Customer {$customer->customer_code} updated");

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function toggleBlock(Customer $customer): JsonResponse
    {
        $customer->update([
            'account_status' => $customer->account_status === 'blocked' ? 'active' : 'blocked',
        ]);

        ActivityLogger::log('updated', 'customers', $customer, "Customer {$customer->customer_code} status changed to {$customer->account_status}");

        return response()->json([
            'success' => true,
            'message' => 'Customer status updated.',
            'status' => $customer->account_status,
        ]);
    }

    public function resetPassword(Request $request, Customer $customer): JsonResponse
    {
        $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);
        $customer->update(['password' => $request->password]);

        if ($customer->user) {
            $customer->user->update(['password' => $request->password]);
        }

        ActivityLogger::log('updated', 'customers', $customer, "Password reset for {$customer->customer_code}");

        return response()->json(['success' => true, 'message' => 'Password reset successfully.']);
    }

    public function verifyEmail(Customer $customer): JsonResponse
    {
        $customer->update(['email_verified' => true]);

        return response()->json(['success' => true, 'message' => 'Email marked as verified.']);
    }

    public function verifyMobile(Customer $customer): JsonResponse
    {
        $customer->update(['mobile_verified' => true]);

        return response()->json(['success' => true, 'message' => 'Mobile marked as verified.']);
    }

    public function walletAdjust(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:credit,debit'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $wallet = $customer->wallet ?? $customer->wallet()->create(['current_balance' => 0]);

        if ($data['type'] === 'credit') {
            $this->walletService->credit($wallet, (float) $data['amount'], $data['description'], 'admin');
        } else {
            $this->walletService->debit($wallet, (float) $data['amount'], $data['description'], 'admin');
        }

        ActivityLogger::log('updated', 'customers', $customer, "Wallet {$data['type']} ₹{$data['amount']} for {$customer->customer_code}");

        return response()->json([
            'success' => true,
            'message' => 'Wallet updated successfully.',
            'balance' => (float) $wallet->fresh()->current_balance,
        ]);
    }

    public function loyaltyAdjust(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:add,deduct'],
            'points' => ['required', 'integer', 'min:1'],
            'remarks' => ['required', 'string', 'max:255'],
        ]);

        $account = $customer->loyaltyPoint ?? $customer->loyaltyPoint()->create([]);

        if ($data['type'] === 'add') {
            $this->loyaltyService->earn($account, (int) $data['points'], $data['remarks'], 'admin');
        } else {
            $this->loyaltyService->redeem($account, (int) $data['points'], $data['remarks'], 'admin');
        }

        ActivityLogger::log('updated', 'customers', $customer, "Loyalty {$data['type']} {$data['points']} pts for {$customer->customer_code}");

        return response()->json([
            'success' => true,
            'message' => 'Loyalty points updated.',
            'points' => (int) $account->fresh()->total_points,
        ]);
    }

    public function export(): StreamedResponse
    {
        $filename = 'customers-'.now()->format('Y-m-d').'.csv';

        return Response::streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Code', 'Name', 'Email', 'Mobile', 'Status', 'Registered', 'Wallet', 'Points', 'Orders', 'Total Spend']);

            Customer::with(['wallet', 'loyaltyPoint'])->chunk(100, function ($customers) use ($handle) {
                foreach ($customers as $c) {
                    $stats = app(CustomerStatsService::class)->stats($c);
                    fputcsv($handle, [
                        $c->customer_code, $c->full_name, $c->email, $c->mobile,
                        $c->account_status, $c->created_at?->format('Y-m-d'),
                        $stats['wallet_balance'], $stats['loyalty_points'],
                        $stats['total_orders'], $stats['total_spend'],
                    ]);
                }
            });
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function validateCustomer(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email,'.$id],
            'mobile' => ['required', 'string', 'max:20', 'unique:customers,mobile,'.$id],
            'country_code' => ['required', 'string', 'max:10'],
            'password' => [$id ? 'nullable' : 'required', 'string', 'min:8'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'anniversary_date' => ['nullable', 'date'],
            'registration_source' => ['required', 'in:website,app,admin'],
            'login_type' => ['required', 'in:email,mobile,google,facebook'],
            'account_status' => ['required', 'in:active,inactive,blocked'],
            'customer_type' => ['nullable', 'string', 'max:50'],
            'loyalty_tier' => ['nullable', 'string', 'max:50'],
            'newsletter_subscription' => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
            'mobile_verified' => ['nullable', 'boolean'],
        ]);
    }

    private function normalizeBooleans(Request $request, array $data): array
    {
        $data['newsletter_subscription'] = $request->boolean('newsletter_subscription');
        $data['email_verified'] = $request->boolean('email_verified');
        $data['mobile_verified'] = $request->boolean('mobile_verified');

        return $data;
    }
}
