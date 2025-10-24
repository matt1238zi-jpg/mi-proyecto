<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Usuarios | Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    :root{
      /* Paleta oscura suave como tu dashboard */
      --bg:#0b1023;
      --bg2:#0f172a;
      --card:#111827;
      --border:#1f2937;
      --text:#e5e7eb;
      --muted:#9ca3af;

      /* Acentos suaves */
      --primary:#69d1e3;     /* cyan pastel */
      --primary-2:#6fd8c8;   /* teal pastel */
      --primary-ink:#0b1220; /* texto en botón primario */
      --ring:#38bdf81f;

      /* Pills/estados */
      --ok:#22c55e;
      --warn:#f59e0b;
      --bad:#ef4444;

      /* Inputs */
      --input-bg:#0b1220;
      --input-br:#223046;
    }

    *{box-sizing:border-box}
    body{
      margin:0;
      background:linear-gradient(120deg,var(--bg),var(--bg2));
      color:var(--text);
      font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Helvetica,Arial,sans-serif
    }

    header{
      display:flex;justify-content:space-between;align-items:center;
      padding:16px 20px;background:#0b1023;border-bottom:1px solid var(--border)
    }

    /* ===== Botones suaves ===== */
    .btn, .btn-ghost, .btn-sm, .delete{
      display:inline-flex;align-items:center;gap:8px;
      padding:10px 14px;border-radius:10px;text-decoration:none;
      border:1px solid transparent; cursor:pointer;
      font-weight:700; line-height:1; box-shadow:none;
      transition:background .15s, border-color .15s, color .15s, transform .05s, filter .15s;
    }
    /* Primario: sin brillo, con borde sutil */
    .btn{
      background:linear-gradient(180deg, #69d1e3, #6fd8c8);
      color:var(--primary-ink);
      border-color:#3aa9b8;
    }
    .btn:hover{ filter:brightness(1.03) }
    .btn:active{ transform:translateY(1px) }

    /* Secundario/ghost */
    .btn-ghost{
      background:var(--input-bg); color:var(--text);
      border-color:#334155;
    }
    .btn-ghost:hover{ background:#0f1a30; border-color:#3b4a64 }

    /* Botones pequeños (acciones tabla) */
    .btn-sm{
      padding:8px 10px; font-weight:600;
      background:#0b1220; color:var(--text);
      border-color:#334155;
      border-radius:8px;
    }
    .btn-sm:hover{ background:#0f1a30 }

    /* Botón eliminar suave */
    .delete{
      padding:8px 10px; font-weight:600; border-radius:8px;
      background:#3b0f0f; color:#fecaca; border-color:#7f1d1d;
    }
    .delete:hover{ background:#4a1111 }

    .wrap{max-width:1100px;margin:22px auto;padding:0 16px}
    .card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:16px}

    .toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-bottom:12px}
    .filters{display:flex;gap:8px;flex-wrap:wrap}

    input[type="text"],select{
      background:var(--input-bg);border:1px solid var(--input-br);color:var(--text);
      border-radius:10px;padding:10px 12px;font-size:14px;outline:none;
      transition:border .15s, box-shadow .15s;
    }
    input::placeholder{color:#73839e}
    input:focus, select:focus{border-color:#38bdf8; box-shadow:0 0 0 4px var(--ring)}

    table{width:100%;border-collapse:collapse}
    th,td{padding:12px;border-bottom:1px solid var(--border);font-size:14px}
    th{color:var(--muted);text-align:left}

    .pill{padding:2px 8px;border-radius:999px;font-size:12px;border:1px solid #334155}
    .ok{color:var(--ok);background:#032b1d;border-color:#064e3b}
    .off{color:#f59e0b;background:#3a2a07;border-color:#7c5e10}
    .danger{color:var(--bad);background:#3b0f0f;border-color:#7f1d1d}

    .table-actions{display:flex;gap:8px}

    .flash{margin-bottom:12px;padding:10px 12px;border-radius:12px;font-size:14px;background:#04212c;border:1px solid #155e75;color:#a5f3fc}

    nav.pagination{display:flex;gap:6px;flex-wrap:wrap;margin-top:12px}
    nav.pagination a, nav.pagination span{
      padding:6px 10px;border:1px solid #334155;border-radius:8px;text-decoration:none;color:var(--text);background:#0b1220
    }
    nav.pagination .active{background:#0f1a30;border-color:#38bdf8}
  </style>
</head>
<body>
<header>
  <div style="display:flex; gap:10px; align-items:center;">
    <a class="btn-ghost" href="{{ route('admin.home') }}">← Volver al panel</a>
    <strong>Administrador · Usuarios</strong>
  </div>
  <div>
    <a class="btn" href="{{ route('admin.usuarios.create') }}">+ Nuevo usuario</a>
  </div>
</header>

<div class="wrap">
  @if(session('ok'))
    <div class="flash">✅ {{ session('ok') }}</div>
  @endif

  <div class="card">
    <form class="toolbar" method="GET" action="{{ route('admin.usuarios.index') }}">
      <div class="filters">
        <input type="text" name="q" value="{{ $q }}" placeholder="Buscar por email…">
        <select name="rol">
          <option value="" {{ $rol==='' ? 'selected' : '' }}>Rol (todos)</option>
          <option value="1" {{ (string)$rol==='1' ? 'selected' : '' }}>Administrador</option>
          <option value="2" {{ (string)$rol==='2' ? 'selected' : '' }}>Recepcionista</option>
          <option value="3" {{ (string)$rol==='3' ? 'selected' : '' }}>Personal</option>
          <option value="4" {{ (string)$rol==='4' ? 'selected' : '' }}>Huésped</option>
        </select>
        <select name="estado">
          <option value="" {{ $estado==='' ? 'selected' : '' }}>Estado (todos)</option>
          <option value="Activo" {{ $estado==='Activo' ? 'selected' : '' }}>Activo</option>
          <option value="Inactivo" {{ $estado==='Inactivo' ? 'selected' : '' }}>Inactivo</option>
        </select>
      </div>
      <button class="btn" type="submit">Filtrar</button>
    </form>

    <div style="overflow:auto;">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Creado</th>
            <th style="width:160px">Acciones</th>
          </tr>
        </thead>
        <tbody>
          @forelse($usuarios as $u)
            <tr>
              <td>{{ $u->ID_Usuario }}</td>
              <td>{{ $u->Email }}</td>
              <td>
                @php $roles=[1=>'Administrador',2=>'Recepcionista',3=>'Personal',4=>'Huésped']; @endphp
                <span class="pill">{{ $roles[$u->ID_Rol] ?? $u->ID_Rol }}</span>
              </td>
              <td>
                <span class="pill {{ $u->Estado==='Activo'?'ok':'off' }}">{{ $u->Estado }}</span>
              </td>
              <td>{{ optional($u->Fecha_Creacion ? \Carbon\Carbon::parse($u->Fecha_Creacion) : null)?->format('d/m/Y H:i') }}</td>
              <td>
                <div class="table-actions">
                  <a class="btn-sm" href="{{ route('admin.usuarios.edit',$u->ID_Usuario) }}">Editar</a>
                  <form method="POST" action="{{ route('admin.usuarios.destroy',$u->ID_Usuario) }}" onsubmit="return confirm('¿Eliminar usuario?');">
                    @csrf @method('DELETE')
                    <button class="delete" type="submit">Eliminar</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" style="color:#9ca3af">Sin resultados</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Si publicaste la paginación, usa tu plantilla. Si no, cambia por:  {{ $usuarios->links() }} --}}
    {{ $usuarios->onEachSide(1)->links('vendor.pagination.simple-default') }}
  </div>
</div>
</body>
</html>
