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
    public function index(Request $request): Response
    {
        $titles = [
            [
                'type' => 'composite',
                'title' => 'User',
                'field' => 'name',
                'fields' => ['name', 'email']
            ],
        ];

        $routes = [
            'createRoute' => "apps.abilities.create",
            'editRoute' => "apps.abilities.edit",
            'destroyRoute' => "apps.abilities.destroy",
            'restoreRoute' => "apps.abilities.restore",
        ];

        $menu = [
            [
                'icon' => "PlusIcon",
                'title' => "Add ability",
                'route' => "apps.abilities.create"
            ],            
            [
                'icon' => "ListBulletIcon",
                'title' => "Show all roles",
                'route' => "apps.roles"
            ],            
        ];

        return Inertia::render('Default/Index', [
            'title' => "Abilities management",
            'subtitle' => "Set abilities to access specifics resources.",
            'softDelete' => Role::hasGlobalScope('Illuminate\Database\Eloquent\SoftDeletingScope'),
            'routes' => $routes,
            'filters' => $request->all('search', 'sorted', 'trashed'),
            'titles' => $titles,
            'menu' => $menu,
            'items' => Role::filter($request->all('search', 'sorted', 'trashed'))
                ->sort($request->sorted ?? "name")
                ->paginate(20)
                ->onEachSide(2)
                ->appends($request->all('search', 'sorted', 'trashed'))
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
            // $title = $id;
            $title = $id . " (" . end($actionSegments) . ")";

            return compact('id', 'title');
        })->values()->toArray();

        usort($menu, function($a, $b) {
            return $a['title'] <=> $b['title'];
        });
        
        return [
            [
                'title' => "Abilities management",
                'subtitle' => "Ability name and application",
                'cols' => 2,
                'fields' => [
                    [
                        [
                            'type' => "input",
                            'name' => "name",
                            'title' => "Name",
                        ],
                        [
                            'type' => "select",
                            'name' => "ability",
                            'title' => "Ability",
                            'content' => $menu,
                        ],
                    ],
                ]
            ]
        ];
    }

    public function create(User $user)
    {
        $collection = collect(DB::getSchemaBuilder()->getColumnListing($user->getTable()));
        $keyed = $collection->mapWithKeys(function ($value, $key) { return [$value => '']; });
         
        return Inertia::render('Default/Create', [
            'body' => $this->__form(),
            'data' => $keyed->all()
        ]);
    }

    public function store(ProfileUpdateRequest $request): RedirectResponse
    {
        dd($request);
        // $request->user()->fill($request->validated());

        // if ($request->user()->isDirty('email')) {
        //     $request->user()->email_verified_at = null;
        // }

        // $request->user()->save();

        return Redirect::route('apps.roles')->with('status', 'User created.');
    }
    
    /**
     * Display the user's profile form.
     */
    public function edit(User $user): Response
    {
        // dd(DB::getSchemaBuilder()->getColumnListing('users'), $user->getTable(), $user->getFillable(), $user);
        
        return Inertia::render('Default/Edit', [
            'body' => $this->__form(),
            'data' => collect($user)->all()
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

    public function restore(User $user)
    {
        $user->restore();

        return Redirect::back()->with('status', 'User restored.');
    }
}
