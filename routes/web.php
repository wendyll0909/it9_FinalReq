<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::get('/', fn() => redirect('/dashboard'));

Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);

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

Route::patch('/user/profile', [UserController::class, 'update'])->name('user.profile')->middleware('auth');

Auth::routes(['login' => false, 'register' => false]);

Route::prefix('dashboard')->middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/top-employee', [DashboardController::class, 'topEmployee'])->name('dashboard.topEmployee');
    Route::get('/rankings', [DashboardController::class, 'rankings'])->name('dashboard.rankings');
    Route::get('/evaluation', [DashboardController::class, 'evaluation'])->name('dashboard.evaluation');

    Route::get('employees/inactive', [EmployeeController::class, 'inactive'])->name('employees.inactive');
    Route::post('employees/{id}/archive', [EmployeeController::class, 'archive'])->name('employees.archive');
    Route::post('employees/{id}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
    Route::resource('employees', EmployeeController::class);

    Route::get('positions/list', [PositionController::class, 'list'])->name('positions.list');
    Route::resource('positions', PositionController::class);

    Route::get('attendance/checkin', [AttendanceController::class, 'checkin'])->name('attendance.checkin');
    Route::post('attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::delete('attendance/{id}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
    Route::get('attendance/checkout', [AttendanceController::class, 'checkout'])->name('attendance.checkout');
    Route::post('attendance/checkout/store', [AttendanceController::class, 'checkoutStore'])->name('attendance.checkout.store');
    Route::get('attendance/{id}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
    Route::put('attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
});

Route::get('/debug-schema', function() {
    try {
        return response()->json([
            'employees_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('employees'),
            'positions_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('positions')
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/check-db', function() {
    try {
        return response()->json([
            'employees_exists' => \Illuminate\Support\Facades\Schema::hasTable('employees'),
            'positions_exists' => \Illuminate\Support\Facades\Schema::hasTable('positions'),
            'employees_columns' => \Illuminate\Support\Facades\Schema::getColumnListing('employees'),
            'db_connection' => \Illuminate\Support\Facades\DB::connection()->getPdo() ? 'OK' : 'Failed'
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

Route::get('/qr_codes/{code}.png', [EmployeeController::class, 'serveQrCode'])->name('qr.serve');

Route::get('/test-qr', function() {
    try {
        $qrCode = new \Endroid\QrCode\QrCode('TEST');
        $qrCode->setEncoding(new \Endroid\QrCode\Encoding('UTF-8'));
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        $qrCode->setForegroundColor(new \Endroid\QrCode\Color\Color(0, 0, 0));
        $qrCode->setBackgroundColor(new \Endroid\QrCode\Color\Color(255, 255, 255));

        $writer = new \Endroid\QrCode\Writer\PngWriter();
        $result = $writer->write($qrCode);
        
        $filePath = public_path('qr_codes/test.png');
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0775, true);
        }
        $result->saveToFile($filePath);
        
        return response()->file($filePath);
    } catch (\Exception $e) {
        \Log::error('QR Test Failed: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});