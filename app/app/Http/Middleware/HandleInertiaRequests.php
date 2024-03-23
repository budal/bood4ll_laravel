<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appEnv' => config('app.env'),
            'appName' => config('app.name'),
            'appNavMenu' => app(\App\Services\Menu\Menu::class)->menu([
                'dashboard.index',
                [
                    'route' => 'apps',
                    'title' => 'Apps',
                    'icon' => 'apps',
                ],
                [
                    'route' => 'analytics',
                    'title' => 'Analytics',
                    'icon' => 'analytics',
                ],
                'help.index'
            ]),
            'appUserMenu' => app(\App\Services\Menu\Menu::class)->menu([
                'profile.edit',
                'system.settings.index',
                'logout',
            ]),
            'auth' => [
                'user' => $request->user(),
                'previousUser' => $request->session()->has('previousUser') ? true : false,
            ],
            'status' => session('status'),
            'ziggy' => fn () => [
                ...(new Ziggy())->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
