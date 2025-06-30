<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h3 class="mb-4">Registro de usuario</h3>
  <form action="../index.php" method="POST">
    <input type="hidden" name="action" value="registro">

    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre</label>
      <input type="text" name="nombre" id="nombre" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="correo" class="form-label">Correo electrónico</label>
      <input type="email" name="correo" id="correo" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Contraseña</label>
      <input type="password" name="password" id="password" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="rol" class="form-label">Rol</label>
      <select name="rol" id="rol" class="form-select" required>
        <option value="">Seleccione un rol</option>
        <option value="directora">Directora</option>
        <option value="coordinador">Coordinador</option>
        <option value="cobranza">Cobranza</option>
      </select>
    </div>

    <!-- Campo oculto para estado (por defecto 1 = activo) -->
    <input type="hidden" name="estado" value="1">

    <button type="submit" class="btn btn-success">Registrar</button>
    <a href="login.php" class="btn btn-secondary">Iniciar sesión</a>
  </form>
</div>
</body>
</html>
