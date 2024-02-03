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
                'cols' => 2,
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
                ]
            ],
            [
                'id' => 'resetPassword',
                'title' => 'Update Password',
                'subtitle' => 'Ensure your account is using a long, random password to stay secure.',
                'cols' => 3,
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
            [
                'id' => 'verifyEmail',
                'title' => 'Email Verification',
                'subtitle' => 'Your email address is unverified.',
                'showIf' => $request->user() instanceof MustVerifyEmail && !$request->user()['email_verified_at'],
                'fields' => [
                    [
                        [
                            'type' => 'button',
                            'name' => 'sendReverificationEmail',
                            'route' => 'verification.send',
                            'method' => 'post',
                            'title' => 'Click here to re-send the verification email',
                            'preserveScroll' => true,
                        ],
                    ],
                ]
            ],
            [
                'id' => 'deleteAccount',
                'title' => 'Delete Account',
                'subtitle' => 'Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.',
                'fields' => [
                    [
                        [
                            'type' => 'button',
                            'name' => 'deleteAccountConfirmation',
                            'route' => 'profile.destroy',
                            'method' => 'delete',
                            'color' => 'danger',
                            'title' => 'Click here to delete your account',
                            'preserveScroll' > true,
                            'modal' => [
                                'confirm' => true,
                                'theme' => "danger",
                                'title' => "Are you sure you want to delete your account?",
                                'subTitle' => "Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.",
                                'buttonTitle' => "Delete Account",
                                'buttonTheme' => "danger",
                            ],
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
            'tabs' => false,
            'routes' => [
                'profile' => [
                    'route' => route('profile.update'),
                    'method' => 'patch',
                ],
                'resetPassword' => [
                    'route' => route('password.update'),
                    'method' => 'put',
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
        dd($request);

        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        // Hash::make($validated['password']

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
