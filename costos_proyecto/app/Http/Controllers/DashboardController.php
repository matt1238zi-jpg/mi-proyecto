<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Renderiza la vista Inertia. Los datos los pide por AJAX a /api/dashboard/summary
        return Inertia::render('Dashboard');
    }

    public function summary(Request $request)
{
    $user = $request->user();

    // Base proyectos (según rol)
    $proyectos = DB::table('proyecto');
    if ((int)($user->rol_id ?? 0) === 2) {
        $proyectos->where('responsable_id', $user->id);
    }

    // Presupuesto por proyecto
    $presupuestos = DB::table('partida')
        ->select('proyecto_id', DB::raw('COALESCE(SUM(subtotal),0) AS presupuesto'))
        ->groupBy('proyecto_id');

    // Último avance físico por proyecto
    $afUlt = DB::table('avance_fisico AS af')
        ->join(DB::raw('(SELECT proyecto_id, MAX(fecha) AS maxf FROM avance_fisico GROUP BY proyecto_id) AS u'),
               function($j){
                   $j->on('u.proyecto_id','=','af.proyecto_id')
                     ->on('u.maxf','=','af.fecha');
               })
        ->select('af.proyecto_id','af.porcentaje');

    // Último avance financiero por proyecto
    $afinUlt = DB::table('avance_financiero AS fn')
        ->join(DB::raw('(SELECT proyecto_id, MAX(fecha) AS maxf FROM avance_financiero GROUP BY proyecto_id) AS u'),
               function($j){
                   $j->on('u.proyecto_id','=','fn.proyecto_id')
                     ->on('u.maxf','=','fn.fecha');
               })
        ->select('fn.proyecto_id','fn.ejecutado');

    // KPI: proyectos activos
    $activos = (clone $proyectos)
        ->whereIn('estado', ['PLANIFICACION','EN_EJECUCION'])
        ->count();

    // KPI: monto total (suma de partidas)
    $montoTotal = DB::table('proyecto AS p')
        ->leftJoinSub($presupuestos, 'pr', 'pr.proyecto_id', '=', 'p.id')
        ->sum('pr.presupuesto');

    // KPI: avance físico promedio (último % por proyecto)
    $promAvance = DB::table('proyecto AS p')
        ->leftJoinSub($afUlt, 'af', 'af.proyecto_id', '=', 'p.id')
        ->avg('af.porcentaje') ?? 0;

    // KPI: desviación promedio = ((ejecutado/presupuesto)-1)*100
    $rows = DB::table('proyecto AS p')
        ->leftJoinSub($presupuestos, 'pr', 'pr.proyecto_id', '=', 'p.id')
        ->leftJoinSub($afinUlt, 'fn', 'fn.proyecto_id', '=', 'p.id')
        ->select('pr.presupuesto','fn.ejecutado')
        ->get();

    $desvTotal = 0; $n = 0;
    foreach ($rows as $r) {
        $pres = (float)($r->presupuesto ?? 0);
        $ejec = (float)($r->ejecutado ?? 0);
        if ($pres > 0) {
            $desvTotal += (($ejec / $pres) - 1.0) * 100.0;
            $n++;
        }
    }
    $promDesv = $n ? $desvTotal / $n : 0.0;

    // Últimos 5 proyectos
    $recientes = DB::table('proyecto AS p')
        ->leftJoinSub($presupuestos, 'pr', 'pr.proyecto_id', '=', 'p.id')
        ->select('p.id','p.nombre','p.cliente','p.estado','p.fecha_inicio','p.fecha_fin',
                 DB::raw('COALESCE(pr.presupuesto,0) AS monto_contratado'))
        ->orderByDesc('p.id')
        ->limit(5)
        ->get()
        ->map(fn($r) => (object)[
            'id' => $r->id,
            'nombre' => $r->nombre,
            'cliente' => $r->cliente,
            'estado' => strtolower($r->estado ?? ''),
            'monto_contratado' => (float)$r->monto_contratado
        ]);

    return response()->json([
        'ok' => true,
        'role' => (int)($user->rol_id ?? 0),
        'kpis' => [
            'proyectos_activos' => $activos,
            'monto_contratado'  => round($montoTotal, 2),
            'avance_fisico'     => round($promAvance, 1),
            'desviacion'        => round($promDesv, 2),
        ],
        'recientes' => $recientes,
    ]);
}
}