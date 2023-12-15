<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UsersController extends Controller
{
    public $title = 'Users management';
    public $description = 'Manage users informations and authorizations.';

    public function index(Request $request, $mode = null): Response
    {
        if ($request->user()->can('canManageNested', User::class)) {
            $units = Unit::whereIn('id', $request->user()->units()->get()->pluck('id'))->with('childrenRecursive')->get()->map->getAllChildren()->flatten()->pluck('id');
        } else {
            $units = $request->user()->units()->get()->flatten()->pluck('id');
        }

        $users = User::filter($request, 'users', [
            'where' => [
                'name',
                'email',
            ],
            ])->join('unit_user', 'unit_user.user_id', '=', 'users.id')

            ->whereIn('unit_user.unit_id', $units)
            ->with('unitsClassified', 'unitsWorking')
            ->withCount('roles')
            ->paginate(20)
            ->onEachSide(2)
            ->appends(collect($request->query)->toArray());

        // $usersAll = User::filter($request, 'users')
        //     // ->where('users.id', '9acb6259-970f-4c1e-b986-c5fb1fdb69d4')

        //     // ->where('users.id', '9ab675fd-9daa-4e72-a91b-fd0fcf974433')
        //     // ->select('users.*', 'unit_user.primary')
        //     ->select('users.id', 'users.name', 'users.email')
        //     ->selectSub(function ($query) {
        //         $query->from('users as users_classified')
        //         ->selectRaw("string_agg('id:' || units.id || ',' || 'name:' || units.shortpath, ';' ORDER BY units.shortpath)")

        //         ->leftjoin('unit_user', 'user_id', '=', 'users_classified.id')
        //         ->leftjoin('units', 'units.id', '=', 'unit_user.unit_id')

        //         ->where('unit_user.primary', '=', true)
        //         ->whereColumn('users_classified.id', 'users.id')
        //         ->take(1)
        //         ;
        //     }, 'unit_classified')

        //     ->leftjoin('unit_user', 'user_id', '=', 'users.id')
        //     ->leftjoin('units', 'units.id', '=', 'unit_user.unit_id')

        //     // ->with('unitsClassified', 'unitsWorking')

        //     ->withCount('roles')
        //     // ->groupBy('users.id', 'users.name', 'users.email')
        //     // ->paginate(20)
        //     // ->onEachSide(2)

        //     // ->get()

        //     // ->appends(collect($request->query)->toArray())
        // ;

        // $users = DB::connection('pgsql')
        //     ->query()
        //     ->fromSub($usersAll, 'users')
        //     ->select('id', 'name', 'email', 'unit_classified')
        //     ->groupBy('id', 'name', 'email', 'unit_classified')
        //     ->paginate(20)
        //     ->onEachSide(2)
        //     // ->appends(collect($request->query)->toArray())
        //     // ->get()
        // ;

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
                                            'condition' => $request->user()->isSuperAdmin(),
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

    public function changeUser(Request $request, User $user): RedirectResponse
    {
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
        dd($request->user()->can('units', User::class));

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
