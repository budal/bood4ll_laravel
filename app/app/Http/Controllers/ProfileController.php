<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __form(Request $request): array
    {
        return [
            [
                'id' => 'profile',
                'title' => 'Main data',
                'subtitle' => 'User account profile information.',
                'fields' => [
                    [
                        [
                            'type' => 'input',
                            'name' => 'name',
                            'title' => 'Name',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'email',
                            'title' => 'Email',
                        ],
                    ],
                    [
                        [
                            'type' => 'modal',
                            'title' => 'Your email address is unverified.',
                            'description' => "Click here to re-send the verification email.",
                            'showIf' => $request->user() instanceof MustVerifyEmail && $request->user()['email_verified_at'] == '---',
                        ],
                    ]
                ]
            ],
            [
                'id' => 'resetPassword',
                'title' => 'Update Password',
                'subtitle' => 'Ensure your account is using a long, random password to stay secure.',
                'fields' => [
                    [
                        [
                            'type' => 'password',
                            'name' => 'current_password',
                            'title' => 'Current Password',
                        ],
                        [
                            'type' => 'password',
                            'name' => 'password',
                            'title' => 'New Password',
                        ],
                        [
                            'type' => 'password',
                            'name' => 'password_confirmation',
                            'title' => 'Confirm Password',
                        ],
                    ],
                ]
            ],
        ];
    }

    public function edit(Request $request): Response
    {
        $user = $request->user();
        $user['mustVerifyEmail'] = $request->user() instanceof MustVerifyEmail;

        // return Inertia::render('Profile/Edit', [
        //     'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
        //     'status' => session('status'),
        // ]);

        return Inertia::render('Default', [
            'form' => $this->__form($request),
            'routes' => [
                'profile' => [
                    'route' => route('apps.users.store'),
                    'method' => 'post',
                ],
            ],
            'data' => $user,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
