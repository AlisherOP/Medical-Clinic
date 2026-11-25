<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OperationScheduleController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
Route::post('/dashboard/broadcast', [DashboardController::class, 'broadcast'])->name('dashboard.broadcast');
Route::delete('/dashboard/broadcast', [DashboardController::class, 'dismissBroadcast'])->name('dashboard.broadcast.dismiss');

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::prefix('appointments')->name('appointments.')->group(function () {
    Route::get('/', [AppointmentController::class, 'index'])->name('index');
    Route::post('/', [AppointmentController::class, 'store'])->name('store');
    Route::patch('/{appointment}/check-in', [AppointmentController::class, 'checkIn'])->name('check-in');
    Route::patch('/{appointment}/start', [AppointmentController::class, 'start'])->name('start');
    Route::patch('/{appointment}/complete', [AppointmentController::class, 'complete'])->name('complete');
    Route::patch('/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');
    Route::patch('/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('reschedule');
});

Route::prefix('operations')->name('operations.')->group(function () {
    Route::get('/', [OperationScheduleController::class, 'index'])->name('index');
});
