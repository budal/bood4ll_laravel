<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public $title = 'Users management';
    public $description = 'Manage users informations and authorizations.';

    public function index(Request $request, $mode = null): Response
    {
        // $user = Auth::user();
        // dd($user);

        $users = User::filter($request, 'users')
            // ->select("users.*", "users.name", "units.name as unit", "unit_user.primary")
            // ->select("users.*")

            // ->addSelect([
            //     // Key is the alias, value is the sub-select
            //     'unit' => User::query()
            //         // ->leftJoin('unit_user', 'unit_user.user_id', '=', 'users.id')
            //         ->select('units.name')
            //         ->where('id', 1)
            //         // ->latest()
            //         ->take(1)
            // ])
            // ->selectRaw("1 as unit")

            // ->leftJoin('unit_user', 'unit_user.user_id', '=', 'users.id')
            // ->leftJoin('units', 'unit_user.unit_id', '=', 'units.id')
            // ->where('unit_user.primary', true)

            // ->groupBy('users.id', 'users.name')

            ->with('unitsClassified', 'unitsWorking')
            ->withCount('roles')
            ->paginate(100)
            ->onEachSide(2)
            ->appends(collect($request->query)->toArray())

            // ->through(function ($item) {
            //     $item->parents = $item->getParentsNames();
            //     $item->all_users_count = $item->getAllChildren()->pluck('users_count')->sum() + $item->users_count;

            //     return $item;
            // });

            // ->transform(fn ($user) => [
            //     'id' => $user->id,
            //     'name' => $user->name,
            //     'email' => $user->email,
            //     'owner' => $user->owner,
            //     'photo' => $user->photo_path ? URL::route('image', ['path' => $user->photo_path, 'w' => 40, 'h' => 40, 'fit' => 'crop']) : null,
            //     'deleted_at' => $user->deleted_at,
            // ])
        ;

        // dd($users[1]);

        return Inertia::render('Default', [
            'form' => [
                [
                    'id' => 'users',
                    'title' => $this->title,
                    'subtitle' => $this->description,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'users',
                                'content' => [
                                    'routes' => [
                                        'createRoute' => 'apps.users.create',
                                        'editRoute' => 'apps.users.edit',
                                        'destroyRoute' => 'apps.users.destroy',
                                        'forceDestroyRoute' => 'apps.users.forcedestroy',
                                        'restoreRoute' => 'apps.users.restore',
                                    ],
                                    'menu' => [
                                        [
                                            'title' => '-',
                                        ],
                                        [
                                            'icon' => 'mdi:book-cog-outline',
                                            'title' => 'Log as another user',
                                            'route' => [
                                                'route' => 'apps.users.logas',
                                                'attributes' => 'logAs',
                                            ],
                                            'condition' => $mode !== 'logAs',
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
                                            'fields' => ['name', 'email'],
                                        ],
                                        [
                                            'type' => 'array',
                                            'title' => 'Unit',
                                            'class' => 'sm:hidden',
                                            'field' => 'units_classified',
                                            'options' => [
                                                [
                                                    'field' => 'name',
                                                    'class' => 'bold',
                                                ],
                                            ],
                                        ],
                                        [
                                            'type' => 'array',
                                            'title' => 'Unit',
                                            'class' => 'sm:hidden',
                                            'field' => 'units_working',
                                            'options' => [
                                                [
                                                    'field' => 'name',
                                                    'class' => 'bold',
                                                ],
                                            ],
                                        ],
                                        [
                                            'type' => 'text',
                                            'title' => 'Roles',
                                            'class' => 'sm:hidden',
                                            'field' => 'roles_count',
                                        ],
                                        [
                                            'type' => 'button',
                                            'title' => 'Login as',
                                            'theme' => 'warning',
                                            'condition' => $mode === 'logAs',
                                            'icon' => 'mdi:login',
                                            'disableSort' => true,
                                            'preserveScroll' => true,
                                            'route' => 'apps.users.loginas',
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

    public function __form(): array
    {
        $states = [
            [
                'id' => 'PR',
                'title' => 'Paraná',
                'disabled' => false,
            ],
            [
                'id' => 'SP',
                'title' => 'São Paulo',
                'disabled' => false,
            ],
            [
                'id' => 'RJ',
                'title' => 'Rio de Janeiro',
                'disabled' => false,
            ],
            [
                'id' => 'PI',
                'title' => 'Piauí',
                'disabled' => true,
            ],
            [
                'id' => 'SE',
                'title' => 'Sergipe',
                'disabled' => false,
            ],
        ];

        return [
            [
                'id' => 'profile',
                'title' => 'User profile Information',
                'subtitle' => 'User account profile information',
                'cols' => 2,
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
                    ],
                    [
                        [
                            'type' => 'input',
                            'name' => 'username',
                            'title' => 'Username',
                        ],
                        [
                            'type' => 'select',
                            'content' => $states,
                            'name' => 'state_birth',
                            'title' => 'State',
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

    public function changeUser(User $user): RedirectResponse
    {
        Auth::loginUsingId($user->id, true);

        return Redirect::back()->with([
            'toast_type' => 'warning',
            'toast_message' => "Logged as ':user'.",
            'toast_replacements' => ['user' => $user->name],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Default', [
            'form' => $this->__form(),
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

    public function edit(User $user): Response
    {
        return Inertia::render('Default', [
            'form' => $this->__form(),
            'routes' => [
                'profile' => [
                    'route' => route('apps.users.edit', $user->id),
                    'method' => 'patch',
                ],
            ],
            'data' => $user,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
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
}
