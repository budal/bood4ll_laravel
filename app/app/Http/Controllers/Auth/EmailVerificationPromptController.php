<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|Response
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(RouteServiceProvider::HOME)
                    : Inertia::render('Default', [
                        'isGuest' => true,
                        'tabs' => false,
                        'title' => 'Email Verification',
                        'status' => session('status') == 'verification-link-sent' ? 'A new verification link has been sent to the email address you provided during registration.' : null,
                        'form' => [
                            [
                                'id' => 'emailVerification',
                                'subtitle' => "Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.",
                                'fields' => [
                                    [
                                        [
                                            'type' => 'links',
                                            'values' => [
                                                [
                                                    'title' => 'Log out',
                                                    'route' => 'logout',
                                                    'method' => 'post',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'routes' => [
                            'emailVerification' => [
                                'route' => route('verification.send'),
                                'method' => 'post',
                                'buttonTitle' => 'Resend Verification Email',
                                'buttonClass' => 'justify-end',
                            ],
                        ],
                    ]);
    }
}
