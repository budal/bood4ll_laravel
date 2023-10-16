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
        
        $titles = [
            [
                'type' => 'composite',
                'title' => 'Ability',
                'field' => 'title',
                'fields' => ['title', 'command']
            ],
            [
                'type' => 'switch',
                'title' => 'Active',
                'field' => 'id',
                'route' => 'apps.abilities.update',
                'method' => 'post',
            ],
        ];

        return Inertia::render('Default/Index', [
            'title' => "Abilities management",
            'subtitle' => "Define which abilities will be showed in the roles management.",
            'softDelete' => Ability::hasGlobalScope('Illuminate\Database\Eloquent\SoftDeletingScope'),
            'routes' => [],
            'filters' => $request->all('search', 'sorted', 'trashed'),
            'titles' => $titles,
            'items' => ['data' => $items]
        ]);
    }
    
    public function update($ability): RedirectResponse
    {
        $getAbility = Ability::where('name', $ability)->first();

        try {
            if ($getAbility) {
                $getAbility->delete();
                return Redirect::back()->with('status', "The ability '$ability' is deactivated.");
            } else {
                Ability::updateOrCreate(['name' => $ability]);
                return Redirect::back()->with('status', "The ability '$ability' is activated.");
            }
        } catch (Throwable $e) {
            return Redirect::back()->with('status', "Error on update the ability.");
        }
    }
}
