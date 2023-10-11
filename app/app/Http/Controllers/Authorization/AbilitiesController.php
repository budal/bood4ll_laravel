<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Role;
use App\Models\Ability;

class AbilitiesController extends Controller
{
    /**
     * Display the users list.
     */
    // public function index(Request $request): Response
    // {
    //     $titles = [
    //         [
    //             'type' => 'simple',
    //             'title' => 'Ability',
    //             'field' => 'name',
    //         ],
    //     ];

    //     $routes = [
    //         'createRoute' => "apps.abilities.create",
    //         'editRoute' => "apps.abilities.edit",
    //         'destroyRoute' => "apps.abilities.destroy",
    //         'restoreRoute' => "apps.abilities.restore",
    //     ];

    //     $menu = [
    //         [
    //             'icon' => "PlusIcon",
    //             'title' => "Add ability",
    //             'route' => "apps.abilities.create"
    //         ],            
    //         [
    //             'icon' => "ListBulletIcon",
    //             'title' => "Show all roles",
    //             'route' => "apps.roles"
    //         ],            
    //     ];

    //     return Inertia::render('Default/Index', [
    //         'title' => "Abilities management",
    //         'subtitle' => "Set abilities to access specifics resources.",
    //         'softDelete' => Ability::hasGlobalScope('Illuminate\Database\Eloquent\SoftDeletingScope'),
    //         'routes' => $routes,
    //         'filters' => $request->all('search', 'sorted', 'trashed'),
    //         'titles' => $titles,
    //         'menu' => $menu,
    //         'items' => Ability::filter($request->all('search', 'sorted', 'trashed'))
    //             ->sort($request->sorted ?? "name")
    //             ->paginate(20)
    //             ->onEachSide(2)
    //             ->appends($request->all('search', 'sorted', 'trashed'))
    //     ]);
    // }

    public function index(Request $request): Response
    {
        $prefix = "apps";
        
        $routes = collect(Route::getRoutes())->filter(function ($route) use ($prefix) {
            return Str::startsWith($route->uri, $prefix);
        });
        
        $items = $routes->map(function ($route) use ($prefix) {
            $actionSegments = explode('\\', $route->action['controller']);
            $id = $route->action['as'];
            $title = $id . " (" . end($actionSegments) . ")";

            return compact('id', 'title');
        })->values()->toArray();

        usort($items, function($a, $b) {
            return $a['title'] <=> $b['title'];
        });
        
        $titles = [
            [
                'type' => 'simple',
                'title' => 'Ability',
                'field' => 'title',
            ],
            [
                'type' => 'switch',
                'title' => 'Active',
                'field' => 'id',
            ],
        ];

        return Inertia::render('Default/Index', [
            'title' => "Abilities management",
            'subtitle' => "Set abilities to access specifics resources.",
            'softDelete' => Ability::hasGlobalScope('Illuminate\Database\Eloquent\SoftDeletingScope'),
            'routes' => [],
            'filters' => $request->all('search', 'sorted', 'trashed'),
            'titles' => $titles,
            'items' => ['data' => $items]
        ]);
    }
    
    public function __form()
    {
        $prefix = "apps";
        
        $routes = collect(Route::getRoutes())->filter(function ($route) use ($prefix) {
            return Str::startsWith($route->uri, $prefix);
        });
        
        $menu = $routes->map(function ($route) use ($prefix) {
            $actionSegments = explode('\\', $route->action['controller']);
            $id = $route->action['as'];
            $title = $id . " (" . end($actionSegments) . ")";

            return compact('id', 'title');
        })->values()->toArray();

        usort($menu, function($a, $b) {
            return $a['title'] <=> $b['title'];
        });
        
        return [
            [
                'title' => "Abilities management",
                'subtitle' => "Select active abilities in the system.",
                'fields' => [
                    [
                        [
                            'type' => "select",
                            'name' => "name",
                            'title' => "Name",
                            'multiple' => true,
                            'content' => $menu,
                        ],
                    ],
                ]
            ]
        ];
    }

    public function create(Ability $ability)
    {
        $collection = collect(DB::getSchemaBuilder()->getColumnListing($ability->getTable()));
        $keyed = $collection->mapWithKeys(function ($value, $key) { return [$value => '']; });
         
        return Inertia::render('Default/Create', [
            'body' => $this->__form(),
            'data' => $keyed->all()
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        dd($request);
        // $request->user()->fill($request->validated());

        // if ($request->user()->isDirty('email')) {
        //     $request->user()->email_verified_at = null;
        // }

        // $request->user()->save();

        return Redirect::route('apps.abilities')->with('status', 'Ability created.');
    }
    
    /**
     * Display the user's profile form.
     */
    public function edit(Ability $ability): Response
    {
        // dd(DB::getSchemaBuilder()->getColumnListing('users'), $ability->getTable(), $ability->getFillable(), $ability);
        
        return Inertia::render('Default/Edit', [
            'body' => $this->__form(),
            'data' => collect($ability)->all()
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // dd($request);
        
        // $request->user()->fill($request->validated());

        // if ($request->user()->isDirty('email')) {
        //     $request->user()->email_verified_at = null;
        // }

        // $request->user()->save();

        // return Redirect::route('profile.edit');
        return Redirect::back()->with('status', 'User edited.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $items = $request->all();

        try {
            $usersToDelete = User::whereIn('id', $items['ids'])->delete();
        } catch (Throwable $e) {
            report($e);
     
            return false;
        }
        
        return back()->with('status', 'Users removed succesfully!');
    }

    public function restore(Ability $ability)
    {
        $ability->restore();

        return Redirect::back()->with('status', 'User restored.');
    }
}
