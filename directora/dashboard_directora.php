<?php
include '../includes/auth.php';

// Redirige si el rol no es 'directora'
if ($_SESSION['rol'] !== 'directora') {
    header("Location: ../../views/login.php"); // Asegúrate de que esta ruta sea correcta para tu estructura de carpetas
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
            background-color: #f8f9fa; /* Fondo gris claro consistente */
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; /* Fuente consistente */
        }
        .navbar {
            background-color: #ffffff !important; /* Blanco navbar */
            border-bottom: 1px solid #e9ecef; /* Borde sutil */
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); /* Sombra más suave */
        }
        .card-custom {
            border: none;
            border-radius: 0.75rem; /* Bordes más redondeados */
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08); /* Sombra consistente */
            transition: transform .2s ease-in-out, box-shadow .2s ease-in-out;
            height: 100%;
            display: flex; /* Para centrar contenido verticalmente */
            flex-direction: column; /* Para organizar contenido dentro de la tarjeta */
        }
        .card-custom:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0,0,0,.15); /* Sombra más pronunciada en hover */
        }
        .card-custom .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center; /* Centrar horizontalmente el contenido */
            text-align: center; /* Centrar texto */
            padding: 2rem; /* Espaciado interno */
        }
        .card-icon {
            font-size: 3.5rem; /* Iconos un poco más grandes */
            color: #0d6efd; /* Color primario para los iconos */
            margin-bottom: 1rem; /* Espacio debajo del icono */
        }
        .card-title {
            font-weight: 600; /* Títulos de tarjeta más audaces */
            color: #344767; /* Color de texto más oscuro */
            margin-bottom: 0.5rem;
        }
        .card-text {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 1.5rem; /* Espacio antes del botón */
        }
        .btn-custom {
            background-color: #0d6efd;
            border-color: #0d6efd;
            border-radius: 0.5rem; /* Botones menos "píldora" para coincidir con el resto */
            padding: 0.6rem 1.8rem; /* Mayor padding */
            font-size: 1.1rem; /* Texto de botón más grande */
            font-weight: 500;
            transition: background-color .2s ease-in-out, border-color .2s ease-in-out, box-shadow .2s ease-in-out;
        }
        .btn-custom:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-weight: bold;
            color: #0d6efd !important;
        }
        .navbar-text {
            color: #344767 !important;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg py-3 shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="bi bi-person-circle me-2"></i>
            Panel de Control de Directora
        </a>
        <div class="d-flex align-items-center">
            <span class="navbar-text me-3 fw-bold">
               Bienvenida, <span class="text-primary"><?= htmlspecialchars($_SESSION['usuario']); ?></span>
            </span>
            <a href="../views/logout.php" class="btn btn-outline-danger d-flex align-items-center">
                <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
            </a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="mb-5 text-center text-primary">Módulos de Administración</h1>

    <div class="row g-4 justify-content-center">
        <div class="col-lg-3 col-md-6 col-sm-10">
            <div class="card card-custom">
                <div class="card-body">
                    <i class="bi bi-backpack2-fill card-icon"></i>
                    <h5 class="card-title">Gestión de Alumnos</h5>
                    <p class="card-text">Registra, edita o elimina información de alumnos.</p>
                    <a href="alumnos.php" class="btn btn-primary btn-custom">Ir al módulo <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-10">
            <div class="card card-custom">
                <div class="card-body">
                    <i class="bi bi-journal-bookmark-fill card-icon"></i>
                    <h5 class="card-title">Gestión de Carreras</h5>
                    <p class="card-text">Administra las carreras disponibles en la institución.</p>
                    <a href="carreras.php" class="btn btn-primary btn-custom">Ir al módulo <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-10">
            <div class="card card-custom">
                <div class="card-body">
                    <i class="bi bi-person-workspace card-icon"></i>
                    <h5 class="card-title">Gestión de Docentes</h5>
                    <p class="card-text">Administra los datos de los docentes asignados.</p>
                    <a href="docentes.php" class="btn btn-primary btn-custom">Ir al módulo <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-10">
            <div class="card card-custom">
                <div class="card-body">
                    <i class="bi bi-book-half card-icon"></i>
                    <h5 class="card-title">Gestión de Cursos</h5>
                    <p class="card-text">Crea y organiza los cursos escolares.</p>
                    <a href="cursos.php" class="btn btn-primary btn-custom">Ir al módulo <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-10">
            <div class="card card-custom">
                <div class="card-body">
                    <i class="bi bi-people-fill card-icon"></i>
                    <h5 class="card-title">Gestión de Usuarios</h5>
                    <p class="card-text">Administra los usuarios del sistema.</p>
                    <a href="usuarios.php" class="btn btn-primary btn-custom">Ir al módulo <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-10">
            <div class="card card-custom">
                <div class="card-body">
                    <i class="bi bi-file-earmark-person-fill card-icon"></i>
                    <h5 class="card-title">Gestión de Inscripciones</h5>
                    <p class="card-text">Administra las inscripciones de alumnos a cursos.</p>
                    <a href="inscripciones.php" class="btn btn-primary btn-custom">Ir al módulo <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-10">
            <div class="card card-custom">
                <div class="card-body">
                    <i class="bi bi-person-lines-fill card-icon"></i>
                    <h5 class="card-title">Asignación Docentes</h5>
                    <p class="card-text">Asigna docentes a cursos y grupos.</p>
                    <a href="asignacion_docentes.php" class="btn btn-primary btn-custom">Ir al módulo <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-10">
            <div class="card card-custom">
                <div class="card-body">
                    <i class="bi bi-journal-check card-icon"></i>
                    <h5 class="card-title">Gestión de Calificaciones</h5>
                    <p class="card-text">Monitorea y valida las calificaciones de los cursos.</p>
                    <a href="calificaciones.php" class="btn btn-primary btn-custom">Ir al módulo <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-10">
            <div class="card card-custom">
                <div class="card-body">
                    <i class="bi bi-calendar-check card-icon"></i>
                    <h5 class="card-title">Períodos Académicos</h5>
                    <p class="card-text">Define y gestiona los ciclos escolares.</p>
                    <a href="periodos_academicos.php" class="btn btn-primary btn-custom">Ir al módulo <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-10">
            <div class="card card-custom">
                <div class="card-body">
                    <i class="bi bi-bar-chart-fill card-icon"></i>
                    <h5 class="card-title">Reportes y Estadísticas</h5>
                    <p class="card-text">Genera informes y estadísticas del sistema.</p>
                    <a href="reportes_estadisticas.php" class="btn btn-primary btn-custom">Ir al módulo <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-10">
            <div class="card card-custom">
                <div class="card-body">
                    <i class="bi bi-megaphone-fill card-icon"></i>
                    <h5 class="card-title">Comunicados y Avisos</h5>
                    <p class="card-text">Envía comunicados a alumnos y docentes.</p>
                    <a href="comunicados.php" class="btn btn-primary btn-custom">Ir al módulo <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>