<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class UnitsController extends Controller
{
    public function getUnits(Request $request, Unit $unit): JsonResponse
    {
        $units = Unit::leftJoin('unit_user', 'unit_user.unit_id', '=', 'units.id')
            ->select('units.id', 'units.parent_id', 'units.shortpath as name', 'units.active')
            ->groupBy('units.id', 'units.parent_id', 'name', 'units.active')
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());

                if ($request->user()->cannot('hasFullAccess', User::class)) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }
            })
            ->orderBy('units.shortpath')
            ->get()
            ->map(function ($item) use ($unit) {
                $item->disabled = $item->active === true && $item->id != $unit->id ? false : true;

                return $item;
            });

        if ($units->pluck('id')->contains($unit->parent_id) === false && $unit->id != null) {
            $parent = Unit::where('id', $unit->parent_id)->first();

            if ($parent === null) {
                $units->prepend([
                    'id' => 0,
                    'name' => '[ root ]',
                ]);
            } else {
                $units->prepend([
                    'id' => $parent->id,
                    'name' => $parent->getParentsNames(),
                ]);
            }
        }

        return response()->json($units);
    }

    public function getUnitsIndex(Request $request): JsonResponse
    {
        $units = Unit::leftJoin('unit_user', 'unit_user.unit_id', '=', 'units.id')
            ->select('units.id', 'units.shortpath', 'units.active', 'units.deleted_at')
            ->groupBy('units.id', 'units.shortpath', 'units.active', 'units.deleted_at')
            ->withCount([
                'children', 'users',
                'users as users_all_count' => function ($query) {
                    $query->orWhere(function ($query) {
                        $query->whereRaw('unit_id IN (
                            SELECT (json_array_elements(u.children_id::json)::text)::bigint FROM units u WHERE u.id = units.id
                            )');
                    });
                },
            ])
            ->orderBy('shortpath')
            ->when($request->showItems ?? null, function ($query, $showItems) {
                if ($showItems == 'both') {
                    $query->withTrashed();
                } elseif ($showItems == 'trashed') {
                    $query->onlyTrashed();
                }
            })
            ->where('shortpath', 'ilike', "%$request->search%")
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                if ($request->user()->cannot('hasFullAccess', User::class)) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }

                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
            })
            ->cursorPaginate(30)
            ->withQueryString();

        return response()->json($units);
    }

    public function getUnitStaff(Request $request, Unit $unit): JsonResponse
    {
        $staff = User::leftJoin('unit_user', 'unit_user.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->with('unitsClassified', 'unitsWorking')
            ->withCount('roles')
            ->orderBy('name')
            ->where("name", 'ilike', '%' . $request->search . '%')
            ->when($request->showItems ?? null, function ($query, $showItems) {
                if ($showItems == 'both') {
                    $query->withTrashed();
                } elseif ($showItems == 'trashed') {
                    $query->onlyTrashed();
                }
            })
            ->when(
                $request->show === 'all',
                function ($query) use ($unit) {
                    $query->whereIn('unit_user.unit_id', json_decode($unit['children_id']));
                },
                function ($query) use ($unit) {
                    $query->where('unit_user.unit_id', $unit->id);
                }
            )
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                if ($request->user()->cannot('hasFullAccess', User::class)) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }

                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
            })
            ->cursorPaginate(30)
            ->withQueryString();

        return response()->json($staff);
    }

    public function index(Request $request): Response
    {
        $this->authorize('access', User::class);

        return Inertia::render('Bood4ll', [
            // 'tabs' => false,
            'structure' => [
                [
                    'label' => Route::current()->title,
                    'description' => Route::current()->description,
                    'fields' => [
                        [
                            'type' => 'table',
                            'name' => 'name',
                            'component' => [
                                'exportCSV' => true,
                                'actions' => [
                                    'index' => [
                                        'source' => 'getUnitsIndex',
                                        'visible' => true,
                                        'disabled' => true,
                                    ],
                                    'create' => [
                                        'confirm' => true,
                                        'popup' => 'Do you want to insert a new unit?',
                                        'toastTitle' => 'Added',
                                        'toast' => 'Unit inserted.',
                                        'callback' => 'apps.units.store',
                                        'method' => 'post',
                                        // 'visible' => (
                                        //     $request->user()->can('access', User::class)
                                        //     && $request->user()->can('isManager', User::class)
                                        //     && $request->user()->can('canManageNestedData', User::class)
                                        // ),
                                        'component' => [
                                            [
                                                'label' => 'Main data',
                                                'description' => 'Unit data management.',
                                                'cols' => 4,
                                                'fields' => $this->__fields(),
                                                'visible' => true,
                                                // 'visible' => (
                                                //     $request->user()->can('access', User::class)
                                                //     && $request->user()->can('isManager', User::class)
                                                //     && $request->user()->can('canManageNestedData', User::class)
                                                // )
                                            ],
                                        ],
                                    ],
                                    'edit' => [
                                        'confirm' => true,
                                        'popup' => 'Do you want to edit this unit?',
                                        'toastTitle' => 'Edited',
                                        'toast' => 'Unit edited.',
                                        'callback' => 'apps.units.update',
                                        'method' => 'patch',
                                        'visible' => true,
                                        'disabled' => false,
                                        // 'tabs' => false,
                                        'component' => [
                                            [
                                                'label' => 'Main data',
                                                'description' => 'Unit data management.',
                                                'source' => 'getUnitInfo',
                                                'sourceAttributes' => ['unit' => 'id'],
                                                'cols' => 4,
                                                'fields' => $this->__fields(),
                                            ],
                                            [
                                                'label' => 'Staff',
                                                'description' => 'Staff management of this unit.',
                                                'fields' => [
                                                    [
                                                        'type' => 'table',
                                                        'name' => 'users',
                                                        'component' => [
                                                            'actions' => [
                                                                'index' => [
                                                                    'source' => 'getUnitStaff',
                                                                    'sourceAttributes' => ['unit' => 'id'],
                                                                    'visible' => true,
                                                                    'disabled' => true,
                                                                ],
                                                            ],
                                                            'menu' => [
                                                                [
                                                                    'icon' => 'mdi:account-multiple',
                                                                    'label' => 'Local staff',
                                                                    'source' => 'getUnitStaff',
                                                                    'sourceAttributes' => ['unit' => 'id'],
                                                                    'showIf' => $request->user()->can('canManageNestedData', User::class),
                                                                ],
                                                                [
                                                                    'icon' => 'mdi:account-group-outline',
                                                                    'label' => 'Total staff',
                                                                    'source' => [
                                                                        'route' => 'getUnitStaff',
                                                                        'attributes' => ['show' => 'all']
                                                                    ],
                                                                    'sourceAttributes' => ['unit' => 'id'],
                                                                    'showIf' => $request->user()->can('canManageNestedData', User::class)
                                                                ],
                                                            ],
                                                            'titles' => [
                                                                [
                                                                    'type' => 'avatar',
                                                                    'header' => 'Avatar',
                                                                    'field' => 'id',
                                                                    'fallback' => 'name',
                                                                ],
                                                                [
                                                                    'type' => 'text',
                                                                    'header' => 'User',
                                                                    'field' => 'name',
                                                                ],
                                                                [
                                                                    'type' => 'composite',
                                                                    'header' => 'Classified',
                                                                    'class' => 'collapse',
                                                                    'field' => 'units_classified',
                                                                    'options' => [
                                                                        [
                                                                            'field' => 'name',
                                                                        ],
                                                                    ],
                                                                ],
                                                                [
                                                                    'type' => 'composite',
                                                                    'header' => 'Working',
                                                                    'class' => 'collapse',
                                                                    'field' => 'units_working',
                                                                    'options' => [
                                                                        [
                                                                            'field' => 'name',
                                                                        ],
                                                                    ],
                                                                ],
                                                                [
                                                                    'type' => 'text',
                                                                    'header' => 'Roles',
                                                                    'field' => 'roles_count',
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'destroy' => [
                                        'dialog' => 'Do you want to destroy this unit?|Do you want to destroy this units?',
                                        'dialogClass' => 'warning',
                                        'toast' => 'Unit destroyed|Units destroyed.',
                                        'toastClass' => 'warning',
                                        'callback' => 'apps.units.destroy',
                                        'method' => 'delete',
                                        'visible' => true,
                                        'disabled' => false,
                                    ],
                                    'restore' => [
                                        'dialog' => 'Do you want to restore this unit?|Do you want to restore this units?',
                                        'toast' => 'Unit restored|Units restored.',
                                        'toastClass' => 'warning',
                                        'callback' => 'apps.units.restore',
                                        'method' => 'post',
                                        'visible' => true,
                                        'disabled' => false,
                                    ],
                                    // 'forceDestroy' => [
                                    //     'dialog' => 'Do you want to erase this unit?|Do you want to erase this units?',
                                    //     'dialogClass' => 'danger',
                                    //     'toast' => 'Unit erased.',
                                    // 'toastClass' => 'warning',
                                    //     'callback' => 'apps.units.forceDestroy',
                                    //     'method' => 'delete',
                                    //     'visible' => true,
                                    //     'disabled' => false,
                                    // ],
                                    // 'reorder' => [
                                    //     'toast' => 'Unit reordered.',
                                    // 'toastClass' => 'warning',
                                    //     'callback' => 'apps.units.reorder',
                                    //     'visible' => true,
                                    // ]
                                ],
                                'menu' => [
                                    [
                                        'icon' => 'pi pi-refresh',
                                        'label' => 'Refresh units hierarchy',
                                        'source' => 'apps.units.hierarchy',
                                        'method' => 'post',
                                        'showIf' => $request->user()->can('isSuperAdmin', User::class),
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'text',
                                        'header' => 'Name',
                                        'field' => 'shortpath',
                                    ],
                                    [
                                        'type' => 'active',
                                        'header' => 'Active',
                                        'field' => 'active',
                                    ],
                                    [
                                        'type' => 'text',
                                        'header' => 'Subunits',
                                        'field' => 'children_count',
                                        'showIf' => $request->user()->can('canManageNestedData', User::class),
                                    ],
                                    [
                                        'type' => 'text',
                                        'header' => 'Local staff',
                                        'field' => 'users_count',
                                        'showIf' => $request->user()->can('hasFullAccess', User::class),
                                    ],
                                    [
                                        'type' => 'text',
                                        'header' => 'Total staff',
                                        'field' => 'users_all_count',
                                        'showIf' => $request->user()->can('canManageNestedData', User::class),
                                    ],
                                ],
                                // 'data' => $this->getUnits($request),
                            ],
                        ],
                    ],
                ],
            ]
        ]);
    }

    public function __form(Request $request, Unit $unit): array
    {
        return [
            'component' => [
                [
                    'label' => 'Main data',
                    'description' => 'Unit data management.',
                    'source' => 'getUnitInfo',
                    'sourceAttributes' => ['unit' => 'id'],
                    'callback' => 'getUnitInfo',
                    'disabledIf' => $unit->id !== null && $request->user()->cannot('isOwner', $unit),
                    'cols' => 4,
                    'fields' => [
                        [
                            'type' => 'input',
                            'name' => 'name',
                            'label' => 'Name',
                            'span' => 2,
                            'required' => true,
                        ],
                        [
                            'type' => 'dropdown',
                            'name' => 'parent_id',
                            'label' => 'Belongs to',
                            'source' => 'getUnits',
                            'sourceAttributes' => ['unit' => 'id'],
                            'span' => 2,
                            'required' => true,
                        ],
                        [
                            'type' => 'input',
                            'name' => 'nickname',
                            'label' => 'Nickname',
                            'required' => true,
                        ],
                        [
                            'type' => 'calendar',
                            'name' => 'founded',
                            'dateFormat' => 'dd/mm/yy',
                            'label' => 'Founded',
                            'required' => true,
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'active',
                            'label' => 'Active',
                            'colorOn' => 'success',
                            'colorOff' => 'danger',
                        ],
                        [
                            'type' => 'calendar',
                            'name' => 'expires',
                            // 'dateFormat' => 'dd/mm/yy',
                            'label' => 'Inactivated at',
                        ],
                        [
                            'type' => 'mask',
                            'name' => 'cellphone',
                            'mask' => '(99) 99999-9999',
                            'label' => 'Cell phone',
                        ],
                        [
                            'type' => 'mask',
                            'name' => 'landline',
                            'mask' => '(99) 9999-9999',
                            'label' => 'Land line',
                        ],
                        [
                            'type' => 'email',
                            'name' => 'email',
                            'label' => 'Email',
                            'span' => 2,
                        ],
                        [
                            'type' => 'input',
                            'name' => 'country',
                            'label' => 'Country',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'state',
                            'label' => 'State',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'city',
                            'label' => 'City',
                        ],
                        [
                            'type' => 'mask',
                            'name' => 'postcode',
                            'mask' => '99999-999',
                            'label' => 'Post code',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'address',
                            'label' => 'Address',
                            'span' => 3,
                        ],
                        [
                            'type' => 'input',
                            'name' => 'complement',
                            'label' => 'Complement',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'geo',
                            'label' => 'Geographic coordinates',
                            'span' => 4,
                        ],
                    ],
                ],
                [
                    'label' => 'Staff',
                    'description' => 'Staff management of this unit.',
                    'showIf' => $unit->id != null,
                    'fields' => [
                        [
                            'type' => 'table',
                            'name' => 'users',
                            'component' => [
                                'id' => 'units',
                                'actions' => [
                                    'index' => [
                                        'source' => 'getUnitStaff',
                                        'sourceAttributes' => ['unit' => 'id'],
                                        'visible' => true,
                                        'disabled' => true,
                                        'values' => [],
                                    ],
                                ],
                                'menu' => [
                                    [
                                        'icon' => 'mdi:account-multiple',
                                        'label' => 'Local staff',
                                        'source' => 'getUnitStaff',
                                        'sourceAttributes' => ['unit' => 'id'],
                                        'showIf' => $request->user()->can('canManageNestedData', User::class),
                                    ],
                                    [
                                        'icon' => 'mdi:account-group-outline',
                                        'label' => 'Total staff',
                                        'source' => [
                                            'route' => 'getUnitStaff',
                                            'attributes' => ['show' => 'all']
                                        ],
                                        'sourceAttributes' => ['unit' => 'id'],
                                        'showIf' => $request->user()->can('canManageNestedData', User::class)
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'avatar',
                                        'header' => 'Avatar',
                                        'field' => 'id',
                                        'fallback' => 'name',
                                        'disableSort' => true,
                                    ],
                                    [
                                        'type' => 'text',
                                        'header' => 'User',
                                        'field' => 'name',
                                    ],
                                    [
                                        'type' => 'composite',
                                        'header' => 'Classified',
                                        'class' => 'collapse',
                                        'field' => 'units_classified',
                                        'options' => [
                                            [
                                                'field' => 'name',
                                            ],
                                        ],
                                    ],
                                    [
                                        'type' => 'composite',
                                        'header' => 'Working',
                                        'class' => 'collapse',
                                        'field' => 'units_working',
                                        'options' => [
                                            [
                                                'field' => 'name',
                                            ],
                                        ],
                                    ],
                                    [
                                        'type' => 'text',
                                        'header' => 'Roles',
                                        'field' => 'roles_count',
                                    ],
                                ],
                                // 'data' => $units,
                            ],
                        ],
                    ],
                ],
            ]
        ];
    }

    public function __fields(): array
    {
        return [
            [
                'type' => 'input',
                'name' => 'name',
                'label' => 'Name',
                'span' => 2,
                'required' => true,
            ],
            [
                'type' => 'dropdown',
                'name' => 'parent_id',
                'label' => 'Belongs to',
                'source' => 'getUnits',
                'sourceAttributes' => ['unit' => 'id'],
                'span' => 2,
                'required' => true,
            ],
            [
                'type' => 'input',
                'name' => 'nickname',
                'label' => 'Nickname',
                'required' => true,
            ],
            [
                'type' => 'calendar',
                'name' => 'founded',
                'dateFormat' => 'dd/mm/yy',
                'label' => 'Founded',
                'required' => true,
            ],
            [
                'type' => 'toggle',
                'name' => 'active',
                'label' => 'Active',
                'colorOn' => 'success',
                'colorOff' => 'danger',
            ],
            [
                'type' => 'calendar',
                'name' => 'expires',
                // 'dateFormat' => 'dd/mm/yy',
                'label' => 'Inactivated at',
            ],
            [
                'type' => 'mask',
                'name' => 'cellphone',
                'mask' => '(99) 99999-9999',
                'label' => 'Cell phone',
            ],
            [
                'type' => 'mask',
                'name' => 'landline',
                'mask' => '(99) 9999-9999',
                'label' => 'Land line',
            ],
            [
                'type' => 'email',
                'name' => 'email',
                'label' => 'Email',
                'span' => 2,
            ],
            [
                'type' => 'input',
                'name' => 'country',
                'label' => 'Country',
            ],
            [
                'type' => 'input',
                'name' => 'state',
                'label' => 'State',
            ],
            [
                'type' => 'input',
                'name' => 'city',
                'label' => 'City',
            ],
            [
                'type' => 'mask',
                'name' => 'postcode',
                'mask' => '99999-999',
                'label' => 'Post code',
            ],
            [
                'type' => 'input',
                'name' => 'address',
                'label' => 'Address',
                'span' => 3,
            ],
            [
                'type' => 'input',
                'name' => 'complement',
                'label' => 'Complement',
            ],
            [
                'type' => 'input',
                'name' => 'geo',
                'label' => 'Geographic coordinates',
                'span' => 4,
            ],
        ];
    }

    public function hierarchy(): RedirectResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('isSuperAdmin', User::class);

        Unit::orderBy('id')->chunk(100, function (Collection $units) {
            foreach ($units as $unit) {
                $unit = Unit::where('id', $unit->id)->first();

                $unit->fullpath = $unit->getParentsNames();
                $unit->shortpath = $unit->getParentsNicknames();
                $unit->children_id = collect($unit->getDescendants())->toJson();

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
        $this->authorize('access', User::class);
        $this->authorize('isManager', User::class);
        $this->authorize('canManageNestedData', User::class);

        $data['parent_id'] = $request->unit->id ?? '';
        $data['active'] = true;

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
        $this->authorize('access', User::class);
        $this->authorize('isManager', User::class);
        $this->authorize('canManageNestedData', User::class);

        DB::beginTransaction();

        try {
            $unit->name = $request->name;
            $unit->nickname = $request->nickname;
            $unit->owner = $request->user()->id;
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
            $unit->children_id = collect($unit->getDescendants())->toJson();

            $unit->save();

            $unit->users()->attach($request->user()->id, ['primary' => false]);

            $newParentUnit = Unit::where('id', $request->parent_id)->first();
            $newParentUnit->children_id = collect($newParentUnit->getDescendants())->toJson();

            $newParentUnit->save();
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

        return Redirect::route('apps.units.edit', $unit->id)->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
            'toast_count' => 1,
        ]);
    }

    public function edit(Request $request, Unit $unit): JsonResponse
    {
        // $this->authorize('access', User::class);
        // $this->authorize('isActive', $unit);
        // $this->authorize('canEdit', $unit);

        $unit['abilities_disabled'] = $unit->id;

        return response()->json($unit);
    }

    public function update(Unit $unit, Request $request): RedirectResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('isActive', $unit);
        $this->authorize('isManager', User::class);
        $this->authorize('canEdit', $unit);
        $this->authorize('isOwner', $unit);

        if (
            $request->user()->cannot('isSuperAdmin', User::class)
            && $request->user()->unitsIds()->contains($unit->parent_id) === false
            && $unit->parent_id != $request->parent_id
        ) {
            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => "You cannot change the unit this record belongs to.",
            ]);
        }

        DB::beginTransaction();

        try {
            $parentId = $unit->parent_id;

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
            $unit->children_id = collect($unit->getDescendants())->toJson();

            $unit->save();

            if ($parentId) {
                $oldParentUnit = Unit::where('id', $parentId)->first();
                $oldParentUnit->children_id = collect($oldParentUnit->getDescendants())->toJson();

                $oldParentUnit->save();
            }

            if ($request->parent_id) {
                $newParentUnit = Unit::where('id', $request->parent_id)->first();
                $newParentUnit->children_id = collect($newParentUnit->getDescendants())->toJson();

                $newParentUnit->save();
            }
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
        $this->authorize('access', User::class);
        $this->authorize('isManager', User::class);
        $this->authorize('canDestroyOrRestore', [Unit::class, $request]);

        try {
            $total = Unit::whereIn('id', $request->list)->delete();

            return back()->with([
                'toast_type' => 'success',
                'toast_message' => '{0} Nothing to remove.|[1] Item removed successfully.|[2,*] :total items successfully removed.',
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on remove selected item.|Error on remove selected items.',
                'toast_count' => count($request->list),
            ]);
        }
    }

    public function forceDestroy(Request $request): RedirectResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('isSuperAdmin', User::class);

        try {
            $total = Unit::whereIn('id', $request->list)->forceDelete();

            return back()->with([
                'toast_type' => 'success',
                'toast_message' => '{0} Nothing to erase.|[1] Item erased successfully.|[2,*] :total items successfully erased.',
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on erase selected item.|Error on erase selected items.',
                'toast_count' => $request->list,
            ]);
        }
    }

    public function restore(Request $request): RedirectResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('isManager', User::class);
        $this->authorize('canDestroyOrRestore', [Unit::class, $request]);

        try {
            $total = Unit::whereIn('id', $request->list)->restore();

            return back()->with([
                'toast_type' => 'success',
                'toast_message' => '{0} Nothing to restore.|[1] Item restored successfully.|[2,*] :total items successfully restored.',
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on restore selected item.|Error on restore selected items.',
                'toast_count' => $request->list,
            ]);
        }
    }
}
