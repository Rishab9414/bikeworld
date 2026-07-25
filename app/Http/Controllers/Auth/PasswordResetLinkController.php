<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email:filter', 'max:255'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        $email = strtolower(trim($validated['email']));

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('is_admin', false)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'We could not find a customer account with that email address.',
            ]);
        }

        if ($user->status === false) {
            throw ValidationException::withMessages([
                'email' => 'This account is inactive. Please contact support.',
            ]);
        }

        $status = Password::broker()->sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        return back()->with('status', 'We sent a password reset link to '.$user->email.'. The link expires in 60 minutes.');
    }
}
