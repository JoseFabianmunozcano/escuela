<?php
// Asegúrate de que auth.php incluya session_start() o lo llame aquí si no lo hace.
global $conn;
include '../includes/auth.php';
// session_start(); // Generalmente auth.php o un archivo de inicio lo maneja. Si ya está, coméntalo.

if ($_SESSION['rol'] !== 'directora') {
    header("Location: ../../views/login.php"); // Asegúrate de que esta ruta sea correcta
    exit;
}

include '../includes/conexion.php';

$id_editar = null;
$datos_editar = null;
$mensaje_exito = ''; // Para mensajes de éxito
$mensaje_error = ''; // Para mensajes de error
$calificaciones = []; // ¡CORRECCIÓN CLAVE! Inicializa $calificaciones como un array vacío.

try {
    if (isset($_GET['editar'])) {
        $id_editar = $_GET['editar'];
        // Usar mayúsculas para nombres de columna si Oracle devuelve así (ej: ID_CALIFICACION, ID_ALUMNO, ID_CURSO, NOTA)
        $sql = "SELECT ID_CALIFICACION, ID_ALUMNO, ID_CURSO, NOTA FROM calificaciones WHERE ID_CALIFICACION = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id_editar, PDO::PARAM_INT); // Especificar tipo para seguridad
        $stmt->execute();
        $datos_editar = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos_editar) {
            $mensaje_error = "Calificación no encontrada para edición.";
            $id_editar = null; // Resetear para que el formulario actúe como "agregar"
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
        $id_alumno = $_POST['id_alumno'];
        $id_curso = $_POST['id_curso'];
        $nota = $_POST['nota'];

        // Opcional: Verificar si ya existe una calificación para este alumno y curso
        // Si tu lógica permite múltiples calificaciones para el mismo alumno/curso, omite esto
        $sql_check = "SELECT COUNT(*) FROM calificaciones WHERE ID_ALUMNO = :id_alumno AND ID_CURSO = :id_curso";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
        $stmt_check->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
        $stmt_check->execute();
        if ($stmt_check->fetchColumn() > 0) {
            $mensaje_error = "Error: Ya existe una calificación para este alumno en este curso.";
        } else {
            // CORRECCIÓN: Usar FECHA_REGISTRO si así se llama tu columna en Oracle. Si es solo FECHA, ajústalo.
            $sql = "INSERT INTO calificaciones (ID_CALIFICACION, ID_ALUMNO, ID_CURSO, NOTA, FECHA) VALUES (calificaciones_seq.NEXTVAL, :id_alumno, :id_curso, :nota, SYSDATE)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
            $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
            $stmt->bindParam(':nota', $nota, PDO::PARAM_STR); // Usar STR para FLOAT/DECIMAL
            $stmt->execute();
            $mensaje_exito = "Calificación agregada con éxito.";
            // Limpiar datos del formulario después de agregar exitosamente
            $_POST = array();
            header("Location: calificaciones.php?msg=added"); // Redireccionar para mostrar mensaje
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
        $id = $_POST['id_calificacion'];
        $id_alumno = $_POST['id_alumno'];
        $id_curso = $_POST['id_curso'];
        $nota = $_POST['nota'];

        // Opcional: Verificar si la nueva combinación alumno-curso ya existe en otra calificación
        $sql_check = "SELECT COUNT(*) FROM calificaciones WHERE ID_ALUMNO = :id_alumno AND ID_CURSO = :id_curso AND ID_CALIFICACION != :id";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
        $stmt_check->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
        $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_check->execute();
        if ($stmt_check->fetchColumn() > 0) {
            $mensaje_error = "Error: Esta combinación de alumno y curso ya existe en otra calificación.";
        } else {
            $sql = "UPDATE calificaciones SET ID_ALUMNO = :id_alumno, ID_CURSO = :id_curso, NOTA = :nota WHERE ID_CALIFICACION = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
            $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
            $stmt->bindParam(':nota', $nota, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $mensaje_exito = "Calificación actualizada con éxito.";
            header("Location: calificaciones.php?msg=updated");
            exit;
        }
    }

    if (isset($_GET['eliminar'])) {
        $id = $_GET['eliminar'];
        $sql = "DELETE FROM calificaciones WHERE ID_CALIFICACION = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $mensaje_exito = "Calificación eliminada con éxito.";
        header("Location: calificaciones.php?msg=deleted");
        exit;
    }

    // Mensajes de URL (para redirecciones de éxito)
    if (isset($_GET['msg'])) {
        if ($_GET['msg'] === 'updated') {
            $mensaje_exito = "Calificación actualizada con éxito.";
        } elseif ($_GET['msg'] === 'deleted') {
            $mensaje_exito = "Calificación eliminada con éxito.";
        } elseif ($_GET['msg'] === 'added') {
            $mensaje_exito = "Calificación agregada con éxito.";
        }
    }

    $stmt_alumnos = $conn->prepare("SELECT ID_ALUMNO, NOMBRE FROM alumnos WHERE ESTADO = 'Activo' ORDER BY NOMBRE"); // Mayúsculas y orden
    $stmt_alumnos->execute();
    $alumnos = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);

    $stmt_cursos = $conn->prepare("SELECT ID_CURSO, NOMBRE FROM cursos ORDER BY NOMBRE"); // Mayúsculas y orden
    $stmt_cursos->execute();
    $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);

    // CORRECCIÓN: Usar FECHA_REGISTRO en el SELECT si así se llama tu columna
    $sql = "SELECT c.ID_CALIFICACION, a.NOMBRE AS ALUMNO, cu.NOMBRE AS CURSO, c.NOTA, c.FECHA AS FECHA
            FROM calificaciones c
            JOIN alumnos a ON c.ID_ALUMNO = a.ID_ALUMNO
            JOIN cursos cu ON c.ID_CURSO = cu.ID_CURSO
            ORDER BY c.FECHA DESC"; // Ordenar por fecha
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $calificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC); // Esta asignación solo ocurre si no hay error

} catch (PDOException $e) {
    $mensaje_error = "Error en la operación de base de datos: " . $e->getMessage();
    // En un entorno de producción, puedes registrar el error: error_log($e->getMessage());
    // $calificaciones ya está inicializada como [], así que no es necesario re-inicializar aquí.
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Calificaciones - Directora</title>
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
        .container {
            background-color: #ffffff;
            padding: 2.5rem; /* Más padding */
            border-radius: 0.75rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
            min-height: calc(100vh - 120px); /* Ajusta según la altura de tu navbar y footer si tuvieras */
        }
        .form-control, .form-select {
            border-radius: 0.5rem;
            border-color: #ced4da;
        }
        .form-label {
            font-weight: 500;
            color: #344767;
        }
        .btn-primary, .btn-success, .btn-warning, .btn-danger, .btn-outline-secondary {
            border-radius: 0.5rem;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
            transition: background-color .2s ease-in-out, border-color .2s ease-in-out, box-shadow .2s ease-in-out;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.1);
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529; /* Color de texto para el botón de advertencia */
        }
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .btn-outline-danger {
            /* Estilo específico para el botón de cerrar sesión */
            color: #dc3545;
            border-color: #dc3545;
        }
        .btn-outline-danger:hover {
            background-color: #dc3545;
            color: #fff;
        }
        .btn-outline-secondary {
            color: #6c757d;
            border-color: #6c757d;
        }
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #fff;
        }
        .table thead {
            background-color: #e9ecef;
        }
        .table th {
            color: #344767;
            font-weight: 600;
        }
        .table td {
            vertical-align: middle;
        }
        h1, h3 {
            color: #344767; /* Color oscuro para títulos */
        }
        .alert {
            border-radius: 0.5rem;
        }
        .section-header {
            background-color: #f0f2f5; /* Un gris muy claro para diferenciar secciones */
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-header h3 {
            margin-bottom: 0;
            color: #0d6efd; /* Color primario para títulos de sección */
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg py-3 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard_directora.php">
            <i class="bi bi-mortarboard-fill me-2"></i>
            Sistema Escolar
        </a>
        <div class="d-flex align-items-center">
            <span class="navbar-text me-3 fw-bold">
                Bienvenida, <span class="text-primary"><?= htmlspecialchars($_SESSION['usuario'] ?? 'Usuario'); ?></span>
            </span>
            <a href="../views/logout.php" class="btn btn-outline-danger d-flex align-items-center">
                <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
            </a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0 text-primary">Gestión de Calificaciones</h1>
        <a href="dashboard_directora.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
        </a>
    </div>

    <?php if ($mensaje_exito): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $mensaje_exito ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($mensaje_error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $mensaje_error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="section-header">
        <i class="bi bi-journal-check fs-4 text-primary"></i>
        <h3><?= $id_editar ? 'Editar Calificación' : 'Registrar Nueva Calificación' ?></h3>
    </div>
    <form action="calificaciones.php" method="POST" class="mb-5 p-4 border rounded shadow-sm bg-light">
        <input type="hidden" name="id_calificacion" value="<?= htmlspecialchars($datos_editar['ID_CALIFICACION'] ?? '') ?>">

        <div class="row g-3">
            <div class="col-md-4">
                <label for="id_alumno" class="form-label">Alumno</label>
                <select name="id_alumno" id="id_alumno" class="form-select" required>
                    <option value="">-- Seleccione alumno --</option>
                    <?php foreach ($alumnos as $alumno): ?>
                        <option value="<?= htmlspecialchars($alumno['ID_ALUMNO']) ?>"
                            <?= (isset($datos_editar['ID_ALUMNO']) && $datos_editar['ID_ALUMNO'] == $alumno['ID_ALUMNO']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($alumno['NOMBRE']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="id_curso" class="form-label">Curso</label>
                <select name="id_curso" id="id_curso" class="form-select" required>
                    <option value="">-- Seleccione curso --</option>
                    <?php foreach ($cursos as $curso): ?>
                        <option value="<?= htmlspecialchars($curso['ID_CURSO']) ?>"
                            <?= (isset($datos_editar['ID_CURSO']) && $datos_editar['ID_CURSO'] == $curso['ID_CURSO']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($curso['NOMBRE']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="nota" class="form-label">Nota</label>
                <input type="number" name="nota" id="nota" class="form-control" min="0" max="100" step="0.01" placeholder="Ej: 85.50" value="<?= htmlspecialchars($datos_editar['NOTA'] ?? '') ?>" required>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12 text-center">
                <?php if (!$id_editar): ?>
                    <button type="submit" name="agregar" class="btn btn-success d-flex align-items-center justify-content-center mx-auto">
                        <i class="bi bi-plus-circle me-2"></i>
                        Agregar Calificación
                    </button>
                <?php else: ?>
                    <button type="submit" name="actualizar" class="btn btn-primary d-flex align-items-center justify-content-center mx-auto">
                        <i class="bi bi-arrow-repeat me-2"></i>
                        Actualizar Calificación
                    </button>
                    <a href="calificaciones.php" class="btn btn-secondary mt-2 d-flex align-items-center justify-content-center mx-auto" style="max-width: 200px;">
                        <i class="bi bi-x-circle me-2"></i>Cancelar Edición
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <div class="section-header mt-5">
        <i class="bi bi-list-check fs-4 text-primary"></i>
        <h3>Calificaciones Existentes</h3>
    </div>
    <div class="table-responsive p-4 border rounded shadow-sm bg-light">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Alumno</th>
                <th scope="col">Curso</th>
                <th scope="col">Nota</th>
                <th scope="col">Fecha</th>
                <th scope="col" class="text-center">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (count($calificaciones) > 0): ?>
                <?php foreach ($calificaciones as $fila): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['ID_CALIFICACION']) ?></td>
                        <td><?= htmlspecialchars($fila['ALUMNO']) ?></td>
                        <td><?= htmlspecialchars($fila['CURSO']) ?></td>
                        <td><?= htmlspecialchars(number_format($fila['NOTA'], 2)) ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($fila['FECHA']))) ?></td>
                        <td class="text-center">
                            <a href="calificaciones.php?editar=<?= htmlspecialchars($fila['ID_CALIFICACION']) ?>" class="btn btn-sm btn-warning me-1" title="Editar">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <a href="calificaciones.php?eliminar=<?= htmlspecialchars($fila['ID_CALIFICACION']) ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro de que desea eliminar esta calificación? Esta acción es irreversible.');">
                                <i class="bi bi-trash"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-4">No hay calificaciones registradas.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>