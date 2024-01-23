<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Default', [
            'isGuest' => true,
            'tabs' => false,
            'title' => 'Log in',
            'status' => session('status'),
            'form' => [
                [
                    'id' => 'loginScreen',
                    'fields' => [
                        [
                            [
                                'type' => 'external_links',
                                'showIf' => getenv('GITHUB_CLIENT_ID') || getenv('GOOGLE_CLIENT_ID') || getenv('TWITTER_CLIENT_ID') || getenv('FACEBOOK_CLIENT_ID'),
                                'values' => [
                                    [
                                        'title' => 'Google',
                                        'type' => 'button',
                                        'showIf' => getenv('GOOGLE_CLIENT_ID') != null,
                                        'icon' => 'mdi:google',
                                        'route' => 'authGoogle',
                                    ],
                                    [
                                        'title' => 'Twitter',
                                        'type' => 'button',
                                        'showIf' => getenv('TWITTER_CLIENT_ID') != null,
                                        'icon' => 'mdi:twitter',
                                        'route' => 'register',
                                    ],
                                    [
                                        'title' => 'Github',
                                        'type' => 'button',
                                        'showIf' => getenv('GITHUB_CLIENT_ID') != null,
                                        'icon' => 'mdi:github',
                                        'route' => 'authGithub',
                                    ],
                                    [
                                        'title' => 'Facebook',
                                        'type' => 'button',
                                        'showIf' => getenv('FACEBOOK_CLIENT_ID') != null,
                                        'icon' => 'mdi:facebook',
                                        'route' => 'register',
                                    ],
                                ],
                            ],
                            [
                                'type' => 'separator',
                                'showIf' => getenv('GITHUB_CLIENT_ID') || getenv('GOOGLE_CLIENT_ID') || getenv('TWITTER_CLIENT_ID') || getenv('FACEBOOK_CLIENT_ID'),
                            ],
                            [
                                'type' => 'email',
                                'name' => 'email',
                                'title' => 'Email',
                                'required' => true,
                                'autofocus' => true,
                                'autocomplete' => true,
                            ],
                            [
                                'type' => 'password',
                                'name' => 'password',
                                'title' => 'Password',
                                'required' => true,
                            ],
                            [
                                'type' => 'checkbox',
                                'name' => 'remember',
                                'title' => 'Remember me',
                            ],
                            [
                                'type' => 'links',
                                'values' => [
                                    [
                                        'title' => 'Not registered?',
                                        'route' => 'register',
                                        'showIf' => Route::has('register'),
                                    ],
                                    [
                                        'title' => 'Forgot your password?',
                                        'route' => 'password.request',
                                        'showIf' => Route::has('password.request'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'routes' => [
                'loginScreen' => [
                    'route' => route('login'),
                    'method' => 'post',
                    'buttonTitle' => 'Log in',
                    'buttonClass' => 'justify-end',
                    'reset' => true,
                    'fieldsToReset' => ['password'],
                ],
            ],
            'data' => [
                'remember' => false,
            ],
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
