<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Bood4ll', [
            'guest' => true,
            'build' => [
                [
                    'label' => 'Sign in',
                    'fields' => [
                        [
                            'type' => 'columns',
                            'structure' => [
                                [
                                    'description' => 'Social',
                                    // 'class' => 'grid grid-cols-2 gap-2 w-[400px]',
                                    'fields' => [
                                        [
                                            'label' => 'Google',
                                            'type' => 'button',
                                            'class' => 'w-full',
                                            'severity' => 'contrast',
                                            'disabled' => getenv('GOOGLE_CLIENT_ID') == null,
                                            // 'icon' => 'google',
                                            'callback' => [
                                                'route' => 'authRedirect',
                                                'attributes' => ['provider' => 'google'],
                                                'external' => true,
                                            ],
                                        ],
                                        [
                                            'label' => 'Twitter',
                                            'type' => 'button',
                                            'class' => 'w-full',
                                            'severity' => 'contrast',
                                            'disabled' => getenv('TWITTER_CLIENT_ID') == null,
                                            // 'icon' => 'twitter',
                                            'callback' => [
                                                'route' => 'authRedirect',
                                                'attributes' => ['provider' => 'twitter'],
                                                'external' => true,
                                            ],
                                        ],
                                        [
                                            'label' => 'Github',
                                            'type' => 'button',
                                            'class' => 'w-full',
                                            'severity' => 'contrast',
                                            'disabled' => getenv('GITHUB_CLIENT_ID') == null,
                                            // 'icon' => 'github',
                                            'callback' => [
                                                'route' => 'authRedirect',
                                                'attributes' => ['provider' => 'github'],
                                                'external' => true,
                                            ],
                                        ],
                                        [
                                            'label' => 'Facebook',
                                            'type' => 'button',
                                            'class' => 'w-full',
                                            'severity' => 'contrast',
                                            'disabled' => getenv('FACEBOOK_CLIENT_ID') == null,
                                            // 'icon' => 'facebook',
                                            'callback' => [
                                                'route' => 'authRedirect',
                                                'attributes' => ['provider' => 'facebook'],
                                                'external' => true,
                                            ],
                                        ],
                                    ]
                                ],
                                // [
                                //     // 'class' => 'w-full md:w-2',
                                //     'fields' => [
                                //         [
                                //             'type' => 'divider',
                                //             'label' => 'OR',
                                //         ],
                                //     ]
                                // ],
                                [
                                    'description' => 'Account',
                                    // 'class' => 'w-[400px]',
                                    'callback' => 'login',
                                    'method' => 'post',
                                    'visible' => true,
                                    'fields' => [
                                        [
                                            'type' => 'input',
                                            'name' => 'email',
                                            'label' => 'Email',
                                            'required' => true,
                                            'autofocus' => true,
                                            'autocomplete' => true,
                                        ],
                                        [
                                            'type' => 'password',
                                            'name' => 'password',
                                            'label' => 'Password',
                                            'required' => true,
                                        ],
                                        [
                                            'type' => 'checkbox',
                                            'name' => 'remember',
                                            'label' => 'Remember me',
                                        ],

                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ]);
    }

    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        // $request->user()->createToken('token', $request->user()->getAbilities->pluck('ability')->toArray());

        $request->session()->regenerate();

        return response()->json([
            'redirectUrl' => redirect()->intended(RouteServiceProvider::HOME)->getTargetUrl(),
        ]);
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

            $user->createToken('token', $user->getAbilities->pluck('ability')->toArray());

            return redirect('/dashboard');
        }
    }
}
