<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Bood4ll', [
            'guest' => true,
            'build' => [
                [
                    'label' => "Register",
                    'callback' => 'register',
                    'method' => 'post',
                    'fields' => [
                        [
                            'type' => 'input',
                            'name' => 'name',
                            'label' => 'Name',
                            'required' => true,
                            'autofocus' => true,
                            'autocomplete' => true,
                        ],
                        [
                            'type' => 'input',
                            'name' => 'email',
                            'label' => 'Email',
                            'required' => true,
                            'autocomplete' => true,
                        ],
                        [
                            'type' => 'password',
                            'name' => 'password',
                            'label' => 'Password',
                            'required' => true,
                        ],
                        [
                            'type' => 'password',
                            'id' => 'password_confirmation',
                            'name' => 'password_confirmation',
                            'label' => 'Confirm Password',
                            'required' => true,
                        ],
                        [
                            'type' => 'links',
                            'values' => [
                                [
                                    'label' => 'Already registered?',
                                    'route' => 'login',
                                ],
                            ],
                        ]
                    ],
                ],
            ]
        ]);


        return Inertia::render('Default', [
            'isGuest' => true,
            'tabs' => false,
            'title' => 'Register',
            'status' => session('status'),
            'form' => [
                [
                    'id' => 'registerUser',
                    'fields' => [
                        [
                            [
                                'type' => 'text',
                                'name' => 'name',
                                'title' => 'Name',
                                'required' => true,
                                'autofocus' => true,
                                'autocomplete' => true,
                            ],
                            [
                                'type' => 'email',
                                'name' => 'email',
                                'title' => 'Email',
                                'required' => true,
                                'autocomplete' => true,
                            ],
                            [
                                'type' => 'password',
                                'name' => 'password',
                                'title' => 'Password',
                                'required' => true,
                            ],
                            [
                                'type' => 'password',
                                'id' => 'password_confirmation',
                                'name' => 'password_confirmation',
                                'title' => 'Confirm Password',
                                'required' => true,
                            ],
                            [
                                'type' => 'links',
                                'values' => [
                                    [
                                        'title' => 'Already registered?',
                                        'route' => 'login',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'routes' => [
                'registerUser' => [
                    'route' => route('register'),
                    'method' => 'post',
                    'buttonTitle' => 'Register',
                    'buttonClass' => 'justify-end',
                    'reset' => true,
                    'fieldsToReset' => ['password', 'password_confirmation'],
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->json([
            'redirect' => true,
            'url' => redirect()->intended(RouteServiceProvider::HOME)->getTargetUrl(),
        ]);
    }
}
