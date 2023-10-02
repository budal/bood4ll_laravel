<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppsController;
use App\Http\Controllers\Authorization\UsersController;
use App\Http\Controllers\Authorization\RolesController;
use App\Http\Controllers\Authorization\AbilitiesController;
use App\Http\Controllers\Authorization\UnitsController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
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
})->middleware(['auth', 'verified'])->name('dashboard')->breadcrumb('Dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit')->breadcrumb('Profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/messages', [ProfileController::class, 'edit'])->name('messages')->breadcrumb('Messages');

    Route::get('/schedule', [ProfileController::class, 'edit'])->name('schedule')->breadcrumb('Schedule');
    
    Route::get('/settings', [ProfileController::class, 'edit'])->name('settings')->breadcrumb('Settings');
    
    Route::get('/apps', [AppsController::class, 'index'])->name('apps')->breadcrumb('Apps');
    
    Route::prefix('apps')->name('apps.')->group(function () {
        Route::middleware('verified')->group(function () {
            Route::controller(UsersController::class)->group(function () {
                Route::get('/users', 'index')->name('users')->middleware(['password.confirm', 'verified'])->breadcrumb('Users');
                Route::get('/users1', 'index1')->name('users1');
                Route::get('/users/create', 'create')->name('users.create')->middleware(['password.confirm'])->breadcrumb('User creation', 'apps.users');
                Route::post('/users/create', 'store')->name('users.store')->middleware(['password.confirm']);
                Route::get('/users/edit/{user}', 'edit')->name('users.edit')->middleware(['password.confirm'])->breadcrumb('User edition', 'apps.users');
                Route::patch('/users/edit/{user}', 'update')->name('users.update')->middleware(['password.confirm']);
                Route::delete('/users/destroy', 'destroy')->name('users.destroy')->middleware(['password.confirm']);
                Route::post('/users/restore/{user}', 'restore')->name('users.restore')->middleware(['password.confirm']);
            });

            Route::controller(RolesController::class)->group(function () {
                Route::get('/permissions/roles', 'index')->name('roles')->middleware(['password.confirm', 'verified'])->breadcrumb('Roles');
                Route::get('/permissions/roles/create', 'create')->name('roles.create')->middleware(['password.confirm'])->breadcrumb('Role creation', 'apps.roles');
                Route::post('/permissions/roles/create', 'create')->name('roles.store')->middleware(['password.confirm']);
                Route::get('/permissions/roles/edit/{user}', 'edit')->name('roles.edit')->middleware(['password.confirm'])->breadcrumb('Role edition', 'apps.roles');
                Route::patch('/permissions/roles/edit/{user}', 'edit')->name('roles.update')->middleware(['password.confirm']);
                Route::delete('/permissions/roles/destroy', 'destroy')->name('roles.destroy')->middleware(['password.confirm']);
            });

            Route::controller(AbilitiesController::class)->group(function () {
                Route::get('/permissions/abilities', 'index')->name('abilities')->middleware(['password.confirm', 'verified'])->breadcrumb('Abilities');
                Route::get('/permissions/abilities/create', 'create')->name('abilities.create')->middleware(['password.confirm'])->breadcrumb('Ability creation', 'apps.abilities');
                Route::post('/permissions/abilities/create', 'create')->name('abilities.store')->middleware(['password.confirm']);
                Route::get('/permissions/abilities/edit/{user}', 'edit')->name('abilities.edit')->middleware(['password.confirm'])->breadcrumb('Ability edition', 'apps.abilities');
                Route::patch('/permissions/abilities/edit/{user}', 'edit')->name('abilities.update')->middleware(['password.confirm']);
                Route::delete('/permissions/abilities/destroy', 'destroy')->name('abilities.destroy')->middleware(['password.confirm']);
            });

            Route::controller(UnitsController::class)->group(function () {
                Route::get('/units', 'index')->name('units')->middleware(['password.confirm', 'verified'])->breadcrumb('Units');
                Route::get('/units/create', 'create')->name('units.create')->middleware(['password.confirm'])->breadcrumb('Unit creation', 'apps.units');
                Route::post('/units/create', 'create')->name('units.store')->middleware(['password.confirm']);
                Route::get('/units/edit/{user}', 'edit')->name('units.edit')->middleware(['password.confirm'])->breadcrumb('Unit edition', 'apps.units');
                Route::patch('/units/edit/{user}', 'edit')->name('units.update')->middleware(['password.confirm']);
                Route::delete('/units/destroy', 'destroy')->name('units.destroy')->middleware(['password.confirm']);
            });
        });
    });


    Route::get('/reports', [ProfileController::class, 'edit'])->name('reports')->breadcrumb('Reports');

    Route::get('/help', [ProfileController::class, 'edit'])->name('help')->breadcrumb('Help');
});


require __DIR__.'/auth.php';
