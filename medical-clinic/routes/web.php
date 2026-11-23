<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index']);

Route::prefix('appointments')->name('appointments.')->group(function () {
    Route::get('/', [AppointmentController::class, 'index'])->name('index');
    Route::post('/', [AppointmentController::class, 'store'])->name('store');
    Route::patch('/{appointment}/check-in', [AppointmentController::class, 'checkIn'])->name('check-in');
    Route::patch('/{appointment}/start', [AppointmentController::class, 'start'])->name('start');
    Route::patch('/{appointment}/complete', [AppointmentController::class, 'complete'])->name('complete');
    Route::patch('/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');
});
