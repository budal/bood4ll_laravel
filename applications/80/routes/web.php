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
    Route::get('apps',  [AppsController::class, 'index'])->name('apps.index');
    Route::get('reports', [AppsController::class, 'index'])->name('reports.index');
    Route::get('help',  [AppsController::class, 'index'])->name('help.index');
});

Route::group(['prefix' => 'apps', 'middleware' => config('jetstream.middleware', ['web'])], function () {
  $authMiddleware = config('jetstream.guard') ? 'auth:'.config('jetstream.guard') : 'auth';
  $authSessionMiddleware = config('jetstream.auth_session', false) ? config('jetstream.auth_session') : null;

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