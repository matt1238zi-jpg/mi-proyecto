<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Login</title>

  <style>
    :root{
      /* Paleta pastel (modo claro) */
      --bg1:#fdf2f8;         /* rosa muy claro */
      --bg2:#e0f2fe;         /* celeste muy claro */
      --card:#ffffff;        /* tarjeta */
      --text:#334155;        /* texto principal (slate-700) */
      --muted:#64748b;       /* texto secundario (slate-500) */
      --accent:#93c5fd;      /* azul pastel */
      --accent-2:#a5b4fc;    /* índigo pastel */
      --danger:#ef9a9a;      /* rojo pastel */
      --ring:#a5b4fc33;      /* foco inputs (sombra) */
      --border:#e2e8f0;      /* borde suave */
    }

    html,body{height:100%}
    body{
      margin:0;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, Helvetica, Arial, sans-serif;
      color:var(--text);
      background:
        radial-gradient(900px 500px at 10% -10%, var(--bg2) 0%, transparent 60%),
        radial-gradient(900px 600px at 110% 110%, var(--bg1) 0%, transparent 60%),
        linear-gradient(160deg, #fff, #f8fafc);
      display:flex; align-items:center; justify-content:center;
      padding:24px;
    }

    .auth-card{
      width:100%;
      max-width:420px;
      background:var(--card);
      border:1px solid var(--border);
      border-radius:16px;
      padding:28px;
      box-shadow: 0 18px 40px rgba(148,163,184,.25);
    }

    .auth-title{ margin:0 0 6px; font-size:24px; font-weight:800; letter-spacing:.2px; }
    .auth-sub{ margin:0 0 18px; color:var(--muted); font-size:14px; }

    .alert{
      margin-bottom:16px;
      background:#fee2e2;
      color:#991b1b;
      border:1px solid #fecaca;
      border-radius:12px;
      padding:10px 12px;
      font-size:14px;
    }

    form{display:grid; gap:14px}
    label{font-size:13px; color:var(--muted)}

    input[type="email"], input[type="password"]{
      width:100%; box-sizing:border-box; appearance:none;
      background:#fff;
      border:1px solid var(--border);
      border-radius:12px;
      color:var(--text);
      padding:12px 14px;
      font-size:14px;
      outline:none;
      transition:border .15s, box-shadow .15s, transform .05s, background .2s;
    }
    input::placeholder{color:#94a3b8}
    input:focus{
      border-color: var(--accent-2);
      box-shadow: 0 0 0 6px var(--ring);
      background:#fbfdff;
    }

    .row{display:flex; align-items:center; justify-content:space-between; gap:8px}
    .muted{color:var(--muted); font-size:13px}

    .btn{
      display:inline-flex; align-items:center; justify-content:center;
      gap:8px; width:100%;
      background:linear-gradient(180deg, var(--accent), var(--accent-2));
      color:#1f2937; font-weight:800; letter-spacing:.3px;
      border:none; border-radius:12px; padding:12px 14px;
      cursor:pointer; transition: transform .06s ease, filter .2s ease, box-shadow .2s;
      box-shadow: 0 6px 14px rgba(165,180,252,.45);
    }
    .btn:hover{ filter:brightness(1.03) }
    .btn:active{ transform: translateY(1px) }

    a{color:#6b7280; text-decoration:none; font-weight:600}
    a:hover{ color:#4f46e5; text-decoration:underline }

    .check{ display:flex; align-items:center; gap:8px; font-size:13px; color:var(--muted); }
    .check input{ width:16px; height:16px; accent-color: var(--accent-2); }

    .foot{margin-top:10px; text-align:center; font-size:13px; color:var(--muted)}
  </style>
</head>

<body>
  <div class="auth-card">
    <h1 class="auth-title">Iniciar sesión</h1>
    <p class="auth-sub">Bienvenido al panel del hotel. Ingresa tus credenciales.</p>

    @if ($errors->any())
      <div class="alert">
        @foreach ($errors->all() as $e)
          <div>• {{ $e }}</div>
        @endforeach
      </div>
    @endif

    <form method="POST" action="/login">
      @csrf

      <div>
        <label for="email">Email</label>
        <input id="email" type="email" name="Email" value="{{ old('Email') }}" placeholder="tucorreo@hotel.com" required>
      </div>

      <div>
        <label for="password">Contraseña</label>
        <input id="password" type="password" name="Contrasena" placeholder="********" required>
      </div>

      <div class="row">
        <label class="check"><input type="checkbox" name="remember"> Recordarme</label>
        <a class="muted" href="#">¿Olvidaste tu contraseña?</a>
      </div>

      <button class="btn" type="submit">Entrar</button>
    </form>

    <div class="foot">¿No tienes cuenta? <a href="/register">Crear cuenta</a></div>
  </div>
</body>
</html>
