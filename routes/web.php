<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PositionController;
use Illuminate\Support\Facades\Route;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

Route::get('/', fn() => redirect('/dashboard'));

Route::prefix('dashboard')->group(function () {
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
Route::get('/debug-schema', function() {
    return response()->json([
        'employees_columns' => Schema::getColumnListing('employees'),
        'positions_columns' => Schema::getColumnListing('positions')
    ]);
});
// routes/web.php
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
        // Create QR code
        $qrCode = QrCode::create('TEST')
            ->setEncoding(new Encoding('UTF-8'))
            ->setSize(300)
            ->setMargin(10)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        // Save to file
        $result->saveToFile(public_path('qr_codes/test.png'));
        
        return response()->file(public_path('qr_codes/test.png'));
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});