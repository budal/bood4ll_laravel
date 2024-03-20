<?php

use App\Http\Controllers\Authorization\RolesController;
use App\Http\Controllers\Authorization\UnitsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(RolesController::class)->middleware('auth:sanctum', 'verified', 'password.confirm')->group(function () {
    Route::get('/getAbilities', 'getAbilities')->name('getAbilities');
    Route::get('/getAbilitiesIndex', 'getAbilitiesIndex')->name('getAbilitiesIndex');
    Route::put('/putAbilitiesUpdate/{mode?}', 'putAbilitiesUpdate')->name('putAbilitiesUpdate')->whereIn('mode', ['toggle', 'on', 'off']);
    Route::get('/getRolesIndex', 'getRolesIndex')->name('getRolesIndex');
    Route::get('/getRoleInfo/{role}/{show?}', 'getRoleInfo')->name('getRoleInfo');
    Route::get('/getRoleAuthorizedUsers/{role}/{show?}', 'getRoleAuthorizedUsers')->name('getRoleAuthorizedUsers');
});

Route::controller(UnitsController::class)->middleware('auth:sanctum', 'verified', 'password.confirm')->group(function () {
    Route::get('/getUnits/{unit?}', 'getUnits')->name('getUnits');
    Route::get('/getUnitsIndex', 'getUnitsIndex')->name('getUnitsIndex');
    Route::get('/getUnitInfo/{unit?}', 'edit')->name('getUnitInfo');
    Route::get('/getUnitStaff/{unit?}/{show?}', 'getUnitStaff')->name('getUnitStaff');
});
