<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

use App\Models\User;

class UnitsController extends Controller
{
    public function index(Request $request): Response
    {
        $items = (new User)->newQuery();
        
        if (request()->has('search')) {
            $items->where('name', 'ilike', '%'.request()->input('search').'%')
                ->orWhere('username', 'ilike', '%'.$search.'%')
                ->orWhere('email', 'ilike', '%'.$search.'%');
        }
        
        if (request()->query('sort')) {
            $attribute = request()->query('sort');
            $sort_order = 'ASC';

            if (strncmp($attribute, '-', 1) === 0) {
                $sort_order = 'DESC';
                $attribute = substr($attribute, 1);
            }

            $items->orderBy($attribute, $sort_order);
        } else {
            $items->orderBy('name');
        }

        $items = $items->paginate(20)
            ->onEachSide(2)
            ->appends(request()->query());

            // ->withQueryString()

        // dd($items);

        // return Inertia::render('Admin/Permission/Index', [
        //     'items' => $items,
        //     'filters' => request()->all('search'),
        // ]);


        // 'items' => User::orderBy('name')
        // ->filter($request->all('search'))
        // ->paginate(20)
        // ->appends($request->all('search', 'active'))

         
        return Inertia::render('Users/Index', [
            'status' => session('status'),
            'filters' => $request->all('search'),
            'titles' => [
                [
                    'type' => 'avatar',
                    'title' => 'Avatar',
                    'field' => 'id',
                    'fallback' => 'name'
                ],
                [
                    'type' => 'composite',
                    'title' => 'User',
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
                ],
            ],
            'items' => $items
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('Users/Edit', [
            'data' => $user
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $items = $request->all();

        try {
            $usersToDelete = User::whereIn('id', $items['ids'])->delete();
        } catch (Throwable $e) {
            report($e);
     
            return false;
        }
        
        // dd($usersToDelete);
        // $request->validate([
        //     'password' => ['required', 'current_password'],
        // ]);

        // $user = $request->user();

        // Auth::logout();

        // $user->delete();

        // $request->session()->invalidate();
        // $request->session()->regenerateToken();

        // return Redirect::to('/');
        return back()->withInput()->with('status', 'Users removed succesfully!');
    }
}
