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

        // Definición de alcance por rol:
        // rol_id=2 (usuario) => solo proyectos donde es responsable
        // otros roles => todos los proyectos
        $base = DB::table('proyecto'); // nombre de tu tabla de proyectos

        if ((int)($user->rol_id ?? 0) === 2) {
            $base->where('responsable_id', $user->id);
        }

        // KPIs (usa COALESCE para evitar nulls si faltan columnas)
        $proyectosActivos = (clone $base)->whereIn('estado', ['planificacion','ejecucion'])->count();
        $montoContratado  = (clone $base)->sum('monto_contratado');

        // Si tienes columnas avance_fisico (%) y desviacion_financiera (%)
        $avgAvanceFisico  = (clone $base)->avg('avance_fisico');              // ej. 0..100
        $avgDesviacion    = (clone $base)->avg('desviacion_financiera');      // ej. -5..+5

        // Últimos 5 proyectos
        $recientes = (clone $base)
            ->select('id','nombre','cliente','estado','monto_contratado','fecha_inicio','fecha_fin')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return response()->json([
            'ok' => true,
            'role' => (int)($user->rol_id ?? 0),
            'kpis' => [
                'proyectos_activos' => (int)$proyectosActivos,
                'monto_contratado'  => (float)($montoContratado ?? 0),
                'avance_fisico'     => round((float)($avgAvanceFisico ?? 0), 1),
                'desviacion'        => round((float)($avgDesviacion ?? 0), 2),
            ],
            'recientes' => $recientes,
        ]);
    }
}
