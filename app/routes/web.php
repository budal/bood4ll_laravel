<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppsController;
use App\Http\Controllers\Authorization\UsersController;
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
                Route::get('/users/create', 'create')->name('users.create')->middleware(['password.confirm'])->breadcrumb('User creation', 'apps.users');
                Route::post('/users/create', 'create')->name('users.store')->middleware(['password.confirm']);
                Route::get('/users/edit/{user}', 'edit')->name('users.edit')->middleware(['password.confirm'])->breadcrumb('User edition');
                Route::patch('/users/edit/{user}', 'edit')->name('users.update')->middleware(['password.confirm']);
                Route::delete('/users/destroy', 'destroy')->name('users.destroy')->middleware(['password.confirm']);
            });
        });
    });


    Route::get('/reports', [ProfileController::class, 'edit'])->name('reports')->breadcrumb('Reports');

    Route::get('/help', [ProfileController::class, 'edit'])->name('help')->breadcrumb('Help');
});


require __DIR__.'/auth.php';
