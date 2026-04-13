<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\EmpresaController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Route::view('dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('empresas', EmpresaController::class);
    Route::patch('/empresas/{empresa}/toggle-status', [EmpresaController::class, 'toggleStatus'])->name('empresas.toggleStatus');
});

Route::view('profile', 'profile')->middleware(['auth'])->name('profile');

require __DIR__ . '/auth.php';
