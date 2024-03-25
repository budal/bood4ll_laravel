<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): JsonResponse | Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'redirectUrl' => redirect()->intended(RouteServiceProvider::HOME)->getTargetUrl(),
            ]);
        } else {
            return Inertia::render('Bood4ll', [
                'guest' => true,
                'build' => [
                    [
                        'label' => 'Email Verification',
                        'description' => "Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.",
                        'callback' => 'verification.send',
                        'method' => 'post',
                        'dialogConfirm' => 'Resend Verification Email',
                    ],
                ]
            ]);
        }
    }
}
