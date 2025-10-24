<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HabitacionEstadoController extends Controller
{
    public function index(Request $request)
    {
        $hoy = Carbon::today();
        $hasta = Carbon::today()->copy()->addDays(7);

        // Filtros opcionales
        $q     = trim((string)$request->query('q',''));          // número
        $piso  = $request->query('piso','');                     // piso
        $tipo  = $request->query('tipo','');                     // tipo

        // Todas las habitaciones (aplicando filtros básicos)
        $base = DB::table('habitaciones')
            ->when($q !== '', fn($qq)=>$qq->where('Numero','like',"%{$q}%"))
            ->when($piso !== '', fn($qq)=>$qq->where('Piso',$piso))
            ->when($tipo !== '', fn($qq)=>$qq->where('Tipo',$tipo))
            ->select('ID_Habitacion','Numero','Tipo','Piso','Estado')
            ->orderBy('Piso')->orderBy('Numero');

        $habitaciones = $base->get();

        // IDs reservados HOY (intersección con rango hoy)
        $reservadasHoyIds = DB::table('reservas')
            ->when($piso !== '' || $tipo !== '' || $q !== '', function($qq) use ($base) {
                // limitar por el mismo subconjunto de habitaciones si hay filtros
                $ids = $base->clone()->pluck('ID_Habitacion');
                $qq->whereIn('ID_Habitacion', $ids);
            })
            ->whereIn('Estado',['Activa','Pendiente'])
            ->whereDate('Fecha_Entrada','<=',$hoy)
            ->whereDate('Fecha_Salida','>=',$hoy)
            ->pluck('ID_Habitacion')->unique();

        // IDs reservadas PRÓXIMOS 7 días (solo futuras)
        $reservadasProxIds = DB::table('reservas')
            ->when($piso !== '' || $tipo !== '' || $q !== '', function($qq) use ($base) {
                $ids = $base->clone()->pluck('ID_Habitacion');
                $qq->whereIn('ID_Habitacion', $ids);
            })
            ->whereIn('Estado',['Activa','Pendiente'])
            ->whereDate('Fecha_Entrada','>',$hoy)
            ->whereDate('Fecha_Entrada','<=',$hasta)
            ->pluck('ID_Habitacion')->unique();

        // Agrupar en colecciones para la vista
        $ocupadas      = $habitaciones->where('Estado','Ocupada')->values();
        $disponibles   = $habitaciones->where('Estado','Disponible')->values();
        $mantenimiento = $habitaciones->where('Estado','Mantenimiento')->values();
        $reservadasHoy = $habitaciones->whereIn('ID_Habitacion',$reservadasHoyIds)->values();
        $reservadasProx= $habitaciones->whereIn('ID_Habitacion',$reservadasProxIds)->values();

        // Para filtros (opciones únicas)
        $pisos = DB::table('habitaciones')->select('Piso')->distinct()->orderBy('Piso')->pluck('Piso');
        $tipos = DB::table('habitaciones')->select('Tipo')->distinct()->orderBy('Tipo')->pluck('Tipo');

        return view('admin.habitaciones.estado', compact(
            'q','piso','tipo','pisos','tipos',
            'ocupadas','disponibles','mantenimiento','reservadasHoy','reservadasProx','hoy','hasta'
        ));
    
    // ... ya tienes $habitaciones (select con ID_Habitacion, Numero, Tipo, Estado)
// Trae extras para el modal
$extras = \DB::table('habitaciones')
    ->select('ID_Habitacion','Descripcion','Precio_Noche','Fecha_Creacion')
    ->get()
    ->keyBy('ID_Habitacion');

// Reserva ACTUAL (si hoy está dentro del rango)
$reservaActual = \DB::table('reservas')
    ->select('ID_Habitacion','Fecha_Entrada','Fecha_Salida','Estado')
    ->whereDate('Fecha_Entrada','<=', $hoy)
    ->whereDate('Fecha_Salida','>=', $hoy)
    ->get()->groupBy('ID_Habitacion');

// Próxima reserva
$proximaReserva = \DB::table('reservas')
    ->select('ID_Habitacion','Fecha_Entrada','Fecha_Salida','Estado')
    ->whereDate('Fecha_Entrada','>', $hoy)
    ->orderBy('Fecha_Entrada')
    ->get()->groupBy('ID_Habitacion');

// Construir mapa de detalles para el modal
$detalles = [];
foreach ($habitaciones as $h) {
    $key = (string)$h->ID_Habitacion;   // ⬅️ clave STRING para que JSON sea objeto

    $ex   = $extras[$h->ID_Habitacion] ?? null;
    $act  = $reservaActual[$h->ID_Habitacion][0] ?? null;
    $prox = $proximaReserva[$h->ID_Habitacion][0] ?? null;

    $detalles[$key] = [
        'ID'             => (int)$h->ID_Habitacion,
        'Numero'         => $h->Numero,
        'Tipo'           => $h->Tipo,
        'Piso'           => $h->Piso,
        'Estado'         => $h->Estado,
        'Descripcion'    => $ex->Descripcion ?? '',
        'Precio_Noche'   => $ex->Precio_Noche ?? null,
        'Fecha_Creacion' => $ex->Fecha_Creacion ?? null,
        'ReservaActual'  => $act ? [
            'Entrada' => (string)$act->Fecha_Entrada,
            'Salida'  => (string)$act->Fecha_Salida,
            'Estado'  => $act->Estado,
        ] : null,
        'Proxima'        => $prox ? [
            'Entrada' => (string)$prox->Fecha_Entrada,
            'Salida'  => (string)$prox->Fecha_Salida,
            'Estado'  => $prox->Estado,
        ] : null,
    ];
}


return view('admin.habitaciones.estado', compact(
  'q','tipo','tipos',
  'ocupadas','disponibles','mantenimiento','reservadasHoy','reservadasProx','hoy','hasta',
  'detalles' // ⬅️ importante
));
}
}
