<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
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
                    'dialogConfirm' => 'Register',
                    'fields' => [
                        [
                            'type' => 'input',
                            'name' => 'name',
                            'label' => 'Name',
                            'required' => true,
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
            'redirectUrl' => redirect()->intended(RouteServiceProvider::HOME)->getTargetUrl(),
        ]);
    }
}
