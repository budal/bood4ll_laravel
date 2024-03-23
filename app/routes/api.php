<?php

use App\Http\Controllers\Authorization\RolesController;
use App\Http\Controllers\Authorization\UnitsController;
use App\Http\Controllers\Authorization\UsersController;
use App\Http\Controllers\HolidaysController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum', 'verified', 'password.confirm')->group(function () {
    Route::controller(RolesController::class)->group(function () {
        Route::get('/getAbilities', 'getAbilities')->name('getAbilities');
        Route::get('/getAbilitiesIndex', 'getAbilitiesIndex')->name('getAbilitiesIndex');
        Route::put('/putAbilitiesUpdate/{mode?}', 'putAbilitiesUpdate')->name('putAbilitiesUpdate')->whereIn('mode', ['toggle', 'on', 'off']);
        Route::get('/getRolesIndex', 'getRolesIndex')->name('getRolesIndex');
        Route::get('/getRoleInfo/{role}', 'getRoleInfo')->name('getRoleInfo');
        Route::get('/getRoleAuthorized/{role}/{show?}', 'getRoleAuthorized')->name('getRoleAuthorized');
    });

    Route::controller(UnitsController::class)->group(function () {
        Route::get('/getUnits/{unit?}', 'getUnits')->name('getUnits');
        Route::get('/getUnitsIndex', 'getUnitsIndex')->name('getUnitsIndex');
        Route::get('/getUnitInfo/{unit?}', 'edit')->name('getUnitInfo');
        Route::get('/getUnitStaff/{unit?}/{show?}', 'getUnitStaff')->name('getUnitStaff');
    });

    Route::controller(UsersController::class)->group(function () {
        Route::get('/getUsersIndex', 'getUsersIndex')->name('getUsersIndex');
        Route::get('/getUserInfo/{user?}', 'getUserInfo')->name('getUserInfo');
        Route::get('/getUserUnits/{user?}/{show?}', 'getUserUnits')->name('getUserUnits');
        Route::get('/getUserRoles/{user?}/{show?}', 'getUserRoles')->name('getUserRoles');
    });

    Route::controller(HolidaysController::class)->group(function () {
        Route::get('/getHolidaysIndex', 'getHolidaysIndex')->name('getHolidaysIndex');
    });
});
