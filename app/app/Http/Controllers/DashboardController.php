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
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __fields(): array
    {
        return [
            [
                'type' => 'input',
                'name' => 'geo',
                'label' => 'Geographic coordinates',
                'span' => 4,
            ],
        ];
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Bood4ll', [
            // 'tabs' => false,
            'build' => [
                [
                    'label' => Route::current()->title,
                    'description' => Route::current()->description,
                    'fields' => $this->__fields(),
                ],
            ]
        ]);
    }
}
