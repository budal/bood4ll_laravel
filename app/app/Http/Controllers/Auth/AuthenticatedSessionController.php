<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

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
                                'type' => 'linkGroup',
                                'showIf' => getenv('GITHUB_CLIENT_ID') || getenv('GOOGLE_CLIENT_ID') || getenv('TWITTER_CLIENT_ID') || getenv('FACEBOOK_CLIENT_ID'),
                                'values' => [
                                    [
                                        'title' => 'Google',
                                        'type' => 'button',
                                        'showIf' => getenv('GOOGLE_CLIENT_ID') != null,
                                        'startIcon' => 'mdi:google',
                                        'route' => [
                                            'route' => 'authRedirect',
                                            'attributes' => ['google']
                                        ],
                                    ],
                                    [
                                        'title' => 'Twitter',
                                        'type' => 'button',
                                        'showIf' => getenv('TWITTER_CLIENT_ID') != null,
                                        'startIcon' => 'mdi:twitter',
                                        'route' => [
                                            'route' => 'authRedirect',
                                            'attributes' => ['twitter']
                                        ],
                                    ],
                                    [
                                        'title' => 'Github',
                                        'type' => 'button',
                                        'showIf' => getenv('GITHUB_CLIENT_ID') != null,
                                        'startIcon' => 'mdi:github',
                                        'route' => [
                                            'route' => 'authRedirect',
                                            'attributes' => ['github']
                                        ],
                                    ],
                                    [
                                        'title' => 'Facebook',
                                        'type' => 'button',
                                        'showIf' => getenv('FACEBOOK_CLIENT_ID') != null,
                                        'startIcon' => 'mdi:facebook',
                                        'route' => [
                                            'route' => 'authRedirect',
                                            'attributes' => ['facebook']
                                        ],
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

        $request->user()->createToken('token', $request->user()->getAllAbilities->whereNotNull('ability')->pluck('ability')->toArray());

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

    public function redirect(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        $providerUser = Socialite::driver($provider)->user();

        $user = User::withTrashed()->where('email', $providerUser->getEmail())->first();

        if ($user->deleted_at) {
            return Redirect::route('login')->with([
                'status' => 'This account was deleted. Contact our support.',
            ]);
        } elseif ($user->active === false) {
            return Redirect::route('login')->with([
                'status' => 'This account is inactivated. Contact our support.',
            ]);
        } else {
            $user = User::updateOrCreate([
                'email' => $providerUser->getEmail(),
            ], [
                'name' => $providerUser->getName(),
                'provider_name' => $provider,
                'provider_id' => $providerUser->getId(),
                'provider_avatar' => $providerUser->getAvatar(),
                'provider_token' => $providerUser->token,
                'provider_refresh_token' => $providerUser->refreshToken,
            ]);

            event(new Registered($user));

            Auth::login($user);

            $user->createToken('token', $user->getAllAbilities->whereNotNull('ability')->pluck('ability')->toArray());

            return redirect('/dashboard');
        }
    }
}
