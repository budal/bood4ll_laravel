<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Models\Ability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AbilitiesController extends Controller
{
    public function index(Request $request): Response
    {
        $prefixes = ['apps', 'reports'];

        $routes = collect(Route::getRoutes())->filter(function ($route) use ($prefixes) {
            return Str::contains($route->uri, $prefixes)
            // && ($request->abilities_search ? Str::contains($route->action['as'], $request->abilities_search) : true)
            ;
        });

        $abilitiesInDB = Ability::filter($request->all('abilities_search'))->get()->pluck('name');

        $validAbilities = $routes->map(function ($route) use ($abilitiesInDB) {
            $actionSegments = explode('\\', $route->action['controller']);
            $id = $route->action['as'];
            $route = $route->uri;
            $command = end($actionSegments);
            $title = $id;
            $checked = $abilitiesInDB->contains($id);

            return compact('id', 'route', 'command', 'title', 'checked');
        })->values();

        $invalidAbilities = $abilitiesInDB->diff(collect($validAbilities)->pluck('id'))->map(function ($zombie) {
            $id = $zombie;
            $route = $zombie;
            $title = $zombie;
            $checked = true;

            return compact('id', 'route', 'title', 'checked');
        })->values();

        $validAbilities = $validAbilities->toArray();
        $invalidAbilities = $invalidAbilities->toArray();

        usort($validAbilities, function ($a, $b) use ($request) {
            return ($request->query('validAbilities_sorted') == 'title' || !$request->query('validAbilities_sorted'))
                ? $a['title'] <=> $b['title']
                : $b['title'] <=> $a['title'];
        });

        usort($invalidAbilities, function ($a, $b) use ($request) {
            return ($request->query('invalidAbilities_sorted') == 'title' || !$request->query('invalidAbilities_sorted'))
                ? $a['title'] <=> $b['title']
                : $b['title'] <=> $a['title'];
        });

        return Inertia::render('Default', [
            'form' => [
                [
                    'id' => 'validAbilities',
                    'title' => 'Valid abilities',
                    'subtitle' => 'Define which abilities will be showed in the roles management.',
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'users',
                                'content' => [
                                    'routes' => [
                                        'showCheckboxes' => true,
                                    ],
                                    'menu' => [
                                        [
                                            'icon' => 'mdi:plus-circle-outline',
                                            'title' => 'Activate abilities',
                                            'route' => [
                                                'route' => 'apps.abilities.update',
                                                'attributes' => 'on',
                                            ],
                                            'method' => 'post',
                                            'list' => 'checkboxes',
                                            'listCondition' => false,
                                            'modalTitle' => 'Are you sure you want to activate the selected item?|Are you sure you want to activate the selected items?',
                                            'modalSubTitle' => 'The selected item will be activated. Do you want to continue?|The selected items will be activated. Do you want to continue?',
                                            'buttonTitle' => 'Activate',
                                            'buttonIcon' => 'mdi:plus-circle-outline',
                                            'buttonColor' => 'success',
                                        ],
                                        [
                                            'icon' => 'mdi:minus-circle-outline',
                                            'title' => 'Deactivate abilities',
                                            'route' => [
                                                'route' => 'apps.abilities.update',
                                                'attributes' => 'off',
                                            ],
                                            'method' => 'post',
                                            'list' => 'checkboxes',
                                            'listCondition' => true,
                                            'modalTitle' => 'Are you sure you want to deactivate the selected item?|Are you sure you want to deactivate the selected items?',
                                            'modalSubTitle' => 'The selected item will be deactivated. Do you want to continue?|The selected items will be deactivated. Do you want to continue?',
                                            'buttonTitle' => 'Deactivate',
                                            'buttonIcon' => 'mdi:minus-circle-outline',
                                            'buttonColor' => 'danger',
                                        ],
                                    ],
                                    'titles' => [
                                        [
                                            'type' => 'composite',
                                            'title' => 'Ability',
                                            'field' => 'title',
                                            'values' => [
                                                [
                                                    'field' => 'title',
                                                ],
                                                [
                                                    'field' => 'command',
                                                    'class' => 'text-xs',
                                                ],
                                            ],
                                        ],
                                        [
                                            'type' => 'toggle',
                                            'title' => 'Active',
                                            'field' => 'checked',
                                            'disableSort' => true,
                                            'route' => [
                                                'route' => 'apps.abilities.update',
                                                'attributes' => 'toggle',
                                            ],
                                            'method' => 'post',
                                            'colorOn' => 'success',
                                            'colorOff' => 'danger',
                                        ],
                                    ],
                                    'items' => ['data' => $validAbilities],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'invalidAbilities',
                    'title' => 'Invalid abilities',
                    'subtitle' => 'This abilities does not have a specific route to refer to. Please delete them.',
                    'condition' => count($invalidAbilities) > 0,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'users',
                                'content' => [
                                    'routes' => [
                                        'showCheckboxes' => true,
                                    ],
                                    'menu' => [
                                        [
                                            'icon' => 'mdi:minus-circle-outline',
                                            'title' => 'Erase',
                                            'route' => [
                                                'route' => 'apps.abilities.update',
                                                'attributes' => 'off',
                                            ],
                                            'method' => 'post',
                                            'list' => 'checkboxes',
                                            'listCondition' => true,
                                            'modalTitle' => 'Are you sure you want to erase the selected item?|Are you sure you want to erase the selected items?',
                                            'modalSubTitle' => "The selected item will be erased from the database. This action can't be undone. Do you want to continue?|The selected items will be erased from the database. This action can't be undone. Do you want to continue?",
                                            'buttonTitle' => 'Erase',
                                            'buttonIcon' => 'mdi:minus-circle-outline',
                                            'buttonColor' => 'warning',
                                        ],
                                    ],
                                    'titles' => [
                                        [
                                            'type' => 'text',
                                            'title' => 'Ability',
                                            'field' => 'title',
                                        ],
                                        [
                                            'type' => 'toggle',
                                            'title' => 'Active',
                                            'field' => 'checked',
                                            'disableSort' => true,
                                            'route' => [
                                                'route' => 'apps.abilities.update',
                                                'attributes' => 'off',
                                            ],
                                            'method' => 'post',
                                            'colorOn' => 'warning',
                                            'colorOff' => 'warning',
                                            'icon' => 'mdi:exclamation-thick',
                                            'rotate' => false,
                                        ],
                                    ],
                                    'items' => ['data' => $invalidAbilities],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function update(Request $request, $mode): RedirectResponse
    {
        try {
            $ability = Ability::sync($mode, $request->list);

            if ($mode == 'toggle') {
                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => $ability->action == 'delete'
                        ? "The ability ':ability' was deactivated."
                        : "The ability ':ability' was activated.",
                    'toast_replacements' => ['ability' => $ability->name],
                ]);
            } elseif ($mode == 'on') {
                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => '{0} Nothing to activate.|[1] Item activated successfully.|[2,*] :total items successfully activated.',
                    'toast_count' => $ability->total,
                    'toast_replacements' => ['total' => $ability->total],
                ]);
            } elseif ($mode == 'off') {
                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => '{0} Nothing to deactivate.|[1] Item deactivated successfully.|[2,*] :total items successfully deactivated.',
                    'toast_count' => $ability->total,
                    'toast_replacements' => ['total' => $ability->total],
                ]);
            }
        } catch (\Throwable $e) {
            report($e);

            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on edit selected item.|Error on edit selected items.',
                'toast_count' => count($request->list),
            ]);
        }
    }
}
