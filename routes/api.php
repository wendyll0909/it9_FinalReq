<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PositionController;
use Illuminate\Support\Facades\Route;

Route::apiResource('employees', EmployeeController::class);
Route::post('/employees/{id}/archive', [EmployeeController::class, 'archive']);
Route::post('/employees/{id}/restore', [EmployeeController::class, 'restore']);
Route::get('/inactive-employees', [EmployeeController::class, 'inactive']);
Route::apiResource('positions', PositionController::class);
Route::get('/{section}', function($section) {
    return response()->json(['html' => "<p>Content for {$section} loaded dynamically.</p>"]);
});