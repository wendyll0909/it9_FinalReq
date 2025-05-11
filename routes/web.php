<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;

// Redirect root to dashboard
Route::get('/', fn() => redirect('/dashboard'));

// Custom login routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Custom logout route
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login')->withHeaders([
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        'Pragma' => 'no-cache',
    ]);
})->name('logout');

// User profile update route
Route::patch('/user/profile', [UserController::class, 'update'])->name('user.profile')->middleware('auth');

// Other authentication routes (only password reset, excluding register and login)
Auth::routes(['login' => false, 'register' => false]);

// Dashboard routes, protected by auth middleware
Route::prefix('dashboard')->middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/data', [DashboardController::class, 'data'])->name('dashboard.data');
    
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

    // Check-Out routes
    Route::get('attendance/checkout', [AttendanceController::class, 'checkout'])->name('attendance.checkout');
    Route::post('attendance/checkout/store', [AttendanceController::class, 'checkoutStore'])->name('attendance.checkout.store');
    Route::get('attendance/{id}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::put('attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
});

// Debug routes
Route::get('/debug-schema', function() {
    try {
        return response()->json([
            'employees_columns' => Schema::getColumnListing('employees'),
            'positions_columns' => Schema::getColumnListing('positions')
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
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
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// QR code routes
Route::get('/qr_codes/{code}.png', [EmployeeController::class, 'serveQrCode'])->name('qr.serve');

Route::get('/test-qr', function() {
    try {
        if (!class_exists('Endroid\QrCode\QrCode')) {
            throw new \Exception('Endroid\QrCode package not installed. Run: composer require endroid/qr-code');
        }

        // Ensure the directory exists
        $directory = public_path('qr_codes');
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0775, true)) {
                throw new \Exception('Failed to create directory: ' . $directory);
            }
        }

        if (!is_writable($directory)) {
            throw new \Exception('Directory not writable: ' . $directory);
        }

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
        \Log::error('QR Test Failed: ' . $e->getMessage());
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});