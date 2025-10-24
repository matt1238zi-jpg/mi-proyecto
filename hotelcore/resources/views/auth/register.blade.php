<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Registro</title></head>
<body>
<h2>Crear cuenta</h2>

@if ($errors->any())
  <div style="color:red;">
    @foreach ($errors->all() as $e)
      <div>{{ $e }}</div>
    @endforeach
  </div>
@endif

<form method="POST" action="/register">
  @csrf
  <label>Email</label><br>
  <input type="email" name="Email" value="{{ old('Email') }}" required><br><br>

  <label>Contraseña</label><br>
  <input type="password" name="Contrasena" required><br><br>

  <label>Confirmar contraseña</label><br>
  <input type="password" name="Contrasena_confirmation" required><br><br>

  <button type="submit">Registrarme</button>
</form>

<p>¿Ya tienes cuenta? <a href="/login">Inicia sesión</a></p>
</body>
</html>
