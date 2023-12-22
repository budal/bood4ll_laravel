<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UnitsController extends Controller
{
    public $title = 'Units';
    public $description = 'Manage the units registered in the system.';

    public function index(Request $request): Response
    {
        $this->authorize('access', User::class);

        $units = Unit::filter($request, 'units', [
            'where' => [
                'name'
            ],
            'order' => [
                'parent_id',
                'order'
            ]
        ])
            ->leftjoin('unit_user', 'unit_user.unit_id', '=', 'units.id')
            ->select('units.id', 'units.name', 'units.parent_id', 'units.deleted_at')
            ->groupBy('units.id', 'units.name', 'units.parent_id', 'units.deleted_at')
            ->when(!$request->user()->isSuperAdmin(), function ($query) use ($request) {
                if (!$request->user()->hasFullAccess()) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }

                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
            })
            ->withCount('children', 'users')
            ->paginate(20)
            ->onEachSide(2)
            ->through(function ($item) {
                $item->name = $item->getParentsNames();
                // $item->all_users_count = $item->getAllChildren()->pluck('users_count')->sum() + $item->users->count();

                return $item;
            })
            ->appends(collect($request->query)->toArray());

        // dd($units);

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
                                        'createRoute' => [
                                            'route' => 'apps.units.create',
                                            'showIf' => Gate::allows('apps.units.create') && Gate::inspect('isSuperAdmin', User::class)->allowed(),
                                        ],
                                        'editRoute' => [
                                            'route' => 'apps.units.edit',
                                            'showIf' => Gate::allows('apps.units.edit'),
                                        ],
                                        'destroyRoute' => [
                                            'route' => 'apps.units.destroy',
                                            'showIf' => Gate::allows('apps.units.destroy'),
                                        ],
                                        'forceDestroyRoute' => [
                                            'route' => 'apps.roles.forcedestroy',
                                            'showIf' => Gate::allows('apps.roles.forcedestroy') && Gate::inspect('isSuperAdmin', User::class)->allowed(),
                                        ],
                                        'restoreRoute' => [
                                            'route' => 'apps.units.restore',
                                            'showIf' => Gate::allows('apps.units.restore'),
                                        ],
                                    ],
                                    'menu' => [
                                        [
                                            'icon' => 'mdi:source-branch-refresh',
                                            'title' => 'Refresh units hierarchy',
                                            'route' => 'apps.units.hierarchy',
                                            'method' => 'post',
                                            'showIf' => Gate::inspect('isManager', User::class)->allowed(),
                                        ],
                                    ],
                                    'titles' => [
                                        [
                                            'type' => 'text',
                                            'title' => 'Name',
                                            'field' => 'name',
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
        $units = Unit::select('units.id', 'units.shortpath AS name', 'units.active')
            ->leftjoin('unit_user', 'unit_user.unit_id', '=', 'units.id')
            ->groupBy('units.id', 'name', 'units.active')
            ->when(!$request->user()->isSuperAdmin(), function ($query) use ($request) {
                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());

                if (!$request->user()->hasFullAccess()) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }
            })
            ->orderBy('units.shortpath')
            ->get()
            ->map(function ($item) use ($unit) {
                $item->disabled = $item->active === true && $item->id != $unit->id ? false : true;

                return $item;
            });

        // $units->prepend([
        //     'id' => $unit->parent_id,
        //     'name' => '[ root ]',
        //     'disabled' => true
        // ]);

        // dd($units);

        $subunits = $unit
            ->when(!$request->search, function ($query) use ($unit) {
                $query->where('units.parent_id', $unit->id);
            })
            ->filter($request, 'subunits', ['order' => ['parent_unit.name', 'parent_unit.order']])
            ->leftJoin('units as parent_unit', 'units.parent_id', '=', 'parent_unit.id')
            ->with('childrenWithUsersCount')
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
                'showIf' => $unit->id != null,
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
                'showIf' => $unit->id != null,
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

    public function hierarchy(): RedirectResponse
    {
        Unit::orderBy('id')->chunk(100, function (Collection $units) {
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
        $unit['abilities_disabled'] = $unit->id;

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
        // dd($request);
        DB::beginTransaction();

        try {
            $unit->name = $request->name;
            $unit->nickname = $request->nickname;
            $unit->founded = $request->founded;
            $unit->parent_id = $request->parent_id;
            $unit->active = $request->active;
            $unit->expires = $request->expires;
            $unit->cellphone = $request->cellphone;
            $unit->landline = $request->landline;
            $unit->email = $request->email;
            $unit->country = $request->country;
            $unit->state = $request->state;
            $unit->city = $request->city;
            $unit->address = $request->address;
            $unit->complement = $request->complement;
            $unit->postcode = $request->postcode;
            $unit->geo = $request->geo;

            $unit->save();

            $unit->fullpath = $unit->getParentsNames();
            $unit->shortpath = $unit->getParentsNicknames();

            $unit->save();
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
