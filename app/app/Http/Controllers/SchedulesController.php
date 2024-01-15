<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchedulesRequest;
use App\Models\Ability;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class SchedulesController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('access', User::class);

        $schedules = Schedule::filter($request, 'schedules')
            ->leftjoin('schedule_user', 'schedule_user.schedule_id', '=', 'schedules.id')
            ->select('schedules.id', 'schedules.name', 'schedules.description', 'schedules.active', 'schedules.starts_at', 'schedules.ends_at', 'schedules.deleted_at')
            ->groupBy('schedules.id', 'schedules.name', 'schedules.description', 'schedules.active', 'schedules.starts_at', 'schedules.ends_at', 'schedules.deleted_at')
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                $query->where('schedules.active', true);
                $query->where('schedule_user.user_id', $request->user()->id);
            })
            ->withCount([
                'users' => function ($query) use ($request) {
                    $query->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                        $query->join('unit_user', function (JoinClause $join) use ($request, $query) {
                            $join->on('unit_user.user_id', '=', 'schedule_user.user_id')
                                ->whereIn('unit_user.unit_id', $request->user()->unitsIds());

                            if ($request->user()->cannot('hasFullAccess', User::class)) {
                                $query->where('unit_user.user_id', $request->user()->id);
                            }
                        });
                    });
                }
            ])
            ->paginate(20)
            ->onEachSide(2)
            ->appends(collect($request->query)->toArray());

        return Inertia::render('Default', [
            'form' => [
                [
                    'id' => 'schedules',
                    'title' => Route::current()->title,
                    'subtitle' => Route::current()->description,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'schedules',
                                'content' => [
                                    'routes' => [
                                        'createRoute' => [
                                            'route' => 'apps.schedules.create',
                                            'showIf' => Gate::allows('apps.schedules.create') && $request->user()->can('isManager', User::class),
                                        ],
                                        'editRoute' => [
                                            'route' => 'apps.schedules.edit',
                                            'showIf' => Gate::allows('apps.schedules.edit')
                                        ],
                                        'destroyRoute' => [
                                            'route' => 'apps.schedules.destroy',
                                            'showIf' => Gate::allows('apps.schedules.destroy') && $request->user()->can('isManager', User::class),
                                        ],
                                        'forceDestroyRoute' => [
                                            'route' => 'apps.schedules.forcedestroy',
                                            'showIf' => Gate::allows('apps.schedules.forcedestroy') && $request->user()->can('isSuperAdmin', User::class),
                                        ],
                                        'restoreRoute' => [
                                            'route' => 'apps.schedules.restore',
                                            'showIf' => Gate::allows('apps.schedules.restore') && $request->user()->can('isManager', User::class),
                                        ],
                                    ],
                                    'titles' => [
                                        [
                                            'type' => 'composite',
                                            'title' => 'Schedule',
                                            'field' => 'name',
                                            'values' => [
                                                [
                                                    'field' => 'name',
                                                ],
                                                [
                                                    'field' => 'description',
                                                    'class' => 'text-xs',
                                                ],
                                            ],
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Abilities',
                                            'field' => 'abilities_count',
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Users',
                                            'field' => 'users_count',
                                        ],
                                    ],
                                    'items' => $schedules,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function __form(Request $request, Schedule $role): array
    {
        $abilities = Ability::select('abilities.*')
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                $query->whereIn('name', $request->user()->getAllAbilities->whereNotNull('ability')->pluck('ability'));
            })
            ->orderBy('name')
            ->get();

        $users = User::filter($request, 'users')
            ->leftjoin('unit_user', 'unit_user.user_id', '=', 'users.id')
            ->leftjoin('role_user', 'role_user.user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->when(
                $request->show == 'all',
                function () {
                },
                function ($query) use ($role) {
                    $query->where('role_user.role_id', $role->id);
                }
            )
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                if ($request->user()->cannot('hasFullAccess', User::class)) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }
                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
            })
            ->with('unitsClassified', 'unitsWorking')
            ->paginate(20)
            ->onEachSide(2)
            ->appends(collect($request->query)->toArray())
            ->through(function ($item) use ($role) {
                $item->checked = $item->schedules->pluck('id')->contains($role->id);

                return $item;
            });

        return [
            [
                'id' => 'role',
                'title' => 'Main data',
                'subtitle' => 'Schedule name, abilities and settings',
                'showIf' => $role->id === null || $request->user()->can('isOwner', $role),
                'disabledIf' => $role->inalterable == true || $role->id !== null && $request->user()->cannot('isOwner', $role),
                'cols' => 3,
                'fields' => [
                    [
                        [
                            'type' => 'input',
                            'name' => 'name',
                            'title' => 'Name',
                            'required' => true,
                            'autofocus' => true,
                        ],
                        [
                            'type' => 'input',
                            'name' => 'description',
                            'title' => 'Description',
                            'span' => 2,
                        ],
                        [
                            'type' => 'select',
                            'name' => 'abilities',
                            'title' => 'Abilities',
                            'span' => 3,
                            'content' => $abilities,
                            'required' => true,
                            'multiple' => true,
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'active',
                            'title' => 'Active',
                            'colorOn' => 'success',
                            'colorOff' => 'danger',
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'lock_on_expire',
                            'title' => 'Lock on expire',
                            'colorOn' => 'info',
                        ],
                        [
                            'type' => 'date',
                            'name' => 'expires_at',
                            'title' => 'Expires at',
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'full_access',
                            'title' => 'Full access',
                            'disabled' => $request->user()->cannot('hasFullAccess', User::class),
                            'colorOn' => 'info',
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'manage_nested',
                            'title' => 'Manage nested data',
                            'disabled' => $request->user()->cannot('canManageNestedData', User::class),
                            'colorOn' => 'info',
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'remove_on_change_unit',
                            'title' => 'Remove on transfer',
                            'disabled' => $request->user()->can('canRemoveOnChangeUnit', User::class) && $request->user()->cannot('isSuperAdmin', User::class),
                            'colorOn' => 'info',
                        ],
                    ],
                ],
            ],
            [
                'id' => 'users',
                'title' => 'Authorizations',
                'subtitle' => 'Define which users will have access to this authorization',
                'showIf' => $role->id != null,
                'fields' => [
                    [
                        [
                            'type' => 'table',
                            'name' => 'users',
                            'content' => [
                                'routes' => [
                                    'showCheckboxes' => true,
                                ],
                                'menu' => [
                                    [
                                        'icon' => 'mdi:plus-circle-outline',
                                        'title' => 'Authorize',
                                        'route' => [
                                            'route' => 'apps.schedules.authorization',
                                            'attributes' => [
                                                $role->id,
                                                'on',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'list' => 'checkboxes',
                                        'listCondition' => false,
                                        'modalTitle' => 'Are you sure you want to authorize the selected users?|Are you sure you want to authorize the selected users?',
                                        'modalSubTitle' => 'The selected user will have the rights to access this role. Do you want to continue?|The selected user will have the rights to access this role. Do you want to continue?',
                                        'buttonTitle' => 'Authorize',
                                        'buttonIcon' => 'mdi:plus-circle-outline',
                                        'buttonColor' => 'success',
                                    ],
                                    [
                                        'icon' => 'mdi:minus-circle-outline',
                                        'title' => 'Deauthorize',
                                        'route' => [
                                            'route' => 'apps.schedules.authorization',
                                            'attributes' => [
                                                $role->id,
                                                'off',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'list' => 'checkboxes',
                                        'listCondition' => true,
                                        'modalTitle' => 'Are you sure you want to deauthorize the selected users?|Are you sure you want to deauthorize the selected users?',
                                        'modalSubTitle' => 'The selected user will lose the rights to access this role. Do you want to continue?|The selected users will lose the rights to access this role. Do you want to continue?',
                                        'buttonTitle' => 'Deauthorize',
                                        'buttonIcon' => 'mdi:minus-circle-outline',
                                        'buttonColor' => 'danger',
                                    ],
                                    [
                                        'title' => '-',
                                    ],

                                    [
                                        'icon' => 'mdi:format-list-checkbox',
                                        'title' => 'List',
                                        'items' => [
                                            [
                                                'icon' => 'mdi:account-key-outline',
                                                'title' => 'Authorized users',
                                                'route' => [
                                                    'route' => 'apps.schedules.edit',
                                                    'attributes' => $role->id,
                                                ],
                                            ],
                                            [
                                                'icon' => 'mdi:account-multiple-outline',
                                                'title' => 'All users',
                                                'route' => [
                                                    'route' => 'apps.schedules.edit',
                                                    'attributes' => [$role->id, 'all'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'titles' => [
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
                                        'type' => 'toggle',
                                        'title' => '',
                                        'field' => 'checked',
                                        'disableSort' => true,
                                        'route' => [
                                            'route' => 'apps.schedules.authorization',
                                            'attributes' => [
                                                $role->id,
                                                'toggle',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'colorOn' => 'info',
                                    ],
                                ],
                                'items' => $users,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function authorization(Request $request, Schedule $role, $mode): RedirectResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('fullAccess', [$role, $request]);

        $hasSchedule = $role->users()->whereIn('user_id', $request->list)->first();

        try {
            if ($mode == 'toggle') {
                $user = User::whereIn('id', $request->list)->first();
                $hasSchedule ? $role->users()->detach($request->list) : $role->users()->attach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => $hasSchedule
                        ? "The user ':user' has been disabled in the ':role' role."
                        : "The user ':user' was enabled in the ':role' role.",
                    'toast_replacements' => ['user' => $user->name, 'role' => $role->name],
                ]);
            } elseif ($mode == 'on') {
                $total = $role->users()->attach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => '{0} Nobody to authorize.|[1] User successfully authorized.|[2,*] :total users successfully authorized.',
                    'toast_count' => count($request->list),
                    'toast_replacements' => ['total' => count($request->list)],
                ]);
            } elseif ($mode == 'off') {
                $total = $role->users()->detach($request->list);

                return Redirect::back()->with([
                    'toast_type' => 'success',
                    'toast_message' => '{0} Nobody to deauthorize.|[1] User successfully deauthorized.|[2,*] :total users successfully deauthorized.',
                    'toast_count' => $total,
                    'toast_replacements' => ['total' => $total],
                ]);
            }
        } catch (\Throwable $e) {
            report($e);

            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on edit selected item.|Error on edit selected items.',
                'toast_count' => count($request->list),
            ]);
        }
    }

    public function create(Request $request, Schedule $role): Response
    {
        $this->authorize('access', User::class);
        $this->authorize('isManager', User::class);

        return Inertia::render('Default', [
            'form' => $this->__form($request, $role),
            'routes' => [
                'role' => [
                    'route' => route('apps.schedules.store'),
                    'method' => 'post',
                ],
            ],
            'data' => [
                'active' => true,
                'remove_on_change_unit' => true,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('isManager', User::class);

        $abilities = collect($request->abilities)->pluck('id');

        DB::beginTransaction();

        try {
            $role = new Schedule();

            $role->name = $request->name;
            $role->description = $request->description;
            $role->owner = $request->user()->id;
            $role->active = $request->active;
            $role->lock_on_expire = $request->lock_on_expire;
            $role->expires_at = $request->expires_at;
            $role->full_access = $request->full_access;
            $role->manage_nested = $request->manage_nested;
            $role->remove_on_change_unit = $request->remove_on_change_unit;

            $role->save();

            try {
                $role->users()->attach($request->user()->id);
                $role->abilities()->sync($abilities);
            } catch (\Exception $e) {
                report($e);

                DB::rollback();

                return Redirect::back()->with([
                    'toast_type' => 'error',
                    'toast_message' => 'Error when syncing abilities to the role.',
                ]);
            }
        } catch (\Throwable $e) {
            report($e);

            DB::rollback();

            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'Error on add this item.|Error on add the items.',
                'toast_count' => 1,
            ]);
        }

        DB::commit();

        return Redirect::route('apps.schedules.edit', $role->id)->with([
            'toast_type' => 'success',
            'toast_message' => '{0} Nothing to add.|[1] Item added successfully.|[2,*] :total items successfully added.',
            'toast_count' => 1,
        ]);
    }

    public function edit(Request $request, Schedule $role): Response
    {
        $this->authorize('access', User::class);
        $this->authorize('isActive', $role);
        $this->authorize('canEdit', $role);
        $this->authorize('canEditManagementSchedules', $role);

        $role['abilities'] = $role->abilities;

        return Inertia::render('Default', [
            'form' => $this->__form($request, $role),
            'routes' => [
                'role' => [
                    'route' => route('apps.schedules.edit', $role->id),
                    'method' => 'patch',
                    'reset' => true,
                    'fieldsToReset' => ['expires_at'],
                ],
            ],
            'data' => $role,
        ]);
    }

    public function update(Request $request, Schedule $role): RedirectResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('isActive', $role);
        $this->authorize('isManager', User::class);
        $this->authorize('canEdit', $role);
        $this->authorize('canEditManagementSchedules', $role);
        $this->authorize('isOwner', $role);

        $abilities = collect($request->abilities)->pluck('id');

        DB::beginTransaction();

        try {
            if ($request->lock_on_expire && !$request->expires_at) {
                return Redirect::back()->with([
                    'toast_type' => 'error',
                    'toast_message' => 'Define the expiration date.',
                ]);
            }

            if ($request->manage_nested && !$request->full_access) {
                return Redirect::back()->with([
                    'toast_type' => 'error',
                    'toast_message' => "It is impossible to manage nested data without enabling 'full access'.",
                ]);
            }

            $role->name = $request->name;
            $role->description = $request->description;
            $role->active = $request->active;
            $role->lock_on_expire = $request->lock_on_expire ? $request->lock_on_expire : false;
            $role->expires_at = $request->lock_on_expire ? $request->expires_at : null;
            $role->full_access = $request->full_access;
            $role->manage_nested = $request->manage_nested;
            $role->remove_on_change_unit = $request->remove_on_change_unit;

            $role->save();

            try {
                $role->abilities()->sync($abilities);
            } catch (\Exception $e) {
                report($e);

                DB::rollback();

                return Redirect::back()->with([
                    'toast_type' => 'error',
                    'toast_message' => 'Error when syncing abilities to the role.',
                ]);
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
        $this->authorize('canDestroyOrRestore', [Schedule::class, $request]);

        try {
            $total = Schedule::whereIn('id', $request->list)
                ->where(function ($query) {
                    $query->where('inalterable', null);
                    $query->orWhere('inalterable', false);
                })->delete();

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
            $total = Schedule::whereIn('id', $request->list)
                ->where(function ($query) {
                    $query->where('inalterable', null);
                    $query->orWhere('inalterable', false);
                })->forceDelete();

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
        $this->authorize('canDestroyOrRestore', [Schedule::class, $request]);

        try {
            $total = Schedule::whereIn('id', $request->list)->restore();

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
