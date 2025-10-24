<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Estado de habitaciones</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    :root{
      --bg:#0b1023; --bg2:#0f172a; --card:#111827; --border:#1f2937;
      --text:#e5e7eb; --muted:#9ca3af;
      --accent:#69d1e3; --accent2:#6fd8c8; --ink:#0b1220;
      --ok:#22c55e; --warn:#f59e0b; --bad:#ef4444;
      --chip:#0b1220; --chip-br:#334155; --ring:#38bdf81f;
      --input-bg:#0b1220; --input-br:#223046;
    }
    *{box-sizing:border-box}
    body{margin:0;background:linear-gradient(120deg,var(--bg),var(--bg2));color:var(--text);font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Helvetica,Arial,sans-serif}
    header{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:#0b1023;border-bottom:1px solid var(--border)}
    .btn,.btn-ghost{
      display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:10px;text-decoration:none;border:1px solid transparent;font-weight:700;cursor:pointer;transition:.15s;
    }
    .btn{background:linear-gradient(180deg,var(--accent),var(--accent2));color:var(--ink);border-color:#3aa9b8}
    .btn:hover{filter:brightness(1.03)} .btn:active{transform:translateY(1px)}
    .btn-ghost{background:var(--chip);color:var(--text);border-color:var(--chip-br)}
    .btn-ghost:hover{background:#0f1a30;border-color:#3b4a64}

    .wrap{max-width:1200px;margin:22px auto;padding:0 16px}

    .filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
    input[type="text"],select{
      background:var(--input-bg);border:1px solid var(--input-br);color:var(--text);
      border-radius:10px;padding:10px 12px;font-size:14px;outline:none;transition:.15s;
    }
    input::placeholder{color:#73839e}
    input:focus,select:focus{border-color:#38bdf8;box-shadow:0 0 0 4px var(--ring)}

    .grid{display:grid;gap:16px}
    .grid.cols-2{grid-template-columns:1fr 1fr}
    .grid.cols-3{grid-template-columns:repeat(3,1fr)}
    .grid.cols-4{grid-template-columns:repeat(4,1fr)}
    @media (max-width:1100px){.grid.cols-4{grid-template-columns:repeat(2,1fr)}}
    @media (max-width:680px){.grid.cols-4,.grid.cols-3,.grid.cols-2{grid-template-columns:1fr}}

    .block{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:16px}
    .block h3{margin:0 0 10px;font-size:16px;color:var(--muted);display:flex;justify-content:space-between;align-items:center}
    .count{font-weight:800;color:var(--text)}
    .chips{display:flex;flex-wrap:wrap;gap:8px}
    .chip{
      background:var(--chip);border:1px solid var(--chip-br);color:var(--text);
      padding:8px 10px;border-radius:10px;font-size:13px;display:inline-flex;gap:8px;align-items:center;
      cursor:pointer;
    }
    .chip .sub{color:var(--muted);font-size:12px}
    .pill{padding:2px 8px;border-radius:999px;font-size:12px;border:1px solid #334155}
    .ok{color:var(--ok);background:#032b1d;border-color:#064e3b}
    .warn{color:#f59e0b;background:#3a2a07;border-color:#7c5e10}
    .bad{color:#ef4444;background:#3b0f0f;border-color:#7f1d1d}

    /* ===== Modal ===== */
    .modal{ position:fixed; inset:0; display:none; place-items:center; background:rgba(2,6,23,.65); backdrop-filter: blur(2px); z-index:50; }
    .modal.open{ display:grid; }
    .modal-card{ width:min(560px, calc(100% - 32px)); background:var(--card); border:1px solid var(--border); border-radius:16px; padding:18px; box-shadow:0 20px 50px rgba(0,0,0,.45); }
    .modal-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
    .modal-title{font-weight:800}
    .modal-body{display:grid;gap:10px}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    @media (max-width:640px){.row{grid-template-columns:1fr}}
    .kv{background:#0b1220;border:1px solid #223046;border-radius:10px;padding:10px 12px}
    .kv label{display:block;color:#9ca3af;font-size:12px;margin-bottom:4px}
    .modal-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:10px}
  </style>
</head>
<body>
<header>
  <div style="display:flex;gap:10px;align-items:center;">
    <a class="btn-ghost" href="{{ route('admin.home') }}">← Volver al panel</a>
    <strong>Estado de habitaciones</strong>
  </div>
  <div>
    <a class="btn" href="{{ route('admin.usuarios.index') }}">Gestionar usuarios</a>
  </div>
</header>

<div class="wrap">
  {{-- Filtros --}}
  <form class="filters" method="GET" action="{{ route('admin.habitaciones.estado') }}">
    <input type="text" name="q" value="{{ $q }}" placeholder="Buscar Nº habitación…">
    <select name="piso">
      <option value="" {{ $piso===''?'selected':'' }}>Piso (todos)</option>
      @foreach($pisos as $p) <option value="{{ $p }}" {{ (string)$piso===(string)$p?'selected':'' }}>{{ $p }}</option> @endforeach
    </select>
    <select name="tipo">
      <option value="" {{ $tipo===''?'selected':'' }}>Tipo (todos)</option>
      @foreach($tipos as $t) <option value="{{ $t }}" {{ (string)$tipo===(string)$t?'selected':'' }}>{{ $t }}</option> @endforeach
    </select>
    <button class="btn" type="submit">Filtrar</button>
    <a class="btn-ghost" href="{{ route('admin.habitaciones.estado') }}">Limpiar</a>
  </form>

  <div class="grid cols-2" style="margin-bottom:16px;">
    <div class="block">
      <h3>Reservadas hoy <span class="count">{{ $reservadasHoy->count() }}</span></h3>
      <div class="chips">
        @forelse($reservadasHoy as $h)
          <span class="chip room-chip" data-id="{{ $h->ID_Habitacion }}">
            #{{ $h->Numero }} <span class="sub">Piso {{ $h->Piso }} · {{ $h->Tipo }}</span> <span class="pill warn">Reservada</span>
          </span>
        @empty
          <span class="sub">Sin reservas para hoy.</span>
        @endforelse
      </div>
    </div>
    <div class="block">
      <h3>Reservadas próximos 7 días <span class="count">{{ $reservadasProx->count() }}</span></h3>
      <div class="chips">
        @forelse($reservadasProx as $h)
          <span class="chip room-chip" data-id="{{ $h->ID_Habitacion }}">
            #{{ $h->Numero }} <span class="sub">Piso {{ $h->Piso }} · {{ $h->Tipo }}</span> <span class="pill warn">Próxima</span>
          </span>
        @empty
          <span class="sub">No hay reservas futuras en el rango.</span>
        @endforelse
      </div>
    </div>
  </div>

  <div class="grid cols-4">
    <div class="block">
      <h3>Ocupadas <span class="count">{{ $ocupadas->count() }}</span></h3>
      <div class="chips">
        @forelse($ocupadas as $h)
          <span class="chip room-chip" data-id="{{ $h->ID_Habitacion }}">
            #{{ $h->Numero }} <span class="sub">Piso {{ $h->Piso }} · {{ $h->Tipo }}</span> <span class="pill ok">Ocupada</span>
          </span>
        @empty <span class="sub">Ninguna.</span> @endforelse
      </div>
    </div>

    <div class="block">
      <h3>Disponibles <span class="count">{{ $disponibles->count() }}</span></h3>
      <div class="chips">
        @forelse($disponibles as $h)
          <span class="chip room-chip" data-id="{{ $h->ID_Habitacion }}">
            #{{ $h->Numero }} <span class="sub">Piso {{ $h->Piso }} · {{ $h->Tipo }}</span>
          </span>
        @empty <span class="sub">Ninguna.</span> @endforelse
      </div>
    </div>

    <div class="block">
      <h3>Mantenimiento <span class="count">{{ $mantenimiento->count() }}</span></h3>
      <div class="chips">
        @forelse($mantenimiento as $h)
          <span class="chip room-chip" data-id="{{ $h->ID_Habitacion }}">
            #{{ $h->Numero }} <span class="sub">Piso {{ $h->Piso }} · {{ $h->Tipo }}</span> <span class="pill bad">Mantenimiento</span>
          </span>
        @empty <span class="sub">Ninguna.</span> @endforelse
      </div>
    </div>

    <div class="block">
      <h3>Resumen</h3>
      <div class="chips">
        <span class="chip"><span class="pill ok">Ocupadas</span> {{ $ocupadas->count() }}</span>
        <span class="chip"><span class="pill">Disponibles</span> {{ $disponibles->count() }}</span>
        <span class="chip"><span class="pill warn">Reservadas (hoy)</span> {{ $reservadasHoy->count() }}</span>
        <span class="chip"><span class="pill warn">Reservadas (7d)</span> {{ $reservadasProx->count() }}</span>
        <span class="chip"><span class="pill bad">Mantenimiento</span> {{ $mantenimiento->count() }}</span>
      </div>
    </div>
  </div>
</div>

{{-- ===== Modal ===== --}}
<div class="modal" id="roomModal" aria-hidden="true">
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="roomTitle">
    <div class="modal-head">
      <div class="modal-title" id="roomTitle">Habitación</div>
      <button class="btn-ghost" id="roomClose" type="button">Cerrar</button>
    </div>

    <div class="modal-body">
      <div class="row">
        <div class="kv"><label>Número</label><div id="m-numero">—</div></div>
        <div class="kv"><label>Tipo</label><div id="m-tipo">—</div></div>
      </div>
      <div class="row">
        <div class="kv"><label>Piso</label><div id="m-piso">—</div></div>
        <div class="kv"><label>Estado</label><div id="m-estado">—</div></div>
      </div>
      <div class="row">
        <div class="kv"><label>Precio por noche</label><div id="m-precio">—</div></div>
        <div class="kv"><label>Creada</label><div id="m-creada">—</div></div>
      </div>
      <div class="kv"><label>Descripción</label><div id="m-desc">—</div></div>
      <div class="row">
        <div class="kv"><label>Reserva actual</label><div id="m-actual">—</div></div>
        <div class="kv"><label>Próxima reserva</label><div id="m-proxima">—</div></div>
      </div>

      <div class="modal-actions">
        <a class="btn-ghost" id="m-ver-reservas" href="#" target="_blank">Ver reservas</a>
        <button class="btn" id="roomOk" type="button">OK</button>
      </div>
    </div>
  </div>
</div>


<script>
  /* =================== Dataset =================== */
  // Llega desde el controlador. Debe tener claves por ID_Habitacion ("202","15",…)
  const ROOMS = {!! $roomsJson !!};

  /* =================== Helpers =================== */
  const $ = (s, r=document)=>r.querySelector(s);
  const modal    = $('#roomModal');
  const closeBtn = $('#roomClose');
  const okBtn    = $('#roomOk');

  /* =================== Modal =================== */
  function openModalByData(d){
    if(!d){ console.warn('Sin datos para abrir modal'); return; }

    $('#roomTitle').textContent = `Habitación #${d.Numero ?? '—'}`;
    $('#m-numero').textContent  = d.Numero ?? '—';
    $('#m-tipo').textContent    = d.Tipo ?? '—';
    $('#m-piso').textContent    = (d.Piso ?? '—');
    $('#m-estado').textContent  = d.Estado ?? '—';
    $('#m-precio').textContent  = d.Precio_Noche != null ? `Bs ${Number(d.Precio_Noche).toLocaleString()}` : '—';
    $('#m-desc').textContent    = d.Descripcion || '—';
    $('#m-creada').textContent  = d.Fecha_Creacion ? new Date(d.Fecha_Creacion).toLocaleString() : '—';

    const act = d.ReservaActual;
    $('#m-actual').textContent  = act
      ? `${new Date(act.Entrada).toLocaleDateString()} → ${new Date(act.Salida).toLocaleDateString()} (${act.Estado})`
      : '—';

    const prox = d.Proxima;
    $('#m-proxima').textContent = prox
      ? `${new Date(prox.Entrada).toLocaleDateString()} → ${new Date(prox.Salida).toLocaleDateString()} (${prox.Estado})`
      : '—';

    // Link de ejemplo (ajústalo si tienes ruta específica)
    $('#m-ver-reservas').href = `/admin/reservas?hab=${encodeURIComponent(d.ID ?? '')}`;

    modal.classList.add('open');
    modal.setAttribute('aria-hidden','false');
    closeBtn?.focus();
  }

  function closeModal(){
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden','true');
  }

  /* =================== Delegación de eventos =================== */
  // Funciona aunque el DOM cambie; captura clicks en cualquier .room-chip
  document.addEventListener('click', (e)=>{
    const chip = e.target.closest('.room-chip');
    if(!chip) return;

    const id = String(chip.dataset.id);     // usamos string; coincide con claves de ROOMS
    let d = ROOMS ? ROOMS[id] : null;

    if(!d){
      console.warn('Sin datos para habitación', id, '| keys:', Object.keys(ROOMS || {}));
      // Fallback mínimo con datos visibles de la chip (por si ROOMS llegara vacío)
      d = {
        ID: id,
        Numero: chip.textContent.match(/#(\S+)/)?.[1] || '',
        Tipo: chip.querySelector('.sub')?.textContent?.split('·')?.[1]?.trim() || '',
        Piso: (chip.querySelector('.sub')?.textContent.match(/Piso\s+(\d+)/)?.[1]) || '',
        Estado: chip.textContent.includes('Ocupada') ? 'Ocupada'
               : chip.textContent.includes('Mantenimiento') ? 'Mantenimiento'
               : chip.textContent.includes('Reservada') ? 'Reservada'
               : 'Disponible',
      };
    }

    openModalByData(d);
  });

  /* =================== Cierre modal =================== */
  closeBtn?.addEventListener('click', closeModal);
  okBtn?.addEventListener('click', closeModal);
  modal?.addEventListener('click', (e)=>{ if(e.target===modal) closeModal(); });
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeModal(); });

  /* =================== Debug =================== */
  console.log('ROOMS listo. Claves:', Object.keys(ROOMS || {}));
</script>

</body>
</html>
