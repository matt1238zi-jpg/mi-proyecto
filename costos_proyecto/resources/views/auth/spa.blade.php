<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login / Registro</title>
  @vite('resources/js/auth.js')  {{-- SOLO auth.js --}}
</head>
<body class="min-h-screen bg-[#f6f7fb]">
  <div id="app"></div>
</body>
</html>
