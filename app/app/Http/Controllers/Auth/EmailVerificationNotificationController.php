<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with(
                [
                    'toast_type' => 'success',
                    'toast_message' => "The email address ':email' is verified.",
                    'toast_replacements' => ['email' => $request->user()->email],
                ]
            );
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with(
            [
                'toast_type' => 'success',
                'toast_message' => "A new verification link has been sent to the email ':email'.",
                'toast_replacements' => ['email' => $request->user()->email],
            ]
        );
    }
}
