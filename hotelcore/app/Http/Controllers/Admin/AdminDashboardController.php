<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        // Usuarios y Huespedes
        $totalUsuarios   = DB::table('usuarios')->count();
        $totalHuespedes  = DB::table('huesped')->count();

        // Habitaciones
        $habitacionesTotales = DB::table('habitaciones')->count();
        $habDisponibles      = DB::table('habitaciones')->where('Estado','Disponible')->count();
        $habOcupadas         = DB::table('habitaciones')->where('Estado','Ocupada')->count();
        $habMantenimiento    = DB::table('habitaciones')->where('Estado','Mantenimiento')->count();
        $ocupacionPct        = $habitacionesTotales > 0 ? round(($habOcupadas / $habitacionesTotales) * 100, 1) : 0;

        // Reservas
        $reservasActivasHoy = DB::table('reservas')
            ->where('Estado','Activa')
            ->whereDate('Fecha_Entrada','<=',$hoy)
            ->whereDate('Fecha_Salida','>=',$hoy)
            ->count();

        $reservasMes = DB::table('reservas')
            ->whereBetween('Fecha_Entrada', [$inicioMes, $finMes])
            ->count();

        // Ingresos
        $ingresosHoy = DB::table('pagos')
            ->where('Estado','Completado')
            ->whereDate('Fecha_Pago',$hoy)
            ->sum('Monto_Total');

        $ingresosMes = DB::table('pagos')
            ->where('Estado','Completado')
            ->whereBetween('Fecha_Pago', [$inicioMes, $finMes])
            ->sum('Monto_Total');

        // Top servicios facturados (por consumo)
        $topServicios = DB::table('consumo_servicios as cs')
            ->join('servicios as s','s.ID_Servicio','=','cs.ID_Servicio')
            ->select('s.Nombre', DB::raw('SUM(cs.Subtotal) as total'), DB::raw('SUM(cs.Cantidad) as cantidad'))
            ->groupBy('s.Nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Últimas reservas
        $ultimasReservas = DB::table('reservas as r')
            ->join('huesped as h','h.ID_Huesped','=','r.ID_Huesped')
            ->join('habitaciones as hab','hab.ID_Habitacion','=','r.ID_Habitacion')
            ->select('r.ID_Reserva','h.Nombre','h.Apellido_Paterno','hab.Numero','r.Fecha_Entrada','r.Fecha_Salida','r.Estado')
            ->orderByDesc('r.Fecha_Creacion')
            ->limit(10)
            ->get();

        // Tickets de soporte
        $ticketsAbiertos = DB::table('soporte_tickets')->where('Estado','<>','Cerrado')->count();
        $ultimosTickets = DB::table('soporte_tickets as t')
            ->join('usuarios as u','u.ID_Usuario','=','t.ID_Usuario')
            ->select('t.ID_Ticket','u.Email','t.Asunto','t.Estado','t.Prioridad','t.Fecha_Creacion')
            ->orderByDesc('t.Fecha_Creacion')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsuarios','totalHuespedes',
            'habitacionesTotales','habDisponibles','habOcupadas','habMantenimiento','ocupacionPct',
            'reservasActivasHoy','reservasMes','ingresosHoy','ingresosMes',
            'topServicios','ultimasReservas','ticketsAbiertos','ultimosTickets'
        ));
    }
}
