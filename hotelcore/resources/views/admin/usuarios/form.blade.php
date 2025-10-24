<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>{{ $usuario->exists ? 'Editar' : 'Nuevo' }} usuario</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    :root{
      /* Oscuro suave + acentos pastel */
      --bg:#0e1426;          /* fondo base */
      --card:#111827;        /* tarjeta */
      --text:#e5e7eb;        /* texto principal */
      --muted:#9ca3af;       /* texto secundario */
      --border:#1f2937;      /* borde suave */

      --primary:#86e2f0;     /* cyan pastel */
      --primary-2:#b5c7ff;   /* índigo pastel */
      --danger:#f5b4b4;      /* rojo pastel */

      --input-bg:#0b1220;    /* inputs */
      --input-br:#223046;    /* borde input */
      --ring:#9acbf833;      /* foco sutil */
    }

    *{box-sizing:border-box}
    body{
      margin:0; color:var(--text);
      background:linear-gradient(120deg,#0c1224,#0e1426);
      font-family:system-ui,-apple-system,"Segoe UI",Roboto,Ubuntu,Helvetica,Arial,sans-serif;
    }

    header{
      display:flex; gap:10px; align-items:center;
      padding:16px 20px; background:#0b1023; border-bottom:1px solid var(--border)
    }

    /* Botones suaves */
    .btn, .btn-ghost{
      display:inline-flex; align-items:center; gap:8px;
      padding:10px 14px; border-radius:10px; text-decoration:none; font-weight:700;
      border:1px solid transparent; cursor:pointer; transition:filter .2s, transform .06s, border-color .15s;
    }
    .btn{
      color:#0e1426;
      background:linear-gradient(180deg, var(--primary), var(--primary-2));
      box-shadow:none; /* sin glow */
    }
    .btn:hover{ filter:brightness(1.03) }
    .btn:active{ transform:translateY(1px) }

    .btn-ghost{
      background:var(--input-bg); color:var(--text);
      border-color:#334155;
    }
    .btn-ghost:hover{ filter:brightness(1.06) }

    .wrap{max-width:720px; margin:22px auto; padding:0 16px}
    .card{background:var(--card); border:1px solid var(--border); border-radius:14px; padding:18px}

    form{display:grid; gap:16px}
    label{font-size:13px; color:var(--muted)}
    input, select{
      width:100%; background:var(--input-bg); color:var(--text);
      border:1px solid var(--input-br); border-radius:10px; padding:10px 12px; font-size:14px; outline:none;
      transition:border-color .15s, box-shadow .15s, background .15s;
    }
    input::placeholder{color:#73839e}
    input:focus, select:focus{
      border-color:#5fb8ff; box-shadow:0 0 0 6px var(--ring);
      background:#0d1526;
    }

    .row{display:grid; grid-template-columns:1fr 1fr; gap:12px}
    @media (max-width:640px){ .row{grid-template-columns:1fr} }

    /* Alertas suaves */
    .alert{
      background:#3a0f0f; border:1px solid #7f1d1d; color:#fecaca;
      padding:10px 12px; border-radius:12px
    }
  </style>
</head>
<body>
<header>
  <a class="btn-ghost" href="{{ route('admin.usuarios.index') }}">← Volver</a>
  <strong style="margin-left:6px">{{ $usuario->exists ? 'Editar usuario' : 'Nuevo usuario' }}</strong>
</header>

<div class="wrap">
  @if ($errors->any())
    <div class="card alert">
      @foreach ($errors->all() as $e)
        <div>• {{ $e }}</div>
      @endforeach
    </div>
  @endif

  <div class="card">
    <form method="POST" action="{{ $usuario->exists ? route('admin.usuarios.update',$usuario->ID_Usuario) : route('admin.usuarios.store') }}">
      @csrf
      @if($usuario->exists) @method('PUT') @endif

      <div class="row">
        <div>
          <label for="email">Email</label>
          <input id="email" type="email" name="Email" value="{{ old('Email',$usuario->Email) }}" placeholder="usuario@hotel.com" required>
        </div>
        <div>
          <label for="rol">Rol</label>
          <select id="rol" name="ID_Rol" required>
            @php $roles=[1=>'Administrador',2=>'Recepcionista',3=>'Personal',4=>'Huésped']; @endphp
            @foreach($roles as $id=>$name)
              <option value="{{ $id }}" @selected(old('ID_Rol',$usuario->ID_Rol)==$id)>{{ $name }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="row">
        <div>
          <label for="estado">Estado</label>
          <select id="estado" name="Estado" required>
            <option value="Activo"   @selected(old('Estado',$usuario->Estado??'Activo')==='Activo')>Activo</option>
            <option value="Inactivo" @selected(old('Estado',$usuario->Estado??'Activo')==='Inactivo')>Inactivo</option>
          </select>
        </div>

        <div>
          <label for="pass">Contraseña {{ $usuario->exists ? '(dejar en blanco para no cambiar)' : '' }}</label>
          <input id="pass" type="password" name="Contrasena" {{ $usuario->exists ? '' : 'required' }} placeholder="{{ $usuario->exists ? '••••••••' : 'Mínimo 6 caracteres' }}">
        </div>
      </div>

      <div style="display:flex; gap:10px; justify-content:flex-end">
        <a class="btn-ghost" href="{{ route('admin.usuarios.index') }}">Cancelar</a>
        <button class="btn" type="submit">{{ $usuario->exists ? 'Guardar cambios' : 'Crear usuario' }}</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
