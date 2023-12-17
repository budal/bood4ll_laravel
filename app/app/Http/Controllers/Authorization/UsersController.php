<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public $title = 'Users management';
    public $description = 'Manage users informations and authorizations.';

    public function index(Request $request, $mode = null): Response
    {
        $users = User::filter($request, 'users', [
            'where' => [
                'name',
                'email',
            ],
            ])
            ->when(!$request->user()->isSuperAdmin(), function ($query) use ($request) {
                $query->join('unit_user', 'unit_user.user_id', '=', 'users.id');

                if (!$request->user()->hasFullAccess()) {
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
                    'title' => $this->title,
                    'subtitle' => $this->description,
                    'fields' => [
                        [
                            [
                                'type' => 'table',
                                'name' => 'users',
                                'content' => [
                                    'routes' => [
                                        'createRoute' => [
                                            'route' => 'apps.users.create',
                                            'condition' => Gate::allows('apps.users.create'),
                                        ],
                                        'editRoute' => [
                                            'route' => 'apps.users.edit',
                                            'condition' => Gate::allows('apps.users.edit'),
                                        ],
                                        'destroyRoute' => [
                                            'route' => 'apps.users.destroy',
                                            'condition' => Gate::allows('apps.users.destroy'),
                                        ],
                                        'forceDestroyRoute' => [
                                            'route' => 'apps.users.forcedestroy',
                                            'condition' => Gate::allows('apps.users.forcedestroy'),
                                        ],
                                        'restoreRoute' => [
                                            'route' => 'apps.users.restore',
                                            'condition' => Gate::allows('apps.users.restore'),
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
                                            'condition' => Gate::allows('apps.users.change_user') && !$request->session()->has('previousUser'),
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

    public function __form(): array
    {
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
        $this->authorize('fullAccess', $user);
        $this->authorize('allowedUnits', $user);

        if (!$request->session()->has('previousUser')) {
            $request->session()->put('previousUser', [
                'id' => Auth::user()->id,
                'name' => Auth::user()->name,
            ]);

            Auth::loginUsingId($user->id, true);

            return Redirect::route('dashboard')->with([
                'toast_type' => 'warning',
                'toast_message' => "Logged as ':user'.",
                'toast_replacements' => ['user' => $user->name],
            ]);
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

    public function create(Request $request): Response
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
        $this->authorize('fullAccess', $user);
        $this->authorize('allowedUnits', $user);

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
