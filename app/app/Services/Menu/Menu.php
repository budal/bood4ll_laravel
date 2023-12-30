<?php

namespace App\Services\Menu;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

use App\Models\User;

class Menu
{
    private $user;

    private $abilities;

    public function __construct(?User $user)
    {
        $this->user = $user;

        if ($user) {
            $this->abilities = $this->user->getAllAbilities()
                ->where('abilities.name', 'like', '%index')
                ->get()
                ->pluck('ability');
        }
    }

    public function getAllMenuRoutes()
    {
        return collect(Route::getRoutes()->getRoutesByName())->filter(function ($item, $route) {
            return Str::contains($route, 'apps') && Str::contains($route, 'index')
                || Str::contains($route, 'reports') && Str::contains($route, 'index')
                || collect([
                    'dashboard',
                    'help',
                    'profile.edit',
                    'settings',
                    'messages',
                    'schedule',
                ])->contains($route);
        });
    }

    public function makeTree()
    {
        $routes = $this->getAllMenuRoutes();

        return [
            [
                'title' => $routes['dashboard']->defaults['title'],
                'description' => $routes['dashboard']->defaults['description'],
                'icon' => isset($routes['dashboard']->defaults['icon']) ? $routes['dashboard']->defaults['icon'] : null,
                'route' => $routes['dashboard']->action['as'],
                'class' => "hidden sm:flex",
            ],
            [
                'title' => "Apps",
                'icon' => "mdi:apps-box",
                'route' => 'apps',
                'class' => "hidden sm:flex",
                'links' => $routes->filter(function ($item, $route) {
                    if ($this->user->isSuperAdmin()) {
                        return Str::contains($route, 'apps');
                    } else {
                        return Str::contains($route, 'apps') && collect($this->abilities)->contains($route);
                    }
                })->map(function ($item) {
                    return [
                        'title' => $item->defaults['title'],
                        'description' => $item->defaults['description'],
                        'icon' => isset($item->defaults['icon']) ? $item->defaults['icon'] : null,
                        'route' => $item->action['as'],
                        'class' => "hidden sm:flex",
                    ];
                }),
            ],
            [
                'title' => "Reports",
                'icon' => "mdi:chart-areaspline",
                'route' => 'reports',
                'class' => "hidden sm:flex",
                'links' => $routes->filter(function ($item, $route) {
                    if ($this->user->isSuperAdmin()) {
                        return Str::contains($route, 'reports');
                    } else {
                        return Str::contains($route, 'reports') && collect($this->abilities)->contains($route);
                    }
                })->map(function ($item) {
                    return [
                        'title' => $item->action['controller'],
                        'description' => "",
                        'icon' => "mdi:chart-areaspline",
                        'route' => $item->action['as'],
                        'class' => "hidden sm:flex",
                    ];
                }),
            ],
            [
                'title' => $routes['help']->defaults['title'],
                'description' => $routes['help']->defaults['description'],
                'icon' => isset($routes['help']->defaults['icon']) ? $routes['help']->defaults['icon'] : null,
                'route' => $routes['help']->action['as'],
                'class' => "hidden sm:flex",
            ],
            [
                'title' => "-",
                'class' => "hidden sm:flex",
                'showInNav' => false,
            ],
            [
                'title' => $routes['profile.edit']->defaults['title'],
                'description' => $routes['profile.edit']->defaults['description'],
                'icon' => isset($routes['profile.edit']->defaults['icon']) ? $routes['profile.edit']->defaults['icon'] : null,
                'route' => $routes['profile.edit']->action['as'],
                'class' => "hidden sm:flex",
                'showInNav' => false,
            ],
            [
                'title' => $routes['settings']->defaults['title'],
                'description' => $routes['settings']->defaults['description'],
                'icon' => isset($routes['settings']->defaults['icon']) ? $routes['settings']->defaults['icon'] : null,
                'route' => $routes['settings']->action['as'],
                'class' => "hidden sm:flex",
                'showInNav' => false,
            ],
            [
                'title' => $routes['messages']->defaults['title'],
                'description' => $routes['messages']->defaults['description'],
                'icon' => isset($routes['messages']->defaults['icon']) ? $routes['messages']->defaults['icon'] : null,
                'route' => $routes['messages']->action['as'],
                'class' => "hidden sm:flex",
                'showInNav' => false,
            ],
            [
                'title' => $routes['schedule']->defaults['title'],
                'description' => $routes['schedule']->defaults['description'],
                'icon' => isset($routes['schedule']->defaults['icon']) ? $routes['schedule']->defaults['icon'] : null,
                'route' => $routes['schedule']->action['as'],
                'class' => "hidden sm:flex",
                'showInNav' => false,
            ],
        ];
    }

    public function menu()
    {
        return collect($this->makeTree());
    }
}
