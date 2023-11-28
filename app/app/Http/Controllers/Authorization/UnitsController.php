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
        $units = Unit::filter($request->all('search', 'sorted', 'trashed'))
            ->when(!$request->search, function ($query) {
                $query->where("parent_id", "1");
            })
            ->withCount('children', 'users')
            ->with('childrenRecursive')
            ->sort($request->sorted ?? "name")
            ->paginate(20)
            ->onEachSide(2)
            ->through(function($item){
                $item->parents = $item->getParentsNames();
                return $item;
            });

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
                [
                    'type' => 'simple',
                    'title' => 'Staff',
                    'field' => 'users_count',
                ],
            ],
            'items' => $units
        ]);
    }
    
    public function __form(Request $request, Unit $unit)
    {
        $units = Unit::orderBy("parent_id")
            ->select("id", "name", "active")
            ->orderBy("name")
            ->where('parent_id', 0)
            ->with('childrenRecursive')
            ->get();

        $subunits = Unit::where('parent_id', $unit->id)
            ->filter($request->all('subunits_search', 'subunits_trashed'))
            ->sort($request->subunits_sorted ?? "name")
            ->withCount('children', 'users')
            ->paginate(20)
            ->onEachSide(2);

        $staff = $unit->users()
            ->where('name', 'ilike', '%'.$request->staff_search.'%')
            ->sort($request->staff_sorted ?? "name")
            ->paginate(20)
            ->onEachSide(2);

        return [
            [
                'id' => "unit",
                'title' => "Unit management",
                'subtitle' => "Manage unit's info.",
                'cols' => 4,
                'fields' => [
                    [
                        [
                            'type' => "select",
                            'name' => "parent_id",
                            'title' => "Unidade pai",
                            'span' => 2,
                            'content' => $units,
                            'required' => true,
                        ],
                        [
                            'type' => "input",
                            'name' => "name",
                            'title' => "Name",
                            'span' => 2,
                            'required' => true,
                        ],
                        [
                            'type' => "input",
                            'name' => "nickname",
                            'title' => "Nickname",
                            'required' => true,
                        ],
                        [
                            'type' => "date",
                            'name' => "founded",
                            'title' => "Founded",
                            'required' => true,
                        ],
                        [
                            'type' => "toggle",
                            'name' => "active",
                            'title' => "Active",
                            'color' => "success",
                            'colorFalse' => "danger",
                        ],
                        [
                            'type' => "date",
                            'name' => "expires",
                            'title' => "Expires in",
                        ],
                        [
                            'type' => "input",
                            'name' => "cellphone",
                            'mask' => "(##) #####-####",
                            'title' => "Cell phone",
                        ],
                        [
                            'type' => "input",
                            'name' => "landline",
                            'mask' => "(##) ####-####",
                            'title' => "Land line",
                        ],
                        [
                            'type' => "email",
                            'name' => "email",
                            'title' => "Email",
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
                            'mask' => "#####-###",
                            'title' => "Post code",
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
                            'title' => "Geographic coordinates",
                            'span' => 4,
                        ],
                        ],
                ],
            ],
            [
                'id' => "subunits",
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
                                        'title' => "Unit creation",
                                        'route' => [
                                            "apps.units.create",
                                            ['id' => $unit->id]
                                        ],
                                        'modal' => true,
                                    ],
                                ],
                                'filters' => $request->all('search', 'sorted', 'trashed'),
                                'titles' => [
                                    [
                                        'type' => 'simple',
                                        'title' => 'Unit',
                                        'field' => 'name',
                                    ],
                                    [
                                        'type' => 'simple',
                                        'title' => 'Subunits',
                                        'field' => 'children_count',
                                    ],
                                    [
                                        'type' => 'simple',
                                        'title' => 'Staff',
                                        'field' => 'users_count',
                                    ],
                    
                                ],
                                'items' => $subunits
                            ],
                        ],
                    ],
                ]
            ],
            [
                'id' => "staff",
                'title' => "Staff management",
                'subtitle' => "Manage unit's staff",
                'condition' => $unit->id <> null,
                'cols' => 2,
                'fields' => [
                    [
                        [
                            'type' => "table",
                            'name' => "users",
                            'title' => "Staff",
                            'span' => 2,
                            'shortcutKey' => "a",
                            'content' => [
                                'routes' => [
                                    'editRoute' => "apps.units.edit",
                                    'destroyRoute' => "apps.units.destroy",
                                    'restoreRoute' => "apps.units.restore",
                                ],
                                'menu' => [
                                    [
                                        'icon' => "mdi:plus",
                                        'title' => "Unit creation",
                                        'route' => [
                                            "apps.units.create",
                                            ['id' => $unit->id]
                                        ],
                                        'modal' => true,
                                    ],
                                ],
                                'filters' => $request->all('search', 'sorted', 'trashed'),
                                'titles' => [
                                    [
                                        'type' => 'simple',
                                        'title' => 'Name',
                                        'field' => 'name',
                                    ],
                                ],
                                'items' => $staff
                            ],
                        ],
                    ],
                ]
            ],
        ];
    }

    public function create(Request $request, Unit $unit)
    {
        $data['parent_id'] = $request->unit->id ?? '';
        
        return Inertia::render('Default/Form', [
            'form' => $this->__form($request, $unit),
            'routes' => [
                'unit' => [
                    'route' => route('apps.units.store'),
                    'method' => 'post'
                ],
            ],
            'data' => $data
        ]);
    }

    public function store(Request $request, Unit $unit): RedirectResponse
    {
        dd($request);
        DB::beginTransaction();

        try {
            $unit = Unit::firstOrCreate($request->except(['users']));
        } catch(\Exception $e) {
            dd($e);
            DB::rollback();
            return Redirect::back()->withInput()->with('status', "Error when inserting a new unit.");
        }
        
        DB::commit();

        return Redirect::route('apps.units.index')->with('status', 'Unit created.');
    }
    
    public function edit(Request $request, Unit $unit): Response
    {
        return Inertia::render('Default/Form', [
            'form' => $this->__form($request, $unit),
            'routes' => [
                'unit' => [
                    'route' => route('apps.units.update', $unit->id),
                    'method' => 'patch'
                ],
            ],
            'data' => $unit
        ]);
    }

    public function update(Unit $unit, Request $request): RedirectResponse
    {
        dd($request);
        DB::beginTransaction();

        try {
            Unit::where('id', $unit->id)
                ->update($request->all());
        } catch(\Exception $e) {

            report($e);

            // dd($e);
            DB::rollback();
            return Redirect::back()->withInput()->with('status', "Error when editing the unit.");
        }
        
        DB::commit();

        return Redirect::route('apps.units.index')->with('status', 'Unit edited.');
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
