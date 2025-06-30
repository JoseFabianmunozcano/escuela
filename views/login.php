<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h3 class="mb-4">Iniciar Sesión</h3>
  <form action="../index.php" method="POST">
    <input type="hidden" name="action" value="login">
    <div class="mb-3">
      <label>Correo</label>
      <input type="email" name="correo" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Contraseña</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Entrar</button>
    <a href="registro.php" class="btn btn-link">Registrarse</a>
  </form>
</div>
</body>
</html>
