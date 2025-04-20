<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PositionController;

Route::put('/positions/{id}', [PositionController::class, 'update']);
Route::get('/employees', [EmployeeController::class, 'index']);
Route::get('/inactive-employees', [EmployeeController::class, 'inactive']);
Route::post('/employees', [EmployeeController::class, 'store']);
Route::get('/employees/{id}', [EmployeeController::class, 'show']);
Route::put('/employees/{id}', [EmployeeController::class, 'update']);
Route::post('/employees/{id}/archive', [EmployeeController::class, 'archive']);
Route::post('/employees/{id}/restore', [EmployeeController::class, 'restore']);
Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);
Route::get('/positions', [EmployeeController::class, 'getPositions']);
Route::post('/positions', [EmployeeController::class, 'storePosition']);
Route::get('/positions/{id}', [EmployeeController::class, 'showPosition']);
Route::put('/positions/{id}', [EmployeeController::class, 'updatePosition']);
Route::delete('/positions/{id}', [EmployeeController::class, 'destroyPosition']);
Route::get('/{section}', function($section) {
    return response()->json(['html' => "<p>Content for {$section} loaded dynamically.</p>"]);
});
