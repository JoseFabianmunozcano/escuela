<?php
include '../includes/auth.php';


if ($_SESSION['rol'] !== 'directora') {
    header("Location: ../../../views/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - Directora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Bienvenida, <?= $_SESSION['usuario']; ?> (Rol: <?= $_SESSION['rol']; ?>)</h2>

    <div class="row g-4">
        <!-- Tarjeta: Gestión de Alumnos -->
        <div class="col-md-4">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Gestión de Alumnos</h5>
                    <p class="card-text">Registrar, editar o eliminar información de alumnos.</p>
                    <a href="alumnos.php" class="btn btn-primary">Ir al módulo</a>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Gestión de Docentes -->
        <div class="col-md-4">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Gestión de Docentes</h5>
                    <p class="card-text">Administrar datos de los docentes asignados.</p>
                    <a href="docentes.php" class="btn btn-primary">Ir al módulo</a>
                </div>
            </div>
        </div>

        <!-- Tarjeta: Gestión de Cursos -->
        <div class="col-md-4">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h5 class="card-title">Gestión de Cursos</h5>
                    <p class="card-text">Crear y organizar cursos escolares.</p>
                    <a href="cursos.php" class="btn btn-primary">Ir al módulo</a>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 text-end">
        <a href="../views/logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
    </div>
</div>
</body>
</html>
