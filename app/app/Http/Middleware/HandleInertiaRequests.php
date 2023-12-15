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
            'auth' => [
                'user' => $request->user(),
                'previousUser' => $request->session()->has('previousUser') ? true : false,
            ],
            'breadcrumbs' => $request->route()->breadcrumbs()->jsonSerialize(),
            'status' => session('status'),
            'toast_type' => session('toast_type'),
            'toast_title' => session('toast_title'),
            'toast_message' => session('toast_message'),
            'toast_count' => session('toast_count'),
            'toast_replacements' => session('toast_replacements'),
            'ziggy' => fn () => [
                ...(new Ziggy())->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
