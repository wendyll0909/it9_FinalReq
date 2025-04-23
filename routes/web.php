<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;

// Redirect root to dashboard
Route::get('/', fn() => redirect('/dashboard'));

// Dashboard routes with grouped routes for Employees and Positions
Route::prefix('dashboard')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Employee routes
    Route::get('employees/inactive', [EmployeeController::class, 'inactive'])->name('employees.inactive');
    Route::post('employees/{id}/archive', [EmployeeController::class, 'archive'])->name('employees.archive');
    Route::post('employees/{id}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
    Route::resource('employees', EmployeeController::class);

    // Position routes
    Route::get('positions/list', [PositionController::class, 'list'])->name('positions.list');
    Route::resource('positions', PositionController::class);
});

// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Debug and dev utilities
Route::get('/debug-schema', function() {
    return response()->json([
        'employees_columns' => Schema::getColumnListing('employees'),
        'positions_columns' => Schema::getColumnListing('positions')
    ]);
});

Route::get('/check-db', function() {
    try {
        return response()->json([
            'employees_exists' => Schema::hasTable('employees'),
            'positions_exists' => Schema::hasTable('positions'),
            'employees_columns' => Schema::getColumnListing('employees'),
            'db_connection' => DB::connection()->getPdo() ? 'OK' : 'Failed'
        ]);
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

Route::get('/test-qr', function() {
    try {
        $qrCode = new QrCode('TEST');
        $qrCode->setEncoding(new Encoding('UTF-8'));
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        $qrCode->setForegroundColor(new Color(0, 0, 0));
        $qrCode->setBackgroundColor(new Color(255, 255, 255));

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $filePath = public_path('qr_codes/test.png');
        $result->saveToFile($filePath);

        return response()->file($filePath);
    } catch (\Exception $e) {
        \Log::error('QR Test Failed: '.$e->getMessage());
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

require __DIR__.'/auth.php';
