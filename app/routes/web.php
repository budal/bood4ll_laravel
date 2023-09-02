<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CallsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dispatchcall', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dispatchcall');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('/calls/create', [CallsController::class, 'create'])->name('calls.create');
    Route::post('/calls/insert', [CallsController::class, 'insert'])->name('calls.insert');
    Route::patch('/registercall', [CallsController::class, 'update'])->name('calls.update');
    Route::delete('/registercall', [CallsController::class, 'destroy'])->name('calls.destroy');

    Route::get('/calls/dispatch', [CallsController::class, 'insert'])->name('calls.dispatch');

});

require __DIR__.'/auth.php';
