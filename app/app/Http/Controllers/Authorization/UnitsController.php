<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class UnitsController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('access', User::class);

        $unitsUsers = Unit::leftJoin('unit_user', 'unit_user.unit_id', '=', 'units.id')
            ->groupBy('units.id')
            ->select('units.id as unit_id')
            ->selectRaw('COUNT(unit_user.unit_id) as local_users')
            ->selectRaw("
                (SELECT COUNT(suu.unit_id) FROM unit_user suu 
                    INNER JOIN units u ON u.id = suu.unit_id
                    WHERE 
                        unit_id IN (
                            SELECT (json_array_elements(u.children_id::json)::text)::bigint FROM units u WHERE u.id = units.id
                        ) 
                        AND suu.primary = true
                ) as all_users
            ")
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                if ($request->user()->cannot('hasFullAccess', User::class)) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }

                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
            });

        $units = Unit::leftJoin('unit_user', 'unit_user.unit_id', '=', 'units.id')
            ->filter($request, 'units', [
                'where' => ['name'],
                'order' => ['shortpath']
            ])
            ->leftJoinSub($unitsUsers, 'units_users', function (JoinClause $join) {
                $join->on('units.id', '=', 'units_users.unit_id');
            })
            ->groupBy('units.id', 'units.shortpath', 'units_users.local_users', 'units_users.all_users')
            ->select('units.id', 'units.shortpath as name', 'units_users.local_users', 'units_users.all_users')
            ->withCount('children')
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                if ($request->user()->cannot('hasFullAccess', User::class)) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }

                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
            })
            ->paginate(20)
            ->onEachSide(2)
            ->withQueryString();

        return Inertia::render('Default', [
            'form' => [
                [
                    'id' => 'units',
                    'title' => Route::current()->title,
                    'subtitle' => Route::current()->description,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'units',
                                'content' => [
                                    'routes' => [
                                        'createRoute' => [
                                            'route' => 'apps.units.create',
                                            'showIf' => Gate::allows('apps.units.create') && $request->user()->can('isManager', User::class) && $request->user()->can('canManageNestedData', User::class),
                                        ],
                                        'editRoute' => [
                                            'route' => 'apps.units.edit',
                                            'showIf' => Gate::allows('apps.units.edit'),
                                        ],
                                        'destroyRoute' => [
                                            'route' => 'apps.units.destroy',
                                            'showIf' => Gate::allows('apps.units.destroy') && $request->user()->can('isManager', User::class),
                                        ],
                                        'forceDestroyRoute' => [
                                            'route' => 'apps.roles.forcedestroy',
                                            'showIf' => Gate::allows('apps.roles.forcedestroy') && $request->user()->can('isSuperAdmin', User::class),
                                        ],
                                        'restoreRoute' => [
                                            'route' => 'apps.units.restore',
                                            'showIf' => Gate::allows('apps.units.restore') && $request->user()->can('isManager', User::class),
                                        ],
                                    ],
                                    'menu' => [
                                        [
                                            'icon' => 'mdi:source-branch-refresh',
                                            'title' => 'Refresh units hierarchy',
                                            'route' => 'apps.units.hierarchy',
                                            'method' => 'post',
                                            'showIf' => $request->user()->can('isSuperAdmin', User::class),
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
                                            'showIf' => $request->user()->can('canManageNestedData', User::class),
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Local staff',
                                            'field' => 'local_users',
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Total staff',
                                            'field' => 'all_users',
                                            'showIf' => $request->user()->can('canManageNestedData', User::class),
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
        $units = Unit::leftJoin('unit_user', 'unit_user.unit_id', '=', 'units.id')
            ->select('units.id', 'units.parent_id', 'units.shortpath AS name', 'units.active')
            ->groupBy('units.id', 'units.parent_id', 'name', 'units.active')
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());

                if ($request->user()->cannot('hasFullAccess', User::class)) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }
            })
            ->orderBy('units.parent_id')
            ->orderBy('units.order')
            ->get()
            ->map(function ($item) use ($unit) {
                $item->disabled = $item->active === true && $item->id != $unit->id ? false : true;

                return $item;
            });

        if ($units->pluck('id')->contains($unit->parent_id) === false && $unit->id != null) {
            $parent = Unit::where('id', $unit->parent_id)->first();

            if ($parent === null) {
                $units->prepend([
                    'id' => null,
                    'name' => '[ root ]',
                ]);
            } else {
                $units->prepend([
                    'id' => $parent->id,
                    'name' => $parent->getParentsNames(),
                ]);
            }
        }

        $unitsUsers = Unit::leftJoin('unit_user', 'unit_user.unit_id', '=', 'units.id')
            ->groupBy('units.id')
            ->select('units.id as unit_id')
            ->selectRaw('COUNT(unit_user.unit_id) as local_users')
            ->selectRaw("
                (SELECT COUNT(suu.unit_id) FROM unit_user suu 
                INNER JOIN units u ON u.id = suu.unit_id
                WHERE 
                    unit_id IN (
                        SELECT (json_array_elements(u.children_id::json)::text)::bigint FROM units u WHERE u.id = units.id
                    ) 
                    AND suu.primary = true
                ) as all_users
            ")
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                if ($request->user()->cannot('hasFullAccess', User::class)) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }

                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
            });

        $subunits = Unit::leftJoin('unit_user', 'unit_user.unit_id', '=', 'units.id')
            ->filter($request, 'subunits', [
                'where' => ['name'],
                'order' => ['shortpath']
            ])
            ->leftJoinSub($unitsUsers, 'units_users', function (JoinClause $join) {
                $join->on('units.id', '=', 'units_users.unit_id');
            })
            ->groupBy('units.id', 'units.shortpath', 'units_users.local_users', 'units_users.all_users')
            ->select('units.id', 'units.shortpath as name', 'units_users.local_users', 'units_users.all_users')
            ->withCount('children')
            ->where('unit_user.unit_id', '<>', $unit->id)
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                if ($request->user()->cannot('hasFullAccess', User::class)) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }

                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
            })
            ->paginate(20)
            ->onEachSide(2)
            ->withQueryString();

        $staff = User::filter($request, 'staff')
            ->leftJoin('unit_user', 'unit_user.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->when($unit['children_id'], function ($query) use ($unit) {
                $query->whereIn('unit_user.unit_id', json_decode($unit['children_id']));
            })
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                if ($request->user()->cannot('hasFullAccess', User::class)) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }

                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
            })
            ->with('unitsClassified', 'unitsWorking')
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
                            'title' => 'Belongs to',
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
                            'title' => 'Inactivated at',
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
                'showIf' => $unit->id != null && $request->user()->can('canManageNestedData', User::class),
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
                                        'showIf' => Gate::allows('apps.units.create') && $request->user()->can('isManager', User::class) && $request->user()->can('canManageNestedData', User::class),
                                    ],
                                    'editRoute' => [
                                        'route' => 'apps.units.edit',
                                        'showIf' => Gate::allows('apps.units.edit'),
                                    ],
                                    'destroyRoute' => [
                                        'route' => 'apps.units.destroy',
                                        'showIf' => Gate::allows('apps.units.destroy') && $request->user()->can('isManager', User::class),
                                    ],
                                    'forceDestroyRoute' => [
                                        'route' => 'apps.roles.forcedestroy',
                                        'showIf' => Gate::allows('apps.roles.forcedestroy') && $request->user()->can('isSuperAdmin', User::class),
                                    ],
                                    'restoreRoute' => [
                                        'route' => 'apps.units.restore',
                                        'showIf' => Gate::allows('apps.units.restore') && $request->user()->can('isManager', User::class),
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'text',
                                        'title' => 'Unit',
                                        'field' => 'name',
                                    ],
                                    [
                                        'type' => 'text',
                                        'title' => 'Subunits',
                                        'field' => 'children_count',
                                        'showIf' => $request->user()->can('canManageNestedData', User::class),
                                    ],
                                    [
                                        'type' => 'text',
                                        'title' => 'Local staff',
                                        'field' => 'local_users',
                                    ],
                                    [
                                        'type' => 'text',
                                        'title' => 'Total staff',
                                        'field' => 'all_users',
                                        'showIf' => $request->user()->can('canManageNestedData', User::class),
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
                'showIf' => $unit->id != null && Gate::allows('apps.users.index'),
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
                                        'type' => 'composite',
                                        'title' => 'Classified',
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
                                        'title' => 'Working',
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

    public function edit(Request $request, Unit $unit): Response
    {
        $this->authorize('access', User::class);
        $this->authorize('isActive', $unit);
        $this->authorize('canEdit', $unit);

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
        ]);
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
