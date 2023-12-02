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
                    'icon' => "mdi:plus-circle-outline",
                    'title' => "Activate abilities",
                    'route' => [
                        'route' => "apps.abilities.update",
                        'attributes' => "on"
                    ],
                    'method' => "post",
                    'list' => 'checkboxes',
                    'modalTitle' => "Are you sure you want to activate the selected items?",
                    'modalSubTitle' => "The selected items will be activated. Do you want to continue?",
                    'buttonTitle' => "Activate selected",
                    'buttonIcon' => "mdi:plus-circle-outline",
                    'buttonColor' => "success",
                ],
                [
                    'icon' => "mdi:minus-circle-outline",
                    'title' => "Deactivate abilities",
                    'route' => [
                        'route' => "apps.abilities.update",
                        'attributes' => "off"
                    ],
                    'method' => "post",
                    'list' => 'checkboxes',
                    'modalTitle' => "Are you sure you want to erase the selected items?",
                    'modalSubTitle' => "The selected item will be erased from the database. This action can't be undone. Do you want to continue?",
                    'buttonTitle' => "Erase selected",
                    'buttonIcon' => "mdi:minus-circle-outline",
                    'buttonColor' => "danger",
            
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
                    'route' => [
                        'route' => "apps.abilities.update",
                        'attributes' => "toggle",
                    ],
                    'method' => 'post',
                    'color' => 'success',
                    'colorFalse' => 'danger',
                ],
            ],
            'items' => ['data' => $items]
        ]);
    }
    
    public function update(Request $request, $mode): RedirectResponse
    {
        try {
            $ability = Ability::sync($mode, $request->list);

            if ($mode == "toggle") {
                return Redirect::back()->with([
                    'toast_message' => $ability->action == 'delete' 
                        ? "The ability ':ability' was deactivated."
                        : "The ability ':ability' was activated." ,
                    'toast_replacements' => ['ability' => $ability->name]
                ]);
            } elseif ($mode == "on") {
                return Redirect::route('apps.abilities.index')->with([
                    'toast_message' => "A total of ':total' abilities were activated.",
                    'toast_replacements' => ['total' => $ability->total]
                ]);
            } elseif ($mode == "off") {
                return Redirect::route('apps.abilities.index')->with([
                    'toast_message' => "A total of ':total' abilities were deactivated.",
                    'toast_replacements' => ['total' => $ability->total]
                ]);
            }
        } catch (Throwable $e) {
            return Redirect::back()->with('status', "Error on activate/inactivate the ability.");
        }
    }
}
