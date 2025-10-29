<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Admin | Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root { --bg:#0f172a; --card:#111827; --text:#e5e7eb; --muted:#9ca3af; --accent:#22d3ee; --danger:#ef4444; --ok:#22c55e; }
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Helvetica,Arial,sans-serif;background:linear-gradient(120deg,#0b1023,#0f172a);color:var(--text);}
    header{display:flex;justify-content:space-between;align-items:center;padding:16px 24px;background:#0b1023;position:sticky;top:0;z-index:10;border-bottom:1px solid #1f2937}
    .wrap{padding:24px;max-width:1200px;margin:0 auto;}
    .grid{display:grid;gap:16px}
    .grid.cards{grid-template-columns:repeat(4,minmax(0,1fr))}
    @media (max-width:1100px){.grid.cards{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width:640px){.grid.cards{grid-template-columns:1fr}}
    .card{background:var(--card);border:1px solid #1f2937;border-radius:14px;padding:16px}
    .card h4{margin:0 0 6px;font-size:14px;color:var(--muted);font-weight:600;letter-spacing:.3px}
    .card .num{font-size:26px;font-weight:800}
    .row{display:flex;gap:12px;flex-wrap:wrap}
    table{width:100%;border-collapse:collapse;font-size:14px}
    th,td{padding:10px;border-bottom:1px solid #1f2937}
    th{color:var(--muted);text-align:left}
    .pill{padding:2px 8px;border-radius:999px;font-size:12px;border:1px solid #334155}
    .ok{color:#10b981;border-color:#10b98122;background:#10b98111}
    .warn{color:#f59e0b;border-color:#f59e0b22;background:#f59e0b11}
    .bad{color:#ef4444;border-color:#ef444422;background:#ef444411}
    a.btn{color:#0f172a;background:var(--accent);padding:8px 12px;border-radius:10px;text-decoration:none;font-weight:700}
    .muted{color:var(--muted);}
  </style>
  <style>
  .card-link {
    display:block; text-decoration:none; color:inherit;
    transition: transform .08s ease, box-shadow .15s ease, border-color .15s ease;
    cursor:pointer;
  }
  .card-link:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(148,163,184,.35); }
  .card-link:focus { outline: none; box-shadow: 0 0 0 4px #a5b4fc55, 0 0 0 1px #a5b4fc inset; }
</style>
<style>
  .card-link{display:block;text-decoration:none;color:inherit;transition:transform .08s,box-shadow .15s,border-color .15s}
  .card-link:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(148,163,184,.25)}
  .card-link:focus{outline:none;box-shadow:0 0 0 4px #38bdf81f,0 0 0 1px #38bdf8 inset}
</style>

</head>
<body>
<header>
  <div><strong>Panel Administrador</strong></div>
  <div class="row">
    <span class="muted">Hola, {{ auth()->user()->Email }}</span>
    <form method="POST" action="/logout">@csrf<button class="btn" style="border:none;cursor:pointer">Salir</button></form>
  </div>
</header>

<div class="wrap">

  <!-- Tarjetas métricas -->
  <div class="grid cards">
    <a href="{{ route('admin.usuarios.index') }}" class="card card-link">
  <h4>Usuarios del sistema</h4>
  <div class="num">{{ number_format($totalUsuarios) }}</div>

</a>

<a href="{{ route('admin.habitaciones.estado') }}" class="card card-link">
  <h4>Ocupación</h4>
  <div class="num">{{ $ocupacionPct }}%</div>
  <div class="muted">{{ $habOcupadas }} ocupadas / {{ $habitacionesTotales }} totales</div>
</a>

    <div class="card">
      <h4>Reservas hoy (activas)</h4>
      <div class="num">{{ number_format($reservasActivasHoy) }}</div>
      <div class="muted">Este mes: {{ number_format($reservasMes) }}</div>
    </div>
    <div class="card">
      <h4>Ingresos</h4>
      <div class="num">Bs {{ number_format($ingresosHoy,2) }}</div>
      <div class="muted">Mes: Bs {{ number_format($ingresosMes,2) }}</div>
    </div>
  </div>

  <div class="grid" style="grid-template-columns:2fr 1fr; margin-top:16px;">
    <!-- Últimas reservas -->
    <div class="card">
      <div class="row" style="justify-content:space-between;align-items:center;">
        <h4 style="margin:0">Últimas reservas</h4>
        <span class="pill {{ $reservasActivasHoy>0?'ok':'warn' }}">Hoy: {{ $reservasActivasHoy }}</span>
      </div>
      <table>
        <thead>
          <tr>
            <th>#</th><th>Huésped</th><th>Hab.</th><th>Entrada</th><th>Salida</th><th>Estado</th>
          </tr>
        </thead>
        <tbody>
        @forelse($ultimasReservas as $r)
          <tr>
            <td>{{ $r->ID_Reserva }}</td>
            <td>{{ $r->Nombre }} {{ $r->Apellido_Paterno }}</td>
            <td>{{ $r->Numero }}</td>
            <td>{{ \Carbon\Carbon::parse($r->Fecha_Entrada)->format('d/m/Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($r->Fecha_Salida)->format('d/m/Y') }}</td>
            <td>
              @php
                $cls = $r->Estado === 'Activa' ? 'ok' : ($r->Estado === 'Completada' ? 'warn' : 'bad');
              @endphp
              <span class="pill {{ $cls }}">{{ $r->Estado }}</span>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="muted">Sin registros</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    <!-- Soporte -->
    <div class="card">
      <div class="row" style="justify-content:space-between;align-items:center;">
        <h4 style="margin:0">Tickets de soporte</h4>
        <span class="pill {{ $ticketsAbiertos>0 ? 'warn':'ok' }}">{{ $ticketsAbiertos }} abiertos</span>
      </div>
      <table>
        <thead><tr><th>#</th><th>Asunto</th><th>Estado</th></tr></thead>
        <tbody>
        @forelse($ultimosTickets as $t)
          <tr>
            <td>{{ $t->ID_Ticket }}</td>
            <td title="{{ $t->Email }}">{{ $t->Asunto }}</td>
            <td><span class="pill {{ $t->Estado==='Cerrado'?'ok':($t->Estado==='En proceso'?'warn':'bad') }}">{{ $t->Estado }}</span></td>
          </tr>
        @empty
          <tr><td colspan="3" class="muted">Sin tickets</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="grid" style="grid-template-columns:1fr 1fr; margin-top:16px;">
    <!-- Estado de habitaciones -->
    <div class="card">
      <h4>Estado de habitaciones</h4>
      <table>
        <tbody>
          <tr><td>Totales</td><td>{{ $habitacionesTotales }}</td></tr>
          <tr><td>Disponibles</td><td>{{ $habDisponibles }}</td></tr>
          <tr><td>Ocupadas</td><td>{{ $habOcupadas }}</td></tr>
          <tr><td>En Mantenimiento</td><td>{{ $habMantenimiento }}</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Top servicios -->
    <div class="card">
      <h4>Top servicios por facturación</h4>
      <table>
        <thead><tr><th>Servicio</th><th>Cant.</th><th>Total (Bs)</th></tr></thead>
        <tbody>
        @forelse($topServicios as $s)
          <tr>
            <td>{{ $s->Nombre }}</td>
            <td>{{ $s->cantidad }}</td>
            <td>{{ number_format($s->total,2) }}</td>
          </tr>
        @empty
          <tr><td colspan="3" class="muted">Sin consumos registrados</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
</body>
</html>
