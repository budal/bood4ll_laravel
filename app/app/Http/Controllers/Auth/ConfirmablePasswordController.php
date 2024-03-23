<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ConfirmablePasswordController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Bood4ll', [
            'guest' => true,
            'build' => [
                [
                    'description' => "This is a secure area of the application. Please confirm your password before continuing.",
                    'callback' => 'password.confirm',
                    'method' => 'post',
                    'dialogConfirm' => 'Log in',
                    'fields' => [
                        [
                            'type' => 'password',
                            'name' => 'password',
                            'label' => 'Password',
                            'required' => true,
                            'autofocus' => true,
                        ]
                    ],
                ],
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages(['password' => __('auth.password')]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return response()->json([
            'redirect' => true,
            'url' => redirect()->intended(RouteServiceProvider::HOME)->getTargetUrl(),
        ]);
    }
}
