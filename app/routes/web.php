<?php

use App\Http\Controllers\Authorization\AbilitiesController;
use App\Http\Controllers\Authorization\RolesController;
use App\Http\Controllers\Authorization\UnitsController;
use App\Http\Controllers\Authorization\UsersController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
})->name('home')->breadcrumb('Home');

Route::get('/email/verify', function () {
    return Inertia::render('Auth/VerifyEmail');
})->middleware('auth')->name('verification.notice')->breadcrumb('Verify EMail');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/dashboard');
})->middleware(['auth', 'signed'])->name('verification.verify')->breadcrumb('Verify EMail');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard')->breadcrumb('Dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit')->breadcrumb('Profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/messages', [ProfileController::class, 'edit'])->name('messages')->breadcrumb('Messages');

    Route::get('/schedule', [ProfileController::class, 'edit'])->name('schedule')->breadcrumb('Schedule');

    Route::get('/settings', [ProfileController::class, 'edit'])->name('settings')->breadcrumb('Settings');

    Route::prefix('apps')->name('apps.')->group(function () {
        Route::controller(UsersController::class)->group(function () {
            Route::name('users.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/users', 'index')->name('index')->breadcrumb('Users');
                Route::post('/users/changeuser/{user}', 'changeUser')->name('change_user')->whereUuid('user');
                Route::post('/users/returnToMyUser', 'returnToMyUser')->name('return_to_my_user')->whereUuid('user');
                Route::post('/users/activate/{user}/{mode?}', 'activate')->name('activate')->whereUuid('user');
                Route::get('/users/create', 'create')->name('create')->breadcrumb('User creation', 'apps.users.index');
                Route::post('/users/create', 'store')->name('store');
                Route::get('/users/edit/{user}', 'edit')->name('edit')->whereUuid('user')->breadcrumb('User edition', 'apps.users.index');
                Route::patch('/users/edit/{user}', 'update')->name('update')->whereUuid('user');
                Route::delete('/users/destroy', 'destroy')->name('destroy');
                Route::delete('/users/forcedestroy', 'forceDestroy')->name('forcedestroy');
                Route::post('/users/restore', 'restore')->name('restore');
            });
        });

        Route::controller(RolesController::class)->group(function () {
            Route::name('roles.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/permissions/roles', 'index')->name('index')->breadcrumb('Roles');
                Route::get('/permissions/roles/create', 'create')->name('create')->breadcrumb('Role creation', 'apps.roles.index');
                Route::post('/permissions/roles/create', 'store')->name('store');
                Route::get('/permissions/roles/edit/{role}/{all?}', 'edit')->name('edit')->breadcrumb('Role edition', 'apps.roles.index');
                Route::patch('/permissions/roles/edit/{role}', 'update')->name('update');
                Route::post('/permissions/roles/authorization/{role}/{mode?}', 'authorization')->name('authorization');
                Route::delete('/permissions/roles/destroy', 'destroy')->name('destroy');
                Route::delete('/permissions/roles/forcedestroy', 'forceDestroy')->name('forcedestroy');
                Route::post('/permissions/roles/restore', 'restore')->name('restore');
                // Route::get('/permissions/roles/adduser/{role}', 'adduser')->name('edit.adduser')->breadcrumb('Role edition', 'apps.roles.index');
                // Route::get('/permissions/roles/deleteuser/{role}', 'deleteuser')->name('edit.deleteuser');
            });
        });

        Route::controller(AbilitiesController::class)->group(function () {
            Route::name('abilities.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/permissions/roles/abilities', 'index')->name('index')->breadcrumb('Abilities', 'apps.roles.index')->middleware('can:apps.abilities.index');
                Route::post('/permissions/roles/abilities/update/{mode?}', 'update')->name('update')->whereIn('mode', ['toggle', 'on', 'off'])->middleware('can:apps.abilities.update');
            });
        });

        Route::controller(UnitsController::class)->group(function () {
            Route::name('units.')->middleware('verified', 'password.confirm')->group(function () {
                Route::get('/units', 'index')->name('index')->breadcrumb('Units');
                Route::get('/units/create/{unit?}', 'create')->name('create')->breadcrumb('Unit creation', 'apps.units.index');
                Route::post('/units/create', 'store')->name('store');
                Route::get('/units/edit/{unit}', 'edit')->name('edit')->breadcrumb('Unit edition', 'apps.units.index');
                Route::patch('/units/edit/{unit}', 'update')->name('update');
                Route::post('/units/hierarchy', 'hierarchy')->name('hierarchy');
                Route::delete('/units/destroy', 'destroy')->name('destroy');
                Route::post('/units/restore/{unit}', 'restore')->name('restore');
            });
        });
    });

    Route::get('/reports', [ProfileController::class, 'edit'])->name('reports')->breadcrumb('Reports');

    Route::get('/help', [ProfileController::class, 'edit'])->name('help')->breadcrumb('Help');
});

require __DIR__.'/auth.php';
