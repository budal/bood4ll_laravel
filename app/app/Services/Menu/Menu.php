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
                'title' => "Dashboard",
                'description' => "",
                'icon' => "mdi:monitor-dashboard",
                'route' => $routes['dashboard']->action['as'],
                'class' => "sm:hidden",
            ],
            [
                'title' => "Apps",
                'description' => "",
                'icon' => "mdi:apps-box",
                'route' => 'apps',
                'class' => "sm:hidden",
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
                        'class' => "sm:hidden",
                    ];
                }),
            ],
            [
                'title' => "Reports",
                'description' => "",
                'icon' => "mdi:chart-areaspline",
                'route' => 'reports',
                'class' => "sm:hidden",
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
                        'class' => "sm:hidden",
                    ];
                }),
            ],
            [
                'title' => "Help",
                'description' => "",
                'icon' => "mdi:help-circle-outline",
                'route' => $routes['help']->action['as'],
                'class' => "sm:hidden",
            ],
            [
                'title' => "-",
                'class' => "sm:hidden",
                'showInNav' => false,
            ],
            [
                'title' => "Profile",
                'description' => "",
                'icon' => "mdi:account-circle-outline",
                'route' => $routes['profile.edit']->action['as'],
                'class' => "sm:hidden",
                'showInNav' => false,
            ],
            [
                'title' => "Settings",
                'description' => "",
                'icon' => "mdi:cog-outline",
                'route' => $routes['settings']->action['as'],
                'class' => "sm:hidden",
                'showInNav' => false,
            ],
            [
                'title' => "Messages",
                'description' => "",
                'icon' => "mdi:message-outline",
                'route' => $routes['messages']->action['as'],
                'class' => "sm:hidden",
                'showInNav' => false,
            ],
            [
                'title' => "Schedule",
                'description' => "",
                'icon' => "mdi:calendar-month-outline",
                'route' => $routes['schedule']->action['as'],
                'class' => "sm:hidden",
                'showInNav' => false,
            ],
        ];
    }

    public function menu()
    {
        return collect($this->makeTree());
    }
}
