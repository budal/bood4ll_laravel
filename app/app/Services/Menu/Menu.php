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

    public function getAllMenuRoutes($source)
    {
        $searchRoutes = collect($source)->map(function ($value) {
            return is_array($value) ? $value['route'] . '.*.index' : $value;
        });

        return collect(Route::getRoutes()->getRoutesByName())->filter(function ($item, $route) use ($searchRoutes) {
            return $searchRoutes->map(function ($item) use ($route) {
                return Str::is($item, $route);
            })->contains(true);
        });
    }

    public function makeTree($source)
    {
        $routes = $this->getAllMenuRoutes($source);

        $tree = collect();

        collect($source)->map(function ($value) use ($routes, $tree) {
            return is_array($value)
                ? $tree->push(
                    [
                        'title' => $value['title'],
                        'icon' => $value['icon'],
                        'route' => $value['route'],
                        'links' => $routes->filter(function ($item, $route) use ($value) {
                            if ($this->user?->isSuperAdmin()) {
                                return Str::contains($route, $value['route']);
                            } else {
                                return Str::contains($route, $value['route']) && collect($this->abilities)->contains($route);
                            }
                        })->map(function ($item) {
                            return [
                                'title' => $item->defaults['title'],
                                'description' => $item->defaults['description'],
                                'icon' => isset($item->defaults['icon']) ? $item->defaults['icon'] : null,
                                'route' => $item->action['as'],
                            ];
                        })->values(),
                    ],
                )
                : $tree->push(
                    [
                        'title' => $routes[$value]->defaults['title'],
                        'description' => $routes[$value]->defaults['description'],
                        'icon' => isset($routes[$value]->defaults['icon']) ? $routes[$value]->defaults['icon'] : null,
                        'route' => $routes[$value]->action['as'],
                    ],
                );
        });

        return $tree;
    }

    public function menu($source)
    {
        return collect($this->makeTree($source));
    }
}
