<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\RecursoImportController;

// Si vas a usar rutas admin, descomenta e importa también AdminController:
// use App\Http\Controllers\Admin\AdminController;

use App\Http\Controllers\Admin\UserController; // <-- IMPORTANTE (arriba)

// Home: si está logueado -> /dashboard; si no -> /auth
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('auth.spa');
})->name('home');

// SPA de login/registro
Route::view('/auth', 'auth.spa')->name('auth.spa');

// Alias /login y /register
Route::redirect('/login', '/auth')->name('login');
Route::redirect('/register', '/auth#register')->name('register');

// Endpoints de auth (con sesiones web)
Route::middleware('web')->prefix('api/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');
    Route::post('/login',    [AuthController::class, 'login'])->name('api.login');
    Route::post('/logout',   [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me',        [AuthController::class, 'me'])->name('api.me');
});

// ----------------------- Área protegida -----------------------
Route::middleware('auth')->group(function () {

    // Vista principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // API dashboard
    Route::get('/api/dashboard/summary', [DashboardController::class, 'summary'])
        ->name('api.dashboard.summary');

    // Perfil
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    // Proyectos
    Route::get('/proyectos/nuevo', [ProyectoController::class, 'nuevo'])
        ->name('proyectos.nuevo');

    Route::get('/proyectos/{proyecto}/presupuesto', [ProyectoController::class, 'editor'])
        ->name('proyectos.editor');

    // API proyectos
    Route::post('/api/proyectos', [ProyectoController::class, 'store'])->name('api.proyectos.store');
    Route::get('/api/proyectos',  [ProyectoController::class, 'index'])->name('api.proyectos.index');

    // API Presupuesto por proyecto
    Route::prefix('api/proyectos/{proyecto}')->group(function () {
        Route::get('/estructura', [PresupuestoController::class, 'estructura']); // capítulos + partidas
        Route::get('/recursos',   [PresupuestoController::class, 'recursos']);   // base de datos
    });

    // API APU / líneas
    Route::get('/api/partidas/{partida}/apu',  [PresupuestoController::class, 'apuShow']);
    Route::post('/api/partidas/{partida}/apu', [PresupuestoController::class, 'apuUpsert']);     // rendimiento, %
    Route::post('/api/apu/{apu}/lineas',       [PresupuestoController::class, 'lineaAdd']);      // agrega insumo
    Route::patch('/api/apu/lineas/{linea}',    [PresupuestoController::class, 'lineaUpdate']);   // cantidad / precio
    Route::delete('/api/apu/lineas/{linea}',   [PresupuestoController::class, 'lineaDelete']);   // borra línea

    // Importación de recursos (Excel)
    Route::post('/api/recursos/import', [RecursoImportController::class, 'import'])
        ->name('api.recursos.import');

    // --------- API: Usuarios (CRUD) ----------
    Route::prefix('api')->group(function () {
        Route::get('/users',        [UserController::class, 'index']);   // ?q=&page=
        Route::post('/users',       [UserController::class, 'store']);
        Route::patch('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}',[UserController::class, 'destroy']);
    });

    // (Opcional) Rutas Admin: descomenta sólo si tienes AdminController y middleware('admin')
    /*
    Route::middleware('admin')->group(function () {
        Route::get('/admin',            [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/usuarios',   [AdminController::class, 'usuarios']);
        Route::get('/admin/proyectos',  [AdminController::class, 'proyectos']);
    });
    */
});