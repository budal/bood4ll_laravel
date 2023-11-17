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
                $query->where("parent_id", "1");
            })
            ->withCount('children')
            ->sort($request->sorted ?? "name")
            ->paginate(20)
            ->onEachSide(2)
            ->through(function($product){
                $product->parents = $product->getParentsNames();
                return $product;
            })
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
        $units = Unit::orderBy("parent_id")
            ->orderBy("name")
            ->where('parent_id', 0)
            ->with('childrenRecursive')
            ->get()
            ->map(function ($item) use ($unit) {
                $item->path = $item->getParentsNames();
                return $item;
            });

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
                                'type' => "select",
                                'name' => "parent_id",
                                'title' => "Unidade pai",
                                'span' => 2,
                                'content' => $units,
                                'required' => true,
                                // 'multiple' => true,
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
                                'title' => "Cell phone",
                            ],
                            [
                                'type' => "input",
                                'name' => "landline",
                                'title' => "Land line",
                            ],
                            [
                                'type' => "input",
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
        return Inertia::render('Default/Form', [
            'form' => $this->__form($request, $unit),
            'routes' => [
                'role' => [
                    'route' => route('apps.units.store'),
                    'method' => 'post'
                ],
            ],
        ]);
    }

    public function store(Request $request, Unit $unit): RedirectResponse
    {
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
                'role' => [
                    'route' => route('apps.units.update', $unit->id),
                    'method' => 'patch'
                ],
            ],
            'data' => $unit
        ]);
    }

    public function update(Unit $unit, Request $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            Unit::where('id', $unit->id)
                ->update($request->all());
        } catch(\Exception $e) {
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
