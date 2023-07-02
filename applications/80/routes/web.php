<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\AppsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AuthorizationController;

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

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    Route::get('apps',  [AppsController::class, 'index'])->name('apps');
    Route::get('reports', [AppsController::class, 'index'])->name('reports');
    Route::get('help',  [AppsController::class, 'index'])->name('help');
});

Route::group(['middleware' => config('jetstream.middleware', ['web'])], function () {
  $authMiddleware = config('jetstream.guard')
          ? 'auth:'.config('jetstream.guard')
          : 'auth';

  $authSessionMiddleware = config('jetstream.auth_session', false)
          ? config('jetstream.auth_session')
          : null;

  Route::group(['middleware' => array_values(array_filter([$authMiddleware, $authSessionMiddleware]))], function () {
      Route::get('apps/users', [UsersController::class, 'index'])->name('apps/users');
  });

  Route::group(['middleware' => array_values(array_filter([$authMiddleware, $authSessionMiddleware]))], function () {
      Route::get('apps/authorization', [AuthorizationController::class, 'index'])->name('apps/authorization');
  });

  Route::group(['middleware' => array_values(array_filter([$authMiddleware, $authSessionMiddleware]))], function () {
      Route::get('apps/units', [UsersController::class, 'index'])->name('apps/units');
  });

  Route::group(['middleware' => array_values(array_filter([$authMiddleware, $authSessionMiddleware]))], function () {
      Route::get('apps/schedule', [UsersController::class, 'index'])->name('apps/schedule');
  });
});