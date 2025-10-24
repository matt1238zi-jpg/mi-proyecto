<?php
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\PresupuestoController;
// Home: si está logueado -> /dashboard; si no -> /auth
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('auth.spa');
})->name('home');

// Tu SPA de login/registro
Route::view('/auth', 'auth.spa')->name('auth.spa');

// Alias clásicos por si alguien visita /login o /register
Route::redirect('/login', '/auth')->name('login');
Route::redirect('/register', '/auth#register')->name('register');

// Endpoints de auth (con sesiones web)
Route::middleware('web')->prefix('api/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');
    Route::post('/login',    [AuthController::class, 'login'])->name('api.login');
    Route::post('/logout',   [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me',        [AuthController::class, 'me'])->name('api.me');
});

// Área protegida
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/api/dashboard/summary', [DashboardController::class, 'summary'])
        ->name('api.dashboard.summary');

    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');
Route::get('/proyectos/nuevo', [ProyectoController::class, 'nuevo'])
    ->name('proyectos.nuevo');

Route::get('/proyectos/{proyecto}/presupuesto', [ProyectoController::class, 'editor'])
    ->name('proyectos.editor');

Route::post('/api/proyectos', [ProyectoController::class, 'store'])
    ->name('api.proyectos.store');
    Route::get('/api/proyectos', [ProyectoController::class, 'index'])->name('api.proyectos.index');

Route::get('/proyectos/{proyecto}/presupuesto', [PresupuestoController::class, 'editor'])
    ->name('proyectos.editor');

// API Presupuesto
Route::prefix('api/proyectos/{proyecto}')->group(function () {
    Route::get('/estructura', [PresupuestoController::class, 'estructura']);     // capitulos + partidas
    Route::get('/recursos',   [PresupuestoController::class, 'recursos']);       // base de datos
});

Route::get('/api/partidas/{partida}/apu',        [PresupuestoController::class, 'apuShow']);
Route::post('/api/partidas/{partida}/apu',       [PresupuestoController::class, 'apuUpsert']);  // crea/actualiza APU (rendimiento, %)
Route::post('/api/apu/{apu}/lineas',             [PresupuestoController::class, 'lineaAdd']);   // agrega insumo a APU
Route::patch('/api/apu/lineas/{linea}',          [PresupuestoController::class, 'lineaUpdate']); // cantidad / precio
Route::delete('/api/apu/lineas/{linea}',         [PresupuestoController::class, 'lineaDelete']); // borra línea

});
