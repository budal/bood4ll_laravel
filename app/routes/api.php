<?php

use App\Http\Controllers\Authorization\UnitsController;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/getUnits', function (Request $request) {
    $units = Unit::select('id', 'shortpath')
        ->when($request->query(), function ($query) use ($request) {
            foreach ($request->query() as $key => $value) {
                $query->where($key, 'ilike', "%$value%");
            }
        })
        ->orderBy('shortpath')
        ->get();

    return response()->json($units);
})->name('getUnits');
