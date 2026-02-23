<?php

use Illuminate\Support\Facades\Route;



use App\Http\Controllers\BmiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AdminController;

// Login routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Proteksi semua akses kalkulator dan profil dengan middleware auth.sso
Route::middleware(['auth.sso'])->group(function () {
	Route::get('/', [BmiController::class, 'index'])->name('bmi.index');
	Route::post('/', [BmiController::class, 'calculate'])->name('bmi.calculate');
	Route::get('/profile', [App\Http\Controllers\AuthController::class, 'profile'])->name('profile');

	// Admin area (dashboard + role management) - requires admin role
	Route::middleware([\App\Http\Middleware\EnsureAdmin::class])->prefix('admin')->group(function () {
		Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
		Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
		Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
		Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
		Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
		Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
		Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
	});
});

