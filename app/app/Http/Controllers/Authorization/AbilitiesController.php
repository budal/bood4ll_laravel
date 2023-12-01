<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

use App\Models\Ability;

use Throwable;

class AbilitiesController extends Controller
{
    public function index(Request $request): Response
    {
        $prefixes = ["apps", "reports"];
        
        $routes = collect(Route::getRoutes())->filter(function ($route) use ($request, $prefixes) {
            return Str::contains($route->uri, $prefixes) && (
                $request->search ? Str::contains($route->uri, $request->search) : true
            );
        });

        $abilities = Ability::filter($request->all('search', 'sorted', 'trashed'))->get();

        $activatedAbilities = collect($abilities->pluck('name')->toArray());

        $items = $routes->map(function ($route) use ($activatedAbilities) {
            $actionSegments = explode('\\', $route->action['controller']);
            $id = $route->action['as'];
            $route = $route->uri;
            $command = end($actionSegments);
            $title = $id;
            $checked = $activatedAbilities->contains($id);

            return compact('id', 'route', 'command', 'title', 'checked');
        })->values()->toArray();

        usort($items, function($a, $b) {
            return $a['title'] <=> $b['title'];
        });
        
        return Inertia::render('Default/Index', [
            'title' => "Abilities management",
            'subtitle' => "Define which abilities will be showed in the roles management.",
            'routes' => [
                'showCheckboxes' => true,
            ],
            'menu' => [
                [
                    'icon' => "mdi:check-circle-outline",
                    'title' => "Activate abilities",
                    'route' => "apps.abilities.update",
                    'method' => "post",
                    'showList' => true,
                ],            
                [
                    'icon' => "mdi:close-circle-outline",
                    'title' => "Deactivate abilities",
                    'route' => "apps.abilities.update",
                    'method' => "post",
                    'showList' => true,
                ],            
            ],
            'titles' => [
                [
                    'type' => 'composite',
                    'title' => 'Ability',
                    'field' => 'title',
                    'fields' => ['title', 'command'],
                ],
                [
                    'type' => 'toggle',
                    'title' => 'Active',
                    'field' => 'id',
                    'route' => 'apps.abilities.update',
                    'method' => 'post',
                    'color' => 'success',
                    'colorFalse' => 'danger',
                ],
            ],
            'items' => ['data' => $items]
        ]);
    }
    
    public function update($ability): RedirectResponse
    {
        $getAbility = Ability::where('name', $ability)->first();

        try {
            if ($getAbility) {
                $getAbility->delete();
                return Redirect::back()->with([
                    'status' => "The ability ':ability' is deactivated.", 
                    'statusComplements' => ['ability' => $ability]
                ]);
            } else {
                Ability::updateOrCreate(['name' => $ability]);
                return Redirect::back()->with([
                    'status' => "The ability ':ability' is activated.", 
                    'statusComplements' => ['ability' => $ability]
                ]);
            }
        } catch (Throwable $e) {
            return Redirect::back()->with('status', "Error on activate/inactivate the ability.");
        }
    }
}
