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

class DashboardController extends Controller
{
    public function __form(Request $request): array
    {
        return [
            [
                'id' => 'profile',
                'title' => 'Main data',
                'subtitle' => 'User account profile information.',
                'cols' => 2,
                'fields' => []
            ],
        ];
    }

    public function index(Request $request): Response
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
}
