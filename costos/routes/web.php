<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProyectoController;

Route::get('/', fn() => redirect()->route('usuarios.index'));

Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
Route::get('/usuarios/crear', [UsuarioController::class, 'crear'])->name('usuarios.crear');
Route::post('/usuarios', [UsuarioController::class, 'guardar'])->name('usuarios.guardar');

Route::get('/proyectos', [ProyectoController::class, 'index'])->name('proyectos.index');
Route::get('/proyectos/crear', [ProyectoController::class, 'crear'])->name('proyectos.crear');
Route::post('/proyectos', [ProyectoController::class, 'guardar'])->name('proyectos.guardar');
