<?php
include '../includes/auth.php';

// Redirige si el rol no es 'directora'
if ($_SESSION['rol'] !== 'directora') {
    header("Location: ../../../views/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Directora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: rgba(105, 104, 104, 0.3); /* Un fondo gris claro para dar contraste */
        }
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .card-custom {
            border: none; /* Quitamos el borde por defecto */
            border-radius: 1rem; /* Bordes más redondeados */
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.1);
            transition: transform .2s ease-in-out, box-shadow .2s ease-in-out;
            height: 100%; /* Asegura que todas las tarjetas tengan la misma altura */
        }
        .card-custom:hover {
            transform: translateY(-5px); /* Efecto de "levantar" la tarjeta */
            box-shadow: 0 1rem 1.5rem rgba(0,0,0,.15);
        }
        .card-custom .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card-icon {
            font-size: 3rem; /* Iconos más grandes */
            color: #0d6efd; /* Color primario para los iconos */
        }
        .btn-custom {
            background-color: #0d6efd;
            border-color: #0d6efd;
            border-radius: 50rem; /* Botones tipo "píldora" */
            padding: 0.5rem 1.5rem;
            transition: background-color .2s ease-in-out;
        }
        .btn-custom:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg mb-5">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <i class="bi bi-briefcase"></i>
            Panel General
        </a>
        <div class="d-flex align-items-center">
            <span class="navbar-text me-3">
               <strong>Bienvenida <?= htmlspecialchars($_SESSION['rol']); ?>, <?= htmlspecialchars($_SESSION['usuario']); ?></strong>
            </span>
            <a href="../views/logout.php" class="btn btn-outline-danger d-flex align-items-center">
                <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row g-4">
        <div class="col-lg-3 col-md-6">
            <div class="card card-custom text-center">
                <div class="card-body p-4">
                    <div>
                        <i class="bi bi-backpack2-fill card-icon mb-3 d-block"></i>
                        <h5 class="card-title">Gestión de Alumnos</h5>
                        <p class="card-text text-muted small">Registra, edita o elimina información de alumnos.</p>
                    </div>
                    <a href="alumnos.php" class="btn btn-primary btn-custom mt-3">Ir al módulo</a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card card-custom text-center">
                <div class="card-body p-4">
                    <div>
                        <i class="bi bi-journal-bookmark-fill card-icon mb-3 d-block"></i>
                        <h5 class="card-title">Gestión de Carreras</h5>
                        <p class="card-text text-muted small">Administra las carreras disponibles en la institución.</p>
                    </div>
                    <a href="carreras.php" class="btn btn-primary btn-custom mt-3">Ir al módulo</a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card card-custom text-center">
                <div class="card-body p-4">
                    <div>
                        <i class="bi bi-person-workspace card-icon mb-3 d-block"></i>
                        <h5 class="card-title">Gestión de Docentes</h5>
                        <p class="card-text text-muted small">Administra los datos de los docentes asignados.</p>
                    </div>
                    <a href="docentes.php" class="btn btn-primary btn-custom mt-3">Ir al módulo</a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card card-custom text-center">
                <div class="card-body p-4">
                    <div>
                        <i class="bi bi-book-half card-icon mb-3 d-block"></i>
                        <h5 class="card-title">Gestión de Cursos</h5>
                        <p class="card-text text-muted small">Crea y organiza los cursos escolares.</p>
                    </div>
                    <a href="cursos.php" class="btn btn-primary btn-custom mt-3">Ir al módulo</a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card card-custom text-center">
                <div class="card-body p-4">
                    <div>
                        <i class="bi bi-people-fill card-icon mb-3 d-block"></i>
                        <h5 class="card-title">Gestión de Usuarios</h5>
                        <p class="card-text text-muted small">Administra los usuarios del sistema.</p>
                    </div>
                    <a href="usuarios.php" class="btn btn-primary btn-custom mt-3">Ir al módulo</a>
                </div>
            </div>
        </div> 
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>