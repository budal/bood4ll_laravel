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
        
        $abilities = Ability::filter($request->all('search', 'sorted', 'trashed'))->get();
        
        $routes = collect(Route::getRoutes())->filter(function ($route) use ($request, $prefixes) {
            return Str::contains($route->uri, $prefixes) 
            && ($request->search ? Str::contains($route->uri, $request->search) : true);
        });

        $activatedAbilities = collect($abilities->pluck('name')->toArray());

        $items = $routes->map(function ($route) use ($activatedAbilities) {
            $actionSegments = explode('\\', $route->action['controller']);
            $id = $route->action['as'];
            $route = $route->uri;
            $command = end($actionSegments);
            $title = $id;
            $checked = $activatedAbilities->contains($id);

            return compact('id', 'route', 'command', 'title', 'checked');
        })->values();

        $zombies = $abilities->pluck('name')->diff(collect($items)->pluck('id'));

        $zombies->map(function ($zombie) use ($items) {
            $id = $zombie;
            $route = $zombie;
            $command = "This abilitation does not have a specific route to refer to. Please delete it.";
            $title = $zombie;
            $checked = null;
            $isUndefined = true;

            $items->push(compact('id', 'route', 'command', 'title', 'checked', 'isUndefined'));
        });

        $items = $items->toArray();

        usort($items, function($a, $b) use ($request) {
            return ($request->query('sorted') == "title" || !$request->query('sorted')) 
                ? $a['title'] <=> $b['title']
                : $b['title'] <=> $a['title'];
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
                    'listCondition' => false,
                    'modalTitle' => "Are you sure you want to activate the selected item?|Are you sure you want to activate the selected items?",
                    'modalSubTitle' => "The selected item will be activated. Do you want to continue?|The selected items will be activated. Do you want to continue?",
                    'buttonTitle' => "Activate",
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
                    'listCondition' => true,
                    'modalTitle' => "Are you sure you want to deactivate the selected item?|Are you sure you want to deactivate the selected items?",
                    'modalSubTitle' => "The selected item will be deactivated. Do you want to continue?|The selected items will be deactivated. Do you want to continue?",
                    'buttonTitle' => "Deactivate",
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
                    'field' => 'checked',
                    'disableSort' => true,
                    'route' => [
                        'route' => "apps.abilities.update",
                        'attributes' => "toggle",
                    ],
                    'method' => 'post',
                    'colorOn' => 'success',
                    'colorOff' => 'danger',
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
                    'toast_type' => 'success',
                    'toast_message' => $ability->action == 'delete' 
                        ? "The ability ':ability' was deactivated."
                        : "The ability ':ability' was activated." ,
                    'toast_replacements' => ['ability' => $ability->name]
                ]);
            } elseif ($mode == "on") {
                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => "{0} Nothing to activate.|[1] Item activated successfully.|[2,*] :total items successfully activated.",
                    'toast_count' => $ability->total,
                    'toast_replacements' => ['total' => $ability->total]
                ]);
            } elseif ($mode == "off") {
                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => "{0} Nothing to deactivate.|[1] Item deactivated successfully.|[2,*] :total items successfully deactivated.",
                    'toast_count' => $ability->total,
                    'toast_replacements' => ['total' => $ability->total]
                ]);
            }
        } catch (Throwable $e) {
            report($e);

            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => "Error on edit selected item.|Error on edit selected items.",
                'toast_count' => count($request->list),
            ]);
        }
    }
}
