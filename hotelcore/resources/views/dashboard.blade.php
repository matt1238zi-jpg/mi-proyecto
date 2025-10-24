<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Dashboard</title></head>
<body>
  <h2>Bienvenido, {{ auth()->user()->Email }}</h2>
  <form method="POST" action="/logout">@csrf<button type="submit">Salir</button></form>
</body>
</html>
