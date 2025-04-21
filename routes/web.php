<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PositionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/dashboard'));

Route::prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('employees', EmployeeController::class);
    Route::get('employees/inactive', [EmployeeController::class, 'inactive'])->name('employees.inactive');
    Route::post('employees/{id}/archive', [EmployeeController::class, 'archive'])->name('employees.archive');
    Route::post('employees/{id}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
    Route::resource('positions', PositionController::class);
    Route::get('positions/{id}', [PositionController::class, 'show'])->name('positions.show');
    Route::get('employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('positions/list', [PositionController::class, 'list'])->name('positions.list');
});