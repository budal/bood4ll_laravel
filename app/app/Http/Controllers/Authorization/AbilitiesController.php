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
        $prefixes = ["apps", "reports"];
        
        $routes = collect(Route::getRoutes())->filter(function ($route) use ($request, $prefixes) {
            return Str::contains($route->uri, $prefixes) && (
                $request->search ? Str::contains($route->uri, $request->search) : true
            );
        });

        $abilities = Ability::filter($request->all('search', 'sorted', 'trashed'))->get();

        $activatedAbilities = collect($abilities->pluck('name')->toArray());

        $items = $routes->map(function ($route) use ($activatedAbilities) {
            $actionSegments = explode('\\', $route->action['controller']);
            $id = $route->action['as'];
            $route = $route->uri;
            $command = end($actionSegments);
            $title = $id;
            $checked = $activatedAbilities->contains($id);

            return compact('id', 'route', 'command', 'title', 'checked');
        })->values()->toArray();

        usort($items, function($a, $b) {
            return $a['title'] <=> $b['title'];
        });
        
        $titles = [
            [
                'type' => 'composite',
                'title' => 'Ability',
                'field' => 'title',
                'fields' => ['title', 'command']
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
            'subtitle' => "Define which abilities will be showed in the roles management.",
            'softDelete' => Ability::hasGlobalScope('Illuminate\Database\Eloquent\SoftDeletingScope'),
            'routes' => [],
            'filters' => $request->all('search', 'sorted', 'trashed'),
            'titles' => $titles,
            'items' => ['data' => $items]
        ]);
    }
    
    public function update($ability): RedirectResponse
    {
        // dd($ability);
        
        // $request->user()->fill($request->validated());

        // if ($request->user()->isDirty('email')) {
        //     $request->user()->email_verified_at = null;
        // }

        // $request->user()->save();

        // return Redirect::route('profile.edit');
        return Redirect::back()->with('status', $ability);
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
