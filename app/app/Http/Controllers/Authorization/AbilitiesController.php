<?php

namespace App\Http\Controllers\Authorization;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Role;
use App\Models\Ability;

class AbilitiesController extends Controller
{
    public function index(Request $request): Response
    {
        $prefix = "apps";
        
        $routes = collect(Route::getRoutes())->filter(function ($route) use ($prefix) {
            return Str::startsWith($route->uri, $prefix);
        });
        
        $items = $routes->map(function ($route) use ($prefix) {
            $actionSegments = explode('\\', $route->action['controller']);
            $id = $route->action['as'];
            $title = $id . " (" . end($actionSegments) . ")";

            return compact('id', 'title');
        })->values()->toArray();

        usort($items, function($a, $b) {
            return $a['title'] <=> $b['title'];
        });
        
        $titles = [
            [
                'type' => 'simple',
                'title' => 'Ability',
                'field' => 'title',
            ],
            [
                'type' => 'switch',
                'title' => 'Active',
                'field' => 'id',
                'route' => 'apps.abilities.update',
                'method' => 'post',
            ],
        ];

        return Inertia::render('Default/Index', [
            'title' => "Abilities management",
            'subtitle' => "Set abilities to access specifics resources.",
            'softDelete' => Ability::hasGlobalScope('Illuminate\Database\Eloquent\SoftDeletingScope'),
            'routes' => [],
            'filters' => $request->all('search', 'sorted', 'trashed'),
            'titles' => $titles,
            'items' => ['data' => $items]
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

        // return Redirect::route('profile.edit');
        return Redirect::back()->with('status', 'User edited.');
    }
        
    public function upsert(ProfileUpdateRequest $request): RedirectResponse
    {
        // dd($request);
        
        // $request->user()->fill($request->validated());

        // if ($request->user()->isDirty('email')) {
        //     $request->user()->email_verified_at = null;
        // }

        // $request->user()->save();

        // return Redirect::route('profile.edit');
        return Redirect::back()->with('status', 'User edited.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $items = $request->all();

        try {
            $usersToDelete = User::whereIn('id', $items['ids'])->delete();
        } catch (Throwable $e) {
            report($e);
     
            return false;
        }
        
        return back()->with('status', 'Users removed succesfully!');
    }
}
