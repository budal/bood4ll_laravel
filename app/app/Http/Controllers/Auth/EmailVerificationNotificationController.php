<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'type' => 'success',
                'message' => "The email address ':email' is verified.",
                'replacements' => ['email' => $request->user()->email],
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'type' => 'success',
            'message' => "A new verification link has been sent to the email ':email'.",
            'replacements' => ['email' => $request->user()->email],
            'redirectUrl' => redirect()->intended(RouteServiceProvider::HOME)->getTargetUrl(),
        ]);
    }
}
