<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\RaceController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\SeasonController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('drivers', DriverController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('teams', TeamController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('races', RaceController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
});

Route::resource('seasons', SeasonController::class)->only(['index', 'show']);
Route::resource('drivers', DriverController::class)->only(['index', 'show']);
Route::resource('teams', TeamController::class)->only(['index', 'show']);
Route::resource('races', RaceController::class)->only(['index', 'show']);

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
