<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index']); // Root route goes to dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');