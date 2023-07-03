<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\AppsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AuthorizationController;

use Laravel\Jetstream\Http\Controllers\CurrentTeamController;
use Laravel\Jetstream\Http\Controllers\Inertia\ApiTokenController;
use Laravel\Jetstream\Http\Controllers\Inertia\CurrentUserController;
use Laravel\Jetstream\Http\Controllers\Inertia\OtherBrowserSessionsController;
use Laravel\Jetstream\Http\Controllers\Inertia\PrivacyPolicyController;
use Laravel\Jetstream\Http\Controllers\Inertia\ProfilePhotoController;
use Laravel\Jetstream\Http\Controllers\Inertia\TeamController;
use Laravel\Jetstream\Http\Controllers\Inertia\TeamMemberController;
use Laravel\Jetstream\Http\Controllers\Inertia\TermsOfServiceController;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController;
use Laravel\Jetstream\Http\Controllers\TeamInvitationController;
use Laravel\Jetstream\Jetstream;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::group(['middleware' => config('jetstream.middleware', ['web'])], function () {
  if (Jetstream::hasTermsAndPrivacyPolicyFeature()) {
      Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.show');
      Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy.show');
  }
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    Route::get('apps',  [AppsController::class, 'index'])->name('apps.index');
    Route::get('reports', [AppsController::class, 'index'])->name('reports.index');
    Route::get('help',  [AppsController::class, 'index'])->name('help.index');
});

Route::group(['prefix' => 'apps', 'middleware' => config('jetstream.middleware', ['web'])], function () {
    $authMiddleware = config('jetstream.guard') 
        ? 'auth:'.config('jetstream.guard') 
        : 'auth';
    
    $authSessionMiddleware = config('jetstream.auth_session', false) 
        ? config('jetstream.auth_session') 
        : null;

    Route::group(['middleware' => array_values(array_filter([$authMiddleware, $authSessionMiddleware]))], function () {
      // User & Profile...
      Route::get('/user/profile', [UserProfileController::class, 'show'])
          ->name('profile.show');

      Route::delete('/user/other-browser-sessions', [OtherBrowserSessionsController::class, 'destroy'])
          ->name('other-browser-sessions.destroy');

      Route::delete('/user/profile-photo', [ProfilePhotoController::class, 'destroy'])
          ->name('current-user-photo.destroy');

      if (Jetstream::hasAccountDeletionFeatures()) {
          Route::delete('/user', [CurrentUserController::class, 'destroy'])
              ->name('current-user.destroy');
      }

      Route::group(['middleware' => 'verified'], function () {
          // API...
          if (Jetstream::hasApiFeatures()) {
              Route::get('/user/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
              Route::post('/user/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
              Route::put('/user/api-tokens/{token}', [ApiTokenController::class, 'update'])->name('api-tokens.update');
              Route::delete('/user/api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');
          }

          // Teams...
          if (Jetstream::hasTeamFeatures()) {
              Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
              Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
              Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
              Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
              Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
              Route::put('/current-team', [CurrentTeamController::class, 'update'])->name('current-team.update');
              Route::post('/teams/{team}/members', [TeamMemberController::class, 'store'])->name('team-members.store');
              Route::put('/teams/{team}/members/{user}', [TeamMemberController::class, 'update'])->name('team-members.update');
              Route::delete('/teams/{team}/members/{user}', [TeamMemberController::class, 'destroy'])->name('team-members.destroy');

              Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'accept'])
                  ->middleware(['signed'])
                  ->name('team-invitations.accept');

              Route::delete('/team-invitations/{invitation}', [TeamInvitationController::class, 'destroy'])
                  ->name('team-invitations.destroy');
          }
      });
  });

  Route::group(['prefix' => 'users', 'middleware' => array_values(array_filter([$authMiddleware, $authSessionMiddleware]))], function () {
      Route::get(null, [UsersController::class, 'index'])->name('apps.users.index');
      Route::get('create', [UsersController::class, 'create'])->name('apps.users.create');
      Route::post('{id}', [UsersController::class, 'store'])->name('apps.users.store');
      Route::get('{id}', [UsersController::class, 'edit'])->name('apps.users.edit');
      Route::put('{id}', [UsersController::class, 'update'])->name('apps.users.update');
      Route::delete('{id}', [UsersController::class, 'destroy'])->name('apps.users.delete');
      Route::put('{id}/restore', [UsersController::class, 'restore'])->name('apps.users.restore');
  });

  Route::group(['prefix' => 'authorization', 'middleware' => array_values(array_filter([$authMiddleware, $authSessionMiddleware]))], function () {
      Route::get('', [AuthorizationController::class, 'index'])->name('apps.authorization.index');
      Route::get('create', [AuthorizationController::class, 'create'])->name('apps.authorization.create');
      Route::post('{id}', [AuthorizationController::class, 'store'])->name('apps.authorization.store');
      Route::get('{id}', [AuthorizationController::class, 'edit'])->name('apps.authorization.edit');
      Route::put('{id}', [AuthorizationController::class, 'update'])->name('apps.authorization.update');
      Route::delete('{id}', [AuthorizationController::class, 'destroy'])->name('apps.authorization.delete');
      Route::put('{id}/restore', [AuthorizationController::class, 'restore'])->name('apps.authorization.restore');
  });

  Route::group(['prefix' => 'units', 'middleware' => array_values(array_filter([$authMiddleware, $authSessionMiddleware]))], function () {
      Route::get('', [UsersController::class, 'index'])->name('apps.units.index');
  });

  Route::group(['prefix' => 'schedule', 'middleware' => array_values(array_filter([$authMiddleware, $authSessionMiddleware]))], function () {
      Route::get('', [UsersController::class, 'index'])->name('apps.schedule.index');
  });
});