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

use App\Http\Requests\RolesRequest;

class UnitsController extends Controller
{
    public function index(Request $request): Response
    {
        $items = Unit::filter($request->all('search', 'sorted', 'trashed'))
            ->when(!$request->search, function ($query) {
                $query->where("parent_id", "0");
            })
            ->with('parentRecursive')
            ->withCount('children')
            ->sort($request->sorted ?? "name")
            ->paginate(20)
            ->onEachSide(2)
            ->through(function($product){
                $product->parents = $product->getParentsNames();
                return $product;
            })
            ->appends($request->all('search', 'sorted', 'trashed'));

        $parents = Unit::where("id", 90)
            ->first()
            ->getParentsNames();

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
                    'type' => $request->search ? 'composite' : 'simple',
                    'title' => 'Name',
                    'field' => 'name',
                    'fields' => ['name', 'parents'],
                ],
                [
                    'type' => 'simple',
                    'title' => 'Subunits',
                    'field' => 'children_count',
                ],
            ],
            'items' => $items
        ]);
    }
    
    public function __form(Request $request, Unit $unit)
    {
        $units2 = Unit::sort("name")->get()->map(function ($unit) {
            $id = $unit['id'];
            $title = $unit['name'];

            return compact('id', 'title');
        });

        $units = Unit::sort("name")
            ->where('parent_id', 0)
            ->with('childrenRecursive')
            ->get();

            // dd($units);

        $items = Unit::filter($request->all('search', 'sorted', 'trashed'))
            ->sort($request->sorted ?? "name")
            ->where('parent_id', $unit->id)
            ->withCount('children')
            ->paginate(20)
            ->onEachSide(2)
            ->appends($request->all('search', 'sorted', 'trashed'));

            return [
                [
                    'id' => "role",
                    'title' => "Unit management",
                    'subtitle' => "Manage unit's info.",
                    'cols' => 4,
                    'fields' => [
                        [
                            [
                                'type' => "input",
                                'name' => "name",
                                'title' => "Name",
                                'span' => 2,
                                'required' => true,
                            ],
                            [
                                'type' => "input",
                                'name' => "name",
                                'title' => "Short name",
                                'required' => true,
                            ],
                            [
                                'type' => "date",
                                'name' => "founded",
                                'title' => "Founded",
                                'required' => true,
                            ],
                            [
                                'type' => "select",
                                'name' => "parent_id",
                                'title' => "Unidade pai",
                                'span' => 2,
                                'content' => $units,
                                'required' => true,
                            ],
                            [
                                'type' => "switch",
                                'name' => "temporary",
                                'title' => "Temporary",
                            ],
                            [
                                'type' => "date",
                                'name' => "expires",
                                'title' => "Expires",
                            ],
                            [
                                'type' => "input",
                                'name' => "cellphone",
                                'title' => "Cell Phone",
                            ],
                            [
                                'type' => "input",
                                'name' => "landline",
                                'title' => "Land Line",
                            ],
                            [
                                'type' => "input",
                                'name' => "email",
                                'title' => "E-mail",
                                'span' => 2,
                            ],
                            [
                                'type' => "input",
                                'name' => "country",
                                'title' => "Country",
                            ],
                            [
                                'type' => "input",
                                'name' => "state",
                                'title' => "State",
                            ],
                            [
                                'type' => "input",
                                'name' => "city",
                                'title' => "City",
                            ],
                            [
                                'type' => "input",
                                'name' => "postcode",
                                'title' => "Post Code",
                            ],
                            [
                                'type' => "input",
                                'name' => "address",
                                'title' => "Address",
                                'span' => 3,
                            ],
                            [
                                'type' => "input",
                                'name' => "complement",
                                'title' => "Complement",
                            ],
                            [
                                'type' => "input",
                                'name' => "geo",
                                'title' => "Coordenadas",
                                'span' => 4,
                            ],
                         ],
                    ],
                ],
                [
                    'title' => "Subunits management",
                    'subtitle' => "Manage unit's subunits",
                    'condition' => $unit->id <> null,
                    'cols' => 2,
                    'fields' => [
                        [
                            [
                                'type' => "table",
                                'name' => "users",
                                'title' => "Subunits",
                                'span' => 2,
                                'content' => [
                                    'softDelete' => Unit::hasGlobalScope('Illuminate\Database\Eloquent\SoftDeletingScope'),
                                    'routes' => [
                                        'editRoute' => "apps.units.edit",
                                        'destroyRoute' => "apps.units.destroy",
                                        'restoreRoute' => "apps.units.restore",
                                    ],
                                    'menu' => [
                                        [
                                            'icon' => "mdi:plus",
                                            'title' => "Role creation",
                                            'route' => "apps.roles.create",
                                            'modal' => true,
                                        ],
                                    ],
                                    'filters' => $request->all('search', 'sorted', 'trashed'),
                                    'titles' => [
                                        [
                                            'type' => 'simple',
                                            'title' => 'Role',
                                            'field' => 'name',
                                        ],
                                        [
                                            'type' => 'simple',
                                            'title' => 'Subunits',
                                            'field' => 'children_count',
                                        ],
                        
                                    ],
                                    'items' => $items
                                ],
                            ],
                        ],
                    ]
                ]
            ];
    }

    public function create(Request $request, Unit $unit)
    {
        return Inertia::render('Default/Create', [
            'form' => $this->__form($request, $unit),
            'routes' => [
                'role' => [
                    'route' => route('apps.units.store'),
                    'method' => 'post'
                ],
            ],
        ]);
    }

    public function store($request): RedirectResponse
    {
        dd($request);

        DB::beginTransaction();

        try {
            $unit = Role::firstOrCreate([
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
                    'role_id' => $unit->id,
                    'ability_id' => $ability_id,
                ]);
            }
        } catch(\Exception $e) {
            DB::rollback();
            return Redirect::back()->with('status', "Error when linking abilities to the role.");
        }

        DB::commit();

        return Redirect::route('apps.units.index')->with('status', 'Role created.');
    }
    
    public function edit(Request $request, Unit $unit): Response
    {
        return Inertia::render('Default/Edit', [
            'form' => $this->__form($request, $unit),
            'routes' => [
                'role' => [
                    'route' => route('apps.units.update', $unit->id),
                    'method' => 'patch'
                ],
            ],
            'data' => $unit
        ]);
    }

    public function update(Role $unit, Request $request): RedirectResponse
    {
        dd($request);
        
        DB::beginTransaction();

        try {
            Role::where('id', $unit->id)
                ->update([
                    'name' => $request->name,
                    'description' => $request->description,
                ]);
        } catch(\Exception $e) {
            DB::rollback();
            return Redirect::back()->with('status', "Error when editing the role.");
        }
        
        try {
            $abilities_role_saved = AbilityRole::where('role_id', $unit->id)
                ->get()
                ->map
                ->only('id', 'ability_id')
                ->pluck('ability_id', 'id');

            $abilities_role_to_delete = AbilityRole::where('role_id', $unit->id)
                ->whereNotIn('ability_id', $request->abilities)
                ->get()
                ->map
                ->only('id', 'ability_id')
                ->pluck('ability_id', 'id')
                ->toArray();

            $abilities_role_mainteined = $abilities_role_saved->diff($abilities_role_to_delete);

            $abilities_role_deleted = AbilityRole::where('role_id', $unit->id)
                ->whereIn('ability_id', $abilities_role_to_delete)
                ->delete();
                
            $abilities_role_to_insert = collect($request->abilities)->diff($abilities_role_mainteined);

            foreach($abilities_role_to_insert as $ability_id) {
                $ability_role = AbilityRole::create([
                    'role_id' => $unit->id,
                    'ability_id' => $ability_id,
                ]);
            }
        } catch(\Exception $e) {
            DB::rollback();
            dd($e);
            return Redirect::back()->with('status', "Error when linking abilities to the role.");
        }

        DB::commit();

        return Redirect::route('apps.units.index')->with('status', 'Role edited.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $items = $request->all();

        try {
            $usersToDelete = Unit::whereIn('id', $items['ids'])->delete();
        } catch (Throwable $e) {
            report($e);
     
            return false;
        }
        
        return back()->with('status', 'Items removed succesfully!');
    }

    public function restore(Unit $unit)
    {
        $unit->restore();

        return Redirect::back()->with('status', 'Item restored.');
    }
}
