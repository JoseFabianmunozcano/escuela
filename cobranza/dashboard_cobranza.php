<?php
include '../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2>Bienvenido, <?php echo $_SESSION['usuario']; ?></h2>
  <p>Rol: <strong><?php echo $_SESSION['rol']; ?></strong></p>
  <a href="../views/logout.php" class="btn btn-danger">Cerrar sesión</a>
</div>
</body>
</html>