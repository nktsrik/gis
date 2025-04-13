<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MarkerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Route::post('/markers', [MarkerController::class, 'store']);
// Route::get('/markers', [MarkerController::class, 'index']);

// Route::get('/locations', [MarkerController::class, 'index']);
// Route::post('/locations', [MarkerController::class, 'store']);
// Route::delete('/locations/{id}', [MarkerController::class, 'destroy']);

Route::get('/locations', [MarkerController::class, 'index']);
Route::post('/locations', [MarkerController::class, 'store']);
Route::put('/locations/{marker}', [MarkerController::class, 'update']);
Route::delete('/locations/{id}', [MarkerController::class, 'destroy']);