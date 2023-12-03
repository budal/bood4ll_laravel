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

use App\Models\User;

use App\Http\Requests\ProfileUpdateRequest;

class UsersController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Default/Index', [
            'title' => "Users management",
            'subtitle' => "Manage users informations and authorizations.",
            'routes' =>  [
                'createRoute' => "apps.users.create",
                'editRoute' => "apps.users.edit",
                'destroyRoute' => "apps.users.destroy",
                'forceDestroyRoute' => "apps.users.forcedestroy",
                'restoreRoute' => "apps.users.restore",
            ],
            'titles' => [
                [
                    'type' => 'avatar',
                    'title' => 'Avatar',
                    'field' => 'id',
                    'fallback' => 'name',
                    'disableSort' => true
                ],    
                [
                    'type' => 'composite',
                    'title' => 'User',
                    'field' => 'name',
                    'fields' => ['name', 'email']
                ],    
                [
                    'type' => 'simple',
                    'title' => 'Username',
                    'field' => 'username'
                ],    
                [
                    'type' => 'simple',
                    'title' => 'Active',
                    'field' => 'active'
                ]    
            ],
            'items' => User::filter($request->all('search', 'sorted', 'trashed'))
                ->sort($request->sorted ?? "name")
                ->paginate(20)
                ->onEachSide(2)
                ->appends($request->all('search', 'sorted', 'trashed'))
        ]);
    }

    public function __form()
    {
        $states = [
            [ 
                'id' => 'PR', 
                'title' => 'Paraná', 
                'disabled' => false 
            ],
            [ 
                'id' => 'SP', 
                'title' => 'São Paulo', 
                'disabled' => false 
            ],
            [ 
                'id' => 'RJ', 
                'title' => 'Rio de Janeiro', 
                'disabled' => false 
            ],
            [ 
                'id' => 'PI', 
                'title' => 'Piauí', 
                'disabled' => true 
            ],
            [ 
                'id' => 'SE', 
                'title' => 'Sergipe', 
                'disabled' => false 
            ],
        ];

        return [
            [
                'id' => "profile",
                'title' => "User profile Information",
                'subtitle' => "User account profile information",
                'cols' => 2,
                'fields' => [
                    [
                        [
                            'type' => "input",
                            'name' => "name",
                            'title' => "Name",
                        ],
                        [
                            'type' => "input",
                            'name' => "email",
                            'title' => "Email",
                        ],
                    ],
                    [
                        [
                            'type' => "input",
                            'name' => "username",
                            'title' => "Username",
                        ],
                        [
                            'type' => "select",
                            'content' => $states,
                            'name' => "state_birth",
                            'title' => "State",
                        ],
                    ],
                ]
            ]
        ];
    }

    public function create()
    {
        return Inertia::render('Default/Form', [
            'form' => $this->__form(),
            'routes' => [
                'profile' => [
                    'route' => route('apps.users.store'),
                    'method' => 'post'
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
            'toast_type' => "success",
            'toast_message' => "Item added.|Items added.",
            'toast_count' => 1,
        ]);
    }
    
    public function edit(User $user): Response
    {
        return Inertia::render('Default/Form', [
            'form' => $this->__form(),
            'routes' => [
                'profile' => [
                    'route' => route('apps.users.edit', $user->id),
                    'method' => 'patch'
                ],
            ],
            'data' => $user
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
            'toast_type' => "success",
            'toast_message' => "Item edited.|Items edited.",
            'toast_count' => 1,
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        try {
            $total = User::whereIn('id', $request->list)->delete();
            return back()->with([
                'toast_type' => "success",
                'toast_message' => "{0} Nothing to remove.|[1] Item removed successfully.|[2,*] :total items successfully removed.",
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total]
            ]);
        } catch (Throwable $e) {
            report($e);
            return back()->with([
                'toast_type' => "error",
                'toast_message' => "Error on remove selected item.|Error on remove selected items.",
                'toast_count' => count($request->list),
            ]);
        }
    }

    public function forceDestroy(Request $request): RedirectResponse
    {
        try {
            $total = User::whereIn('id', $request->list)->forceDelete();
            return back()->with([
                'toast_type' => "success",
                'toast_message' => "{0} Nothing to erase.|[1] Item erased successfully.|[2,*] :total items successfully erased.",
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total]
            ]);
        } catch (Throwable $e) {
            report($e);
            return back()->with([
                'toast_type' => "error",
                'toast_message' => "Error on erase selected item.|Error on erase selected items.",
                'toast_count' => count($request->list),
            ]);
        }
    }

    public function restore(Request $request): RedirectResponse
    {
        try {
            $total = User::whereIn('id', $request->list)->restore();
            return back()->with([
                'toast_type' => "success",
                'toast_message' => "{0} Nothing to restore.|[1] Item restored successfully.|[2,*] :total items successfully restored.",
                'toast_count' => $total,
                'toast_replacements' => ['total' => $total]
            ]);
        } catch (Throwable $e) {
            report($e);
            return back()->with([
                'toast_type' => "error",
                'toast_message' => "Error on restore selected item.|Error on restore selected items.",
                'toast_count' => count($request->list),
            ]);
        }
    }
}
