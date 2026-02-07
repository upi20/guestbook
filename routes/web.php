<?php

use App\Http\Controllers\GuestbookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GuestbookController::class, 'gate'])->name('gate');
Route::get('/form', [GuestbookController::class, 'form'])->name('form');
Route::post('/form', [GuestbookController::class, 'store'])->name('store');
Route::get('/monitoring', [GuestbookController::class, 'monitoring'])->name('monitoring');
Route::get('/api/data', [GuestbookController::class, 'data'])->name('api.data');

// Super-admin actions
Route::put('/api/tamu/{id}', [GuestbookController::class, 'update'])->name('api.tamu.update');
Route::delete('/api/tamu/{id}', [GuestbookController::class, 'destroy'])->name('api.tamu.destroy');
Route::post('/api/tamu/bulk-delete', [GuestbookController::class, 'bulkDestroy'])->name('api.tamu.bulkDestroy');
