<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;

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

    
    
    // Attendance routes
    Route::get('attendance/checkin', [AttendanceController::class, 'checkin'])->name('attendance.checkin');
    Route::post('attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::delete('attendance/{id}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');

    // Add Check-Out routes
    Route::get('attendance/checkout', [AttendanceController::class, 'checkout'])->name('attendance.checkout'); // For rendering the check-out page
    Route::post('attendance/checkout/store', [AttendanceController::class, 'checkoutStore'])->name('attendance.checkout.store'); // For submitting check-out data
    Route::get('attendance/{id}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit'); // For editing attendance
    Route::put('attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update'); // For updating attendance
});

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

Route::get('/qr_codes/{code}.png', [EmployeeController::class, 'serveQrCode'])->name('qr.serve');

Route::get('/test-qr', function() {
    try {
        // Create QR code
        $qrCode = new QrCode('TEST');
        $qrCode->setEncoding(new Encoding('UTF-8'));
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        $qrCode->setForegroundColor(new Color(0, 0, 0));
        $qrCode->setBackgroundColor(new Color(255, 255, 255));

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        // Save to file
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