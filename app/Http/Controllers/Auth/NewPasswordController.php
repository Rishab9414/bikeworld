<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'request' => $request,
            'token' => $request->route('token'),
            'email' => old('email', $request->query('email')),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email:filter', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Please enter a new password.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $email = strtolower(trim($request->input('email')));

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('is_admin', false)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'We could not find a customer account with that email address.',
            ]);
        }

        $status = Password::broker()->reset(
            [
                'email' => $user->email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'token' => $request->token,
            ],
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => $request->password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        return redirect()
            ->route('login')
            ->with('status', 'Your password has been reset. You can sign in with your new password.');
    }
}
