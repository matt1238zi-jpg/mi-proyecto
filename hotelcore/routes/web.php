<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\AdminDashboardController;

/*
|-----------------------------------------
| Home: redirige según el rol
| - Admin (ID_Rol = 1)  -> /admin
| - Otros usuarios      -> /dashboard
|-----------------------------------------
*/
Route::get('/', function () {
    $user = auth()->user();
    if ($user && (int)$user->ID_Rol === 1) {
        return redirect()->route('admin.home');
    }
    return redirect()->route('dashboard');
});

/*
|-----------------------------------------
| Auth
|-----------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class,'show'])->name('login');
    Route::post('/login', [LoginController::class,'login']);
    Route::get('/register', [RegisterController::class,'show'])->name('register.show');
    Route::post('/register', [RegisterController::class,'store'])->name('register.store');
});

Route::post('/logout', [LoginController::class,'logout'])->middleware('auth')->name('logout');

/*
|-----------------------------------------
| Dashboard general (usuarios NO admin)
|-----------------------------------------
*/
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

/*
|-----------------------------------------
| Zona Administrador (solo rol 1)
|-----------------------------------------
*/
Route::middleware(['auth','role:1'])->prefix('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.home');
});


use App\Http\Controllers\Admin\UsuarioController;

Route::middleware(['auth','role:1'])->prefix('admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('admin.home');

    // CRUD Usuarios
    Route::get('/usuarios',               [UsuarioController::class, 'index'])->name('admin.usuarios.index');
    Route::get('/usuarios/create',        [UsuarioController::class, 'create'])->name('admin.usuarios.create');
    Route::post('/usuarios',              [UsuarioController::class, 'store'])->name('admin.usuarios.store');
    Route::get('/usuarios/{id}/edit',     [UsuarioController::class, 'edit'])->name('admin.usuarios.edit');
    Route::put('/usuarios/{id}',          [UsuarioController::class, 'update'])->name('admin.usuarios.update');
    Route::delete('/usuarios/{id}',       [UsuarioController::class, 'destroy'])->name('admin.usuarios.destroy');
});
use App\Http\Controllers\Admin\HabitacionEstadoController;

Route::middleware(['auth','role:1'])->prefix('admin')->group(function () {
    // ...
    Route::get('/habitaciones/estado', [HabitacionEstadoController::class, 'index'])
        ->name('admin.habitaciones.estado');
});

