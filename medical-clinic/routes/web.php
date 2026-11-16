<?php

use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('appointments.index');
});

Route::prefix('appointments')->name('appointments.')->group(function () {
    Route::get('/', [AppointmentController::class, 'index'])->name('index');
    Route::post('/', [AppointmentController::class, 'store'])->name('store');
    Route::patch('/{appointment}/check-in', [AppointmentController::class, 'checkIn'])->name('check-in');
    Route::patch('/{appointment}/start', [AppointmentController::class, 'start'])->name('start');
    Route::patch('/{appointment}/complete', [AppointmentController::class, 'complete'])->name('complete');
    Route::patch('/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');
});
