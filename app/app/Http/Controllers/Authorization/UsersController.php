<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Role;
use App\Models\User;
use Emargareten\InertiaModal\Modal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('access', User::class);

        $users = User::filter($request, 'users', [
            'where' => [
                'name',
                'email',
            ],
        ])
            ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
                $query->join('unit_user', 'unit_user.user_id', '=', 'users.id');
                $query->select('users.id', 'users.name', 'users.email');
                $query->groupBy('users.id', 'users.name', 'users.email');

                if ($request->user()->cannot('hasFullAccess', User::class)) {
                    $query->where('unit_user.user_id', $request->user()->id);
                }

                $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
            })
            ->with('unitsClassified', 'unitsWorking')
            ->withCount('roles')
            ->paginate(20)
            ->onEachSide(2)
            ->appends(collect($request->query)->toArray());

        return Inertia::render('Default', [
            'form' => [
                [
                    'id' => 'users',
                    'title' => Route::current()->title,
                    'subtitle' => Route::current()->description,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'users',
                                'content' => [
                                    'routes' => [
                                        'createRoute' => [
                                            'route' => 'apps.users.create',
                                            'showIf' => Gate::allows('apps.users.create'),
                                        ],
                                        'editRoute' => [
                                            'route' => 'apps.users.edit',
                                            'showIf' => Gate::allows('apps.users.edit'),
                                        ],
                                        'destroyRoute' => [
                                            'route' => 'apps.users.destroy',
                                            'showIf' => Gate::allows('apps.users.destroy'),
                                        ],
                                        'forceDestroyRoute' => [
                                            'route' => 'apps.users.forcedestroy',
                                            'showIf' => Gate::allows('apps.users.forcedestroy'),
                                        ],
                                        'restoreRoute' => [
                                            'route' => 'apps.users.restore',
                                            'showIf' => Gate::allows('apps.users.restore'),
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
                                            'class' => 'collapse',
                                            'field' => 'roles_count',
                                        ],
                                        [
                                            'type' => 'button',
                                            'title' => 'Login as',
                                            'theme' => 'warning',
                                            'showIf' => Gate::allows('apps.users.change_user') && !$request->session()->has('previousUser'),
                                            'icon' => 'mdi:login',
                                            'disableSort' => true,
                                            'preserveScroll' => true,
                                            'route' => 'apps.users.change_user',
                                            'method' => 'post',
                                        ],
                                    ],
                                    'items' => $users,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function __form(Request $request, User $user): array
    {
        $units = $user->units()
            ->filter($request, 'units')
            ->paginate($perPage = 20, $columns = ['*'], $pageName = 'units')
            ->onEachSide(2)
            ->withQueryString();

        $roles = Role::filter($request, 'roles')
            ->select('roles.id', 'roles.name')
            ->when(!$request->all, function ($query) use ($user) {
                $query->join('role_user', 'role_user.role_id', '=', 'roles.id');
                $query->where('role_user.user_id', $user->id);
            })
            // ->when($request->user()->cannot('isSuperAdmin', User::class), function ($query) use ($request) {
            //     if ($request->user()->cannot('hasFullAccess', User::class)) {
            //         $query->where('unit_user.user_id', $request->user()->id);
            //     }
            //     $query->whereIn('unit_user.unit_id', $request->user()->unitsIds());
            // })
            ->paginate($perPage = 20, $columns = ['*'], $pageName = 'roles')
            ->onEachSide(2)
            ->withQueryString()
            ->through(function ($item) use ($user) {
                $item->checked = $item->users->pluck('id')->contains($user->id);

                return $item;
            });

        return [
            [
                'id' => 'profile',
                'title' => 'Main data',
                'subtitle' => 'User account profile information',
                'cols' => 3,
                'fields' => [
                    [
                        [
                            'type' => 'input',
                            'name' => 'name',
                            'title' => 'Name',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'email',
                            'title' => 'Email',
                        ],
                        [
                            'type' => 'toggle',
                            'name' => 'active',
                            'title' => 'Active',
                            'colorOn' => 'success',
                            'colorOff' => 'danger',
                        ],
                    ],
                    [
                        [
                            'type' => 'date',
                            'name' => 'birthday',
                            'title' => 'Birthday',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'naturalness',
                            'title' => 'Naturalness',
                        ],
                        [
                            'type' => 'input',
                            'name' => 'nationality',
                            'title' => 'Nationality',
                        ],
                    ]
                ],
                [
                    [
                        'type' => 'date',
                        'name' => 'birthday',
                        'title' => 'Birthday',
                    ],
                    [
                        'type' => 'input',
                        'name' => 'naturalness',
                        'title' => 'Naturalness',
                    ],
                    [
                        'type' => 'input',
                        'name' => 'nationality',
                        'title' => 'Nationality',
                    ],
                ]
            ],
            [
                'id' => 'units',
                'title' => 'Units',
                'subtitle' => "Users' units management.",
                'showIf' => $user->id != null && $request->user()->can('canManageNestedData', User::class),
                'fields' => [
                    [
                        [
                            'type' => 'table',
                            'name' => 'units',
                            'content' => [
                                'routes' => [
                                    'createRoute' => [
                                        'route' => 'apps.units.create',
                                        'attributes' => $user->id,
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
                                        'title' => 'Name',
                                        'field' => 'shortpath',
                                    ],
                                    [
                                        'type' => 'text',
                                        'title' => 'Classified',
                                        'field' => 'primary',
                                    ],
                                    [
                                        'type' => 'text',
                                        'title' => 'Working',
                                        'field' => 'temporary',
                                    ],
                                ],
                                'items' => $units,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'roles',
                'title' => 'Roles',
                'subtitle' => "Users' roles management.",
                'showIf' => $user->id != null && $request->user()->can('canManageNestedData', User::class),
                'fields' => [
                    [
                        [
                            'type' => 'table',
                            'name' => 'subunits',
                            'content' => [
                                'routes' => [
                                    'showCheckboxes' => true,
                                ],
                                'menu' => [
                                    [
                                        'icon' => 'mdi:plus-circle-outline',
                                        'title' => 'Authorize',
                                        'route' => [
                                            'route' => 'apps.roles.authorization',
                                            'attributes' => [
                                                $user->id,
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
                                            'route' => 'apps.roles.authorization',
                                            'attributes' => [
                                                $user->id,
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
                                                    'route' => 'apps.users.edit',
                                                    'attributes' => $user->id,
                                                ],
                                            ],
                                            [
                                                'icon' => 'mdi:account-multiple-outline',
                                                'title' => 'All users',
                                                'route' => [
                                                    'route' => 'apps.users.edit',
                                                    'attributes' => [$user->id, 'all'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'text',
                                        'title' => 'Name',
                                        'field' => 'name',
                                    ],
                                    [
                                        'type' => 'toggle',
                                        'title' => '',
                                        'field' => 'checked',
                                        'disableSort' => true,
                                        'route' => [
                                            'route' => 'apps.roles.authorization',
                                            'attributes' => [
                                                $user->id,
                                                'toggle',
                                            ],
                                        ],
                                        'method' => 'post',
                                        'colorOn' => 'info',
                                    ],
                                ],
                                'items' => $roles,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function activate(): RedirectResponse
    {
        return Redirect::back()->with('status', 'Error on edit selected item.|Error on edit selected items.');
    }

    public function changeUser(Request $request, User $user): RedirectResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('fullAccess', $user);
        $this->authorize('allowedUnits', $user);

        if (!$request->session()->has('previousUser')) {
            $request->session()->put('previousUser', [
                'id' => Auth::user()->id,
                'name' => Auth::user()->name,
            ]);

            Auth::loginUsingId($user->id, true);

            if ($user->getAllAbilities->whereNotNull('ability')->pluck('ability')->contains(Route::current()->getName())) {
                return Redirect::back()->with([
                    'toast_type' => 'warning',
                    'toast_message' => "Logged as ':user'.",
                    'toast_replacements' => ['user' => $user->name],
                ]);
            } else {
                return Redirect::route('dashboard')->with([
                    'toast_type' => 'warning',
                    'toast_message' => "Logged as ':user'.",
                    'toast_replacements' => ['user' => $user->name],
                ]);
            }
        } else {
            return Redirect::back()->with([
                'toast_type' => 'error',
                'toast_message' => 'This action is unauthorized.',
            ]);
        }
    }

    public function returnToMyUser(Request $request): RedirectResponse
    {
        $previousUser = $request->session()->all()['previousUser'];

        $request->session()->put('previousUser', [
            'id' => Auth::user()->id,
            'name' => Auth::user()->name,
        ]);

        Auth::loginUsingId($previousUser['id'], true);

        $request->session()->forget('previousUser');

        return Redirect::back()->with([
            'toast_type' => 'info',
            'toast_message' => "Logged as ':user'.",
            'toast_replacements' => ['user' => $previousUser['name']],
        ]);
    }

    public function create(Request $request, User $user): Response
    {
        $this->authorize('access', User::class);

        return Inertia::render('Default', [
            'form' => $this->__form($request, $user),
            'routes' => [
                'profile' => [
                    'route' => route('apps.users.store'),
                    'method' => 'post',
                ],
            ],
        ]);
    }

    public function store(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->authorize('access', User::class);

        // dd($request);
        // $request->user()->fill($request->validated());

        // if ($request->user()->isDirty('email')) {
        //     $request->user()->email_verified_at = null;
        // }

        // $request->user()->save();

        return Redirect::route('apps.users.index')->with([
            'toast_type' => 'success',
            'toast_message' => 'Item added.|Items added.',
            'toast_count' => 1,
        ]);
    }

    public function edit(Request $request, User $user): Response
    {
        $this->authorize('access', User::class);
        $this->authorize('fullAccess', $user);
        $this->authorize('allowedUnits', $user);

        return Inertia::render('Default', [
            'form' => $this->__form($request, $user),
            'routes' => [
                'profile' => [
                    'route' => route('apps.users.edit', $user->id),
                    'method' => 'patch',
                ],
            ],
            'data' => $user,
        ]);
    }

    public function update(ProfileUpdateRequest $request, User $user): RedirectResponse
    {
        $this->authorize('access', User::class);
        $this->authorize('fullAccess', $user);
        $this->authorize('allowedUnits', $user);

        // dd($request);
        // $request->user()->fill($request->validated());

        // if ($request->user()->isDirty('email')) {
        //     $request->user()->email_verified_at = null;
        // }

        // $request->user()->save();

        return Redirect::back()->with([
            'toast_type' => 'success',
            'toast_message' => 'Item edited.|Items edited.',
            'toast_count' => 1,
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        try {
            $total = User::whereIn('id', $request->list)->delete();

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
        try {
            $total = User::whereIn('id', $request->list)->forceDelete();

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
                'toast_count' => count($request->list),
            ]);
        }
    }

    public function restore(Request $request): RedirectResponse
    {
        try {
            $total = User::whereIn('id', $request->list)->restore();

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
                'toast_count' => count($request->list),
            ]);
        }
    }


    public function __formModal(Request $request, Role $role): array
    {
        $abilities = Ability::sort('name')->get()->map->only(['id', 'name']);

        $users = User::paginate(5)
            ->onEachSide(1)
            ->appends($request->all('users_search', 'users_sorted', 'users_trashed'));

        return [
            [
                'id' => 'users',
                'fields' => [
                    [
                        [
                            'type' => 'table',
                            'name' => 'users',
                            'span' => 2,
                            'content' => [
                                'menu' => [
                                    [
                                        'icon' => 'mdi:book-cog-outline',
                                        'title' => 'Abilities management',
                                        'route' => 'apps.abilities.index',
                                    ],
                                ],
                                'titles' => [
                                    [
                                        'type' => 'text',
                                        'title' => 'Name',
                                        'field' => 'name',
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

    public function adduser(Request $request, Role $role): Modal
    {
        $role['abilities'] = $role->getAllAbilities()->get()->map->only('id')->pluck('id');

        return Inertia::modal('Default', [
            'form' => $this->__formModal($request, $role),
            'isModal' => true,
            'title' => 'Define the users who have access to this authorization',
            'routes' => [
                'role' => [
                    'route' => route('apps.roles.edit', $role->id),
                    'method' => 'patch',
                ],
            ],
            'data' => $role,
        ])
            ->baseRoute('apps.roles.edit', $role)
            // ->refreshBackdrop()
        ;
    }
}
