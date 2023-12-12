<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
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
        return Inertia::render('Default', [
            'isGuest' => true,
            'tabs' => false,
            'title' => 'Confirm Password',
            'form' => [
                [
                    'id' => 'confirmablePassword',
                    'subtitle' => 'This is a secure area of the application. Please confirm your password before continuing.',
                    'fields' => [
                        [
                            [
                                'type' => 'password',
                                'name' => 'password',
                                'title' => 'Password',
                                'required' => true,
                                'autofocus' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'routes' => [
                'confirmablePassword' => [
                    'route' => route('password.confirm'),
                    'method' => 'post',
                    'buttonTitle' => 'Confirm',
                    'buttonClass' => 'justify-end',
                    'reset' => true,
                ],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages(['password' => __('auth.password')]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
