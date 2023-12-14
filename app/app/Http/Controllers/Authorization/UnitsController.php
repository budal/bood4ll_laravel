<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UnitsController extends Controller
{
    public $title = 'Units';
    public $description = 'Manage the units registered in the system.';

    public function index(Request $request): Response
    {
        // dd(Unit::getTable());

        $units = Unit::filter($request, 'units', 'parent_unit.name,parent_unit.order')
            ->when(!$request->units_search, function ($query) {
                $query->where('units.parent_id', null);
            })
            ->leftJoin('units as parent_unit', 'units.parent_id', '=', 'parent_unit.id')
            ->with('childrenRecursive')
            ->withCount('children', 'users')
            ->paginate(20)
            ->onEachSide(2)
            ->through(function ($item) {
                $item->all_users_count = $item->getAllChildren()->pluck('users_count')->sum() + $item->users_count;

                return $item;
            })
            ->appends(collect($request->query)->toArray());

        // dd($units[0]);

        return Inertia::render('Default', [
            'form' => [
                [
                    'id' => 'units',
                    'title' => $this->title,
                    'subtitle' => $this->description,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'units',
                                'content' => [
                                    'routes' => [
                                        'createRoute' => 'apps.units.create',
                                        'editRoute' => 'apps.units.edit',
                                        'destroyRoute' => 'apps.units.destroy',
                                        'restoreRoute' => 'apps.units.restore',
                                    ],
                                    'menu' => [
                                        [
                                            'icon' => 'mdi:source-branch-refresh',
                                            'title' => 'Refresh units hierarchy',
                                            'route' => 'apps.units.hierarchy',
                                            'method' => 'post',
                                        ],
                                    ],
                                    'titles' => [
                                        [
                                            'type' => 'text',
                                            'title' => 'Name',
                                            'field' => 'shortpath',
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Subunits',
                                            'field' => 'children_count',
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Local staff',
                                            'field' => 'users_count',
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Total staff',
                                            'field' => 'all_users_count',
                                            'disableSort' => true,
                                        ],
                                    ],
                                    'items' => $units,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function __form(Request $request, Unit $unit): array
    {
        $units = Unit::orderBy('parent_id')
            ->select('id', 'name', 'active')
            ->with('childrenRecursive')
            ->orderBy('name')
            ->where('parent_id', 0)
            ->get();

        $subunits = $unit
            ->when(!$request->search, function ($query) use ($unit) {
                $query->where('units.parent_id', $unit->id);
            })
            ->filter($request, 'subunits', 'order')
            ->with('childrenRecursive')
            ->withCount('children', 'users')
            ->paginate($perPage = 20, $columns = ['*'], $pageName = 'subunits')
            ->onEachSide(2)
            ->appends(collect($request->query)->toArray())
            ->through(function ($item) {
                $item->all_users_count = $item->getAllChildren()->pluck('users_count')->sum() + $item->users_count;

                return $item;
            });

        $staff = $unit->users()
            ->filter($request, 'staff')
            ->withCount('roles')
            ->paginate($perPage = 20, $columns = ['*'], $pageName = 'staff')
            ->onEachSide(2)
            ->appends(collect($request->query)->toArray());

        return [
            [
                'id' => 'unit',
                'title' => 'Main data',
                'subtitle' => 'Unit data management.',
                'cols' => 4,
                'fields' => [
                    [
                        [
                            'type' => 'input',
                            'name' => 'name',
                            'title' => 'Name',
                            'span' => 2,
                            'required' => true,
                        ],
                        [
                            'type' => 'select',
                            'name' => 'parent_id',
                            'title' => 'Unidade pai',
                            'span' => 2,
                            'content' => $units,
                            'required' => true,
                        ],
                        [
                            'type' => 'input',
                            'name' => 'nickname',
                            'title' => 'Nickname',
                            'required' => true,
                        ],
                        [
                            'type' => 'date',
                            'name' => 'founded',
                            'title' => 'Founded',
                            'required' => true,
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'active',
                            'title' => 'Active',
                            'colorOn' => 'success',
                            'colorOff' => 'danger',
                        ],
                        [
                            'type' => 'date',
                            'name' => 'expires',
                            'title' => 'Expires in',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'cellphone',
                            'mask' => '(##) #####-####',
                            'title' => 'Cell phone',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'landline',
                            'mask' => '(##) ####-####',
                            'title' => 'Land line',
                        ],
                        [
                            'type' => 'email',
                            'name' => 'email',
                            'title' => 'Email',
                            'span' => 2,
                        ],
                        [
                            'type' => 'input',
                            'name' => 'country',
                            'title' => 'Country',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'state',
                            'title' => 'State',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'city',
                            'title' => 'City',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'postcode',
                            'mask' => '#####-###',
                            'title' => 'Post code',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'address',
                            'title' => 'Address',
                            'span' => 3,
                        ],
                        [
                            'type' => 'input',
                            'name' => 'complement',
                            'title' => 'Complement',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'geo',
                            'title' => 'Geographic coordinates',
                            'span' => 4,
                        ],
                        ],
                ],
            ],
            [
                'id' => 'subunits',
                'title' => 'Subunits',
                'subtitle' => 'Management of subunits of this unit.',
                'condition' => $unit->id != null,
                'fields' => [
                    [
                        [
                            'type' => 'table',
                            'name' => 'subunits',
                            'content' => [
                                'routes' => [
                                    'createRoute' => [
                                        'route' => 'apps.units.create',
                                        'attributes' => $unit->id,
                                    ],
                                    'editRoute' => 'apps.units.edit',
                                    'destroyRoute' => 'apps.units.destroy',
                                    'restoreRoute' => 'apps.units.restore',
                                ],
                                'titles' => [
                                    [
                                        'type' => 'text',
                                        'title' => 'Unit',
                                        'field' => 'shortpath',
                                    ],
                                    [
                                        'type' => 'text',
                                        'title' => 'Subunits',
                                        'field' => 'children_count',
                                    ],
                                    [
                                        'type' => 'text',
                                        'title' => 'Local staff',
                                        'field' => 'users_count',
                                    ],
                                    [
                                        'type' => 'text',
                                        'title' => 'Total staff',
                                        'field' => 'all_users_count',
                                        'disableSort' => true,
                                    ],
                                ],
                                'items' => $subunits,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'staff',
                'title' => 'Staff',
                'subtitle' => 'Staff management of this unit.',
                'condition' => $unit->id != null,
                'cols' => 2,
                'fields' => [
                    [
                        [
                            'type' => 'table',
                            'name' => 'users',
                            'span' => 2,
                            'content' => [
                                'routes' => [
                                    'editRoute' => 'apps.users.edit',
                                    'destroyRoute' => 'apps.units.destroy',
                                    'restoreRoute' => 'apps.units.restore',
                                ],
                                'menu' => [
                                    [
                                        'icon' => 'mdi:plus',
                                        'title' => 'Unit creation',
                                        'route' => [
                                            'apps.units.create',
                                            ['id' => $unit->id],
                                        ],
                                        'modal' => true,
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'avatar',
                                        'title' => 'Avatar',
                                        'field' => 'id',
                                        'fallback' => 'name',
                                        'disableSort' => true,
                                    ],
                                    [
                                        'type' => 'composite',
                                        'title' => 'User',
                                        'field' => 'name',
                                        'values' => [
                                            [
                                                'field' => 'name',
                                            ],
                                            [
                                                'field' => 'email',
                                                'class' => 'text-xs',
                                            ],
                                        ],
                                    ],
                                    [
                                        'type' => 'text',
                                        'title' => 'Roles',
                                        'field' => 'roles_count',
                                    ],
                                ],
                                'items' => $staff,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function create(Request $request, Unit $unit): Response
    {
        $data['parent_id'] = $request->unit->id ?? '';

        return Inertia::render('Default', [
            'form' => $this->__form($request, $unit),
            'routes' => [
                'unit' => [
                    'route' => route('apps.units.store'),
                    'method' => 'post',
                ],
            ],
            'data' => $data,
        ]);
    }

    public function store(Request $request, Unit $unit): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $unit = Unit::firstOrCreate([
                'name' => $request->name,
                'nickname' => $request->nickname,
                'founded' => $request->founded,
                'parent_id' => $request->parent_id,
                'active' => $request->active,
                'expires' => $request->expires,
                'cellphone' => $request->cellphone,
                'landline' => $request->landline,
                'email' => $request->email,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'address' => $request->address,
                'complement' => $request->complement,
                'postcode' => $request->postcode,
                'geo' => $request->geo,
            ]);
        } catch (\Exception $e) {
            report($e);

            DB::rollback();

            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on add this item.|Error on add the items.',
                'toast_count' => 1,
            ]);
        }

        DB::commit();

        return Redirect::back()->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
            'toast_count' => 1,
        ]);
    }

    public function edit(Request $request, Unit $unit): Response
    {
        return Inertia::render('Default', [
            'form' => $this->__form($request, $unit),
            'routes' => [
                'unit' => [
                    'route' => route('apps.units.update', $unit->id),
                    'method' => 'patch',
                ],
            ],
            'data' => $unit,
            // 'tabs' => false,
        ]);
    }

    public function update(Unit $unit, Request $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            Unit::where('id', $unit->id)->update([
                'name' => $request->name,
                'nickname' => $request->nickname,
                'founded' => $request->founded,
                'parent_id' => $request->parent_id,
                'active' => $request->active,
                'expires' => $request->expires,
                'cellphone' => $request->cellphone,
                'landline' => $request->landline,
                'email' => $request->email,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'address' => $request->address,
                'complement' => $request->complement,
                'postcode' => $request->postcode,
                'geo' => $request->geo,
            ]);
        } catch (\Exception $e) {
            report($e);

            DB::rollback();

            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on edit selected item.|Error on edit selected items.',
                'toast_count' => 1,
            ]);
        }

        DB::commit();

        return Redirect::back()->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to edit.|[1] Item edited successfully.|[2,*] :total items successfully edited.',
            'toast_count' => 1,
        ]);
    }

    public function hierarchy(): RedirectResponse
    {
        DB::table('units')->orderBy('id')->chunk(100, function (Collection $units) {
            foreach ($units as $unit) {
                $unit = Unit::where('id', $unit->id)->first();

                $unit->fullpath = $unit->getParentsNames();
                $unit->shortpath = $unit->getParentsNicknames();

                $unit->save();
            }
        });

        return Redirect::back()->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to refresh.|[1] Item refreshed successfully.|[2,*] :total items successfully refreshed.',
            'toast_count' => 1,
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $items = $request->all();

        try {
            $usersToDelete = Unit::whereIn('id', $items['ids'])->delete();
        } catch (\Throwable $e) {
            report($e);

            return back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on remove selected item.|Error on remove selected items.',
                'toast_count' => count($request->list),
            ]);
        }

        return back()->with('status', 'Items removed succesfully!');
    }

    public function restore(Unit $unit): RedirectResponse
    {
        $unit->restore();

        return Redirect::back()->with('status', 'Item restored.');
    }
}
