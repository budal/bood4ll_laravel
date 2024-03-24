<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Bood4ll', [
            'guest' => true,
            'build' => [
                [
                    'label' => 'Forgot your password?',
                    'description' => "Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.",
                    'callback' => 'password.email',
                    'method' => 'post',
                    'dialogConfirm' => 'Email Password Reset Link',
                    'fields' => [
                        [
                            'type' => 'input',
                            'name' => 'email',
                            'title' => 'Email',
                            'required' => true,
                            'autocomplete' => true,
                        ],
                        [
                            'type' => 'links',
                            'values' => [
                                [
                                    'label' => 'Back to sign in',
                                    'route' => 'login',
                                ],
                            ],
                        ]
                    ],
                ],
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json([
                'type' => 'success',
                'message' => __($status),
                'redirectUrl' => redirect()->intended(route('login'))->getTargetUrl(),
            ]);
        }

        throw ValidationException::withMessages(['email' => [trans($status)]]);
    }
}
