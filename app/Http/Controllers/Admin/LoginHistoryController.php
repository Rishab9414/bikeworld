<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginHistoryController extends Controller
{
    public function index(): View
    {
        return view('admin.users.login-history.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = LoginHistory::with('user')->latest('logged_in_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit(500)->get()->map(fn ($h) => [
                'id' => $h->id,
                'user_name' => $h->user?->name,
                'user_email' => $h->user?->email,
                'ip_address' => $h->ip_address,
                'user_agent' => $h->user_agent,
                'status' => $h->status,
                'logged_in_at' => $h->logged_in_at?->format('M d, Y H:i'),
                'logged_out_at' => $h->logged_out_at?->format('M d, Y H:i'),
            ]),
        ]);
    }
}
