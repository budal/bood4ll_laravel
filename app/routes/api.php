<?php

use App\Http\Controllers\Authorization\UnitsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(UnitsController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/getUnits/{unit?}', 'getUnits')->name('getUnits');
    Route::get('/getUnitsIndex', 'getUnitsIndex')->name('getUnitsIndex');
    Route::get('/getUnitInfo/{unit?}', 'edit')->name('getUnitInfo');
    Route::get('/getUnitStaff/{unit?}/{show?}/{search?}', 'getUnitStaff')->name('getUnitStaff');
});
