<?php

use App\Http\Controllers\DriverController;
use App\Http\Controllers\RaceController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('home');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('drivers', DriverController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('teams', TeamController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('races', RaceController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
});

Route::resource('drivers', DriverController::class)->only(['index', 'show']);
Route::resource('teams', TeamController::class)->only(['index', 'show']);
Route::resource('races', RaceController::class)->only(['index', 'show']);

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
