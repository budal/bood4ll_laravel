<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AppsController;
use App\Http\Controllers\Authorization\UsersController;

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
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/messages', [ProfileController::class, 'edit'])->name('messages');

    Route::get('/schedule', [ProfileController::class, 'edit'])->name('schedule');
    
    Route::get('/settings', [ProfileController::class, 'edit'])->name('settings');
    

    
    
    Route::get('/apps', [AppsController::class, 'index'])->name('apps');
    
    Route::prefix('apps')->name('apps.')->group(function () {
        Route::middleware('verified')->group(function () {
            Route::controller(UsersController::class)->group(function () {
                Route::get('/users', 'index')->name('users');
                Route::get('/users/new', 'create')->name('users.create');
                Route::get('/users/{uuid}/edit', 'edit')->name('users.edit');
            });
        });
    });


    Route::get('/reports', [ProfileController::class, 'edit'])->name('reports');

    Route::get('/help', [ProfileController::class, 'edit'])->name('help');
});


require __DIR__.'/auth.php';
