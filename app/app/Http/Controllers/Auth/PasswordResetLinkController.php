<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Default', [
            'isGuest' => true,
            'tabs' => false,
            'title' => 'Forgot your password?',
            'status' => session('status'),
            'form' => [
                [
                    'id' => 'forgotPassword',
                    'subtitle' => 'Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.',
                    'fields' => [
                        [
                            [
                                'type' => 'email',
                                'name' => 'email',
                                'title' => 'Email',
                                'required' => true,
                                'autofocus' => true,
                                'autocomplete' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'routes' => [
                'forgotPassword' => [
                    'route' => route('password.email'),
                    'method' => 'post',
                    'buttonTitle' => 'Email Password Reset Link',
                    'buttonClass' => 'justify-end',
                ],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages(['email' => [trans($status)]]);
    }
}
