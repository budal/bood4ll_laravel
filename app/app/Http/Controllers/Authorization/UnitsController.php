<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\Unit;
use App\Models\UserUnit;

use App\Http\Requests\RolesRequest;

class UnitsController extends Controller
{
    public function index(Request $request): Response
    {
        $items = Unit::filter($request->all('search', 'sorted', 'trashed'))
            ->addSelect([
                'subunits' => Unit::selectRaw('COUNT(*)')
                    ->whereColumn('id', 'units.parent_id')
                    ->take(1),
            ])
            ->when(!$request->search, function ($query) {
                $query->where("parent_id", "0");
            })
            ->with('parentRecursive')
            ->with('childrenRecursive')
            ->sort($request->sorted ?? "name")
            ->paginate(20)
            ->onEachSide(2)
            ->appends($request->all('search', 'sorted', 'trashed'));

        return Inertia::render('Default/Index', [
            'title' => "Units management",
            'subtitle' => "Manage the units users are classified in.",
            'softDelete' => Unit::hasGlobalScope('Illuminate\Database\Eloquent\SoftDeletingScope'),
            'routes' => [
                'editRoute' => "apps.units.edit",
                'destroyRoute' => "apps.units.destroy",
                'restoreRoute' => "apps.units.restore",
            ],
            'menu' => [
                [
                    'icon' => "mdi:plus",
                    'title' => "Unit creation",
                    'route' => "apps.units.create"
                ],
            ],
            'filters' => $request->all('search', 'sorted', 'trashed'),
            'titles' => [
                [
                    'type' => 'composite',
                    'title' => 'Name',
                    'field' => 'name',
                    'fields' => ['name', 'childrenRecursive'],
                ],
                [
                    'type' => 'simple',
                    'title' => 'Subunits',
                    'field' => 'subunits',
                ],
            ],
            'items' => $items
        ]);
    }
    
    public function __form()
    {
        $abilities = Ability::sort("name")->get()->map(function ($ability) {
            $id = $ability['id'];
            $title = $ability['name'];

            return compact('id', 'title');
        })->toArray();

        return [
            [
                'id' => "role",
                'title' => "Roles management",
                'subtitle' => "Role name and abilities",
                'cols' => 2,
                'fields' => [
                    [
                        [
                            'type' => "input",
                            'name' => "name",
                            'title' => "Name",
                            'required' => true,
                        ],
                        [
                            'type' => "input",
                            'name' => "description",
                            'title' => "Description",
                            'required' => true,
                        ],
                        [
                            'type' => "select",
                            'name' => "abilities",
                            'title' => "Abilities",
                            'span' => 2,
                            'content' => $abilities,
                            'required' => true,
                            'multiple' => true,
                        ],
                    ],
                ]
            ]
        ];
    }

    public function create()
    {
        return Inertia::render('Default/Create', [
            'form' => $this->__form(),
            'routes' => [
                'role' => [
                    'route' => route('apps.roles.store'),
                    'method' => 'post'
                ],
            ],
        ]);
    }

    public function store(RolesRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $role = Role::firstOrCreate([
                'name' => $request->name,
                'description' => $request->description,
            ]);
        } catch(\Exception $e) {
            DB::rollback();
            return Redirect::back()->with('status', "Error when inserting a new role.");
        }
        
        try {
            foreach($request->abilities as $ability_id) {
                $abilities_role = AbilityRole::create([
                    'role_id' => $role->id,
                    'ability_id' => $ability_id,
                ]);
            }
        } catch(\Exception $e) {
            DB::rollback();
            return Redirect::back()->with('status', "Error when linking abilities to the role.");
        }

        DB::commit();

        return Redirect::route('apps.roles.index')->with('status', 'Role created.');
    }
    
    public function edit(Role $role): Response
    {
        $role['abilities'] = $role->listAbilities()
            ->get()
            ->map
            ->only('ability_id')
            ->pluck('ability_id');
        
        return Inertia::render('Default/Edit', [
            'form' => $this->__form(),
            'routes' => [
                'role' => [
                    'route' => route('apps.roles.edit', $role->id),
                    'method' => 'patch'
                ],
            ],
            'data' => $role
        ]);
    }

    public function update(Role $role, Request $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            Role::where('id', $role->id)
                ->update([
                    'name' => $request->name,
                    'description' => $request->description,
                ]);
        } catch(\Exception $e) {
            DB::rollback();
            return Redirect::back()->with('status', "Error when editing the role.");
        }
        
        try {
            $abilities_role_saved = AbilityRole::where('role_id', $role->id)
                ->get()
                ->map
                ->only('id', 'ability_id')
                ->pluck('ability_id', 'id');

            $abilities_role_to_delete = AbilityRole::where('role_id', $role->id)
                ->whereNotIn('ability_id', $request->abilities)
                ->get()
                ->map
                ->only('id', 'ability_id')
                ->pluck('ability_id', 'id')
                ->toArray();

            $abilities_role_mainteined = $abilities_role_saved->diff($abilities_role_to_delete);

            $abilities_role_deleted = AbilityRole::where('role_id', $role->id)
                ->whereIn('ability_id', $abilities_role_to_delete)
                ->delete();
                
            $abilities_role_to_insert = collect($request->abilities)->diff($abilities_role_mainteined);

            foreach($abilities_role_to_insert as $ability_id) {
                $ability_role = AbilityRole::create([
                    'role_id' => $role->id,
                    'ability_id' => $ability_id,
                ]);
            }
        } catch(\Exception $e) {
            DB::rollback();
            dd($e);
            return Redirect::back()->with('status', "Error when linking abilities to the role.");
        }

        DB::commit();

        return Redirect::route('apps.roles.index')->with('status', 'Role edited.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $items = $request->all();

        try {
            $usersToDelete = Role::whereIn('id', $items['ids'])->delete();
        } catch (Throwable $e) {
            report($e);
     
            return false;
        }
        
        return back()->with('status', 'Users removed succesfully!');
    }

    public function restore(Role $role)
    {
        $role->restore();

        return Redirect::back()->with('status', 'Role restored.');
    }
}
