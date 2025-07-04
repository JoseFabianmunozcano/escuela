<?php
global $conn;
include '../includes/auth.php';

if ($_SESSION['rol'] !== 'directora') {
    header("Location: ../views/login.php");
    exit;
}

include '../includes/conexion.php';

$id_editar = null;
$datos_editar = null;
$error_message = ''; // Variable para mensajes de error
$success_message = ''; // Variable para mensajes de éxito

if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $sql = "SELECT ID_CURSO, ID_CARRERA, NOMBRE, DESCRIPCION FROM cursos WHERE id_curso = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_editar);
    try {
        $stmt->execute();
        $datos_editar = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$datos_editar) {
            $error_message = "Curso no encontrado para edición.";
            $id_editar = null; // Reset id_editar if not found
        }
    } catch (PDOException $e) {
        $error_message = "Error al cargar curso para edición: " . htmlspecialchars($e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $id_carrera = $_POST['id_carrera'];
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);

    if (empty($id_carrera) || empty($nombre) || empty($descripcion)) {
        $error_message = "Todos los campos (Carrera, Nombre, Descripción) son obligatorios.";
    } else {
        // Optional: Check for duplicate course name within the same career if needed
        // $sql_check = "SELECT COUNT(*) FROM cursos WHERE id_carrera = :id_carrera AND LOWER(nombre) = LOWER(:nombre)";
        // $stmt_check = $conn->prepare($sql_check);
        // $stmt_check->bindParam(':id_carrera', $id_carrera);
        // $stmt_check->bindParam(':nombre', $nombre);
        // try {
        //     $stmt_check->execute();
        //     if ($stmt_check->fetchColumn() > 0) {
        //         $error_message = "Ya existe un curso con ese nombre en la carrera seleccionada.";
        //     } else {
        $sql = "INSERT INTO cursos (id_curso, id_carrera, nombre, descripcion)
                        VALUES (cursos_seq.NEXTVAL, :id_carrera, :nombre, :descripcion)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_carrera', $id_carrera);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        try {
            $stmt->execute();
            $success_message = "Curso agregado exitosamente.";
            header("Location: cursos.php?success=add"); // Redirect to prevent form re-submission
            exit;
        } catch (PDOException $e) {
            $error_message = "Error al agregar curso: " . htmlspecialchars($e->getMessage());
        }
        //     }
        // } catch (PDOException $e) {
        //     $error_message = "Error de base de datos al verificar curso: " . htmlspecialchars($e->getMessage());
        // }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_curso = $_POST['id_curso'];
    $id_carrera = $_POST['id_carrera'];
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);

    if (empty($id_curso) || empty($id_carrera) || empty($nombre) || empty($descripcion)) {
        $error_message = "Todos los campos son obligatorios para actualizar el curso.";
    } else {
        // Optional: Check for duplicate course name within the same career (excluding current course)
        // $sql_check = "SELECT COUNT(*) FROM cursos WHERE id_carrera = :id_carrera AND LOWER(nombre) = LOWER(:nombre) AND id_curso != :id_curso";
        // $stmt_check = $conn->prepare($sql_check);
        // $stmt_check->bindParam(':id_carrera', $id_carrera);
        // $stmt_check->bindParam(':nombre', $nombre);
        // $stmt_check->bindParam(':id_curso', $id_curso);
        // try {
        //     $stmt_check->execute();
        //     if ($stmt_check->fetchColumn() > 0) {
        //         $error_message = "Ya existe otro curso con ese nombre en la carrera seleccionada.";
        //     } else {
        $sql = "UPDATE cursos SET id_carrera = :id_carrera, nombre = :nombre, descripcion = :descripcion WHERE id_curso = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_carrera', $id_carrera);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id', $id_curso);
        try {
            $stmt->execute();
            $success_message = "Curso actualizado exitosamente.";
            header("Location: cursos.php?success=update");
            exit;
        } catch (PDOException $e) {
            $error_message = "Error al actualizar curso: " . htmlspecialchars($e->getMessage());
        }
        //     }
        // } catch (PDOException $e) {
        //     $error_message = "Error de base de datos al verificar curso para actualización: " . htmlspecialchars($e->getMessage());
        // }
    }
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];

    // Optional: Check for foreign key constraints (e.g., if students are enrolled in this course)
    // For example:
    // $sql_check_enrollments = "SELECT COUNT(*) FROM inscripciones WHERE id_curso = :id";
    // $stmt_check_enrollments = $conn->prepare($sql_check_enrollments);
    // $stmt_check_enrollments->bindParam(':id', $id_eliminar);
    // $stmt_check_enrollments->execute();
    // if ($stmt_check_enrollments->fetchColumn() > 0) {
    //     $error_message = "No se puede eliminar el curso porque tiene alumnos inscritos.";
    // } else {
    $sql = "DELETE FROM cursos WHERE id_curso = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_eliminar);
    try {
        $stmt->execute();
        $success_message = "Curso eliminado exitosamente.";
        header("Location: cursos.php?success=delete");
        exit;
    } catch (PDOException $e) {
        $error_message = "Error al eliminar curso: " . htmlspecialchars($e->getMessage());
    }
    // }
}

// Check for success messages from redirects
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'add') {
        $success_message = "Curso agregado exitosamente.";
    } elseif ($_GET['success'] == 'update') {
        $success_message = "Curso actualizado exitosamente.";
    } elseif ($_GET['success'] == 'delete') {
        $success_message = "Curso eliminado exitosamente.";
    }
}

// Obtener cursos con nombre de carrera
$sql = "SELECT c.id_curso, c.nombre, c.descripcion, ca.nombre AS nombre_carrera
        FROM cursos c
        JOIN carreras ca ON c.id_carrera = ca.id_carrera
        ORDER BY c.id_curso";
$stmt = $conn->prepare($sql);
try {
    $stmt->execute();
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error al cargar listado de cursos: " . htmlspecialchars($e->getMessage());
    $cursos = [];
}


// Obtener lista de carreras para el formulario
$sql_carreras = "SELECT id_carrera, nombre FROM carreras ORDER BY nombre";
$stmt = $conn->prepare($sql_carreras);
try {
    $stmt->execute();
    $carreras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error al cargar lista de carreras: " . htmlspecialchars($e->getMessage());
    $carreras = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: rgba(173, 172, 172, 0.3); /* Light grey background */
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        .navbar {
            background-color: #ffffff !important;
            border-bottom: 1px solid #e9ecef;
        }
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08); /* Stronger, softer shadow */
        }
        .card-header {
            background-color: #f1f5f9; /* Light header background */
            border-bottom: 1px solid #e2e8f0;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            padding: 1.5rem;
            font-weight: 600;
            color: #344767; /* A bit darker text for headers */
        }
        .table-dark th {
            background-color: #344767; /* Dark blue-grey for table header */
            color: white;
            font-weight: 600;
        }
        .table-hover tbody tr:hover {
            background-color: #e9ecef;
            cursor: pointer;
        }
        .form-control-lg, .form-select-lg {
            min-height: calc(1.5em + 1rem + 2px);
            padding: 0.5rem 1rem;
            font-size: 1.25rem;
        }
        .input-group-text {
            background-color: #e9ecef;
            border-right: none;
            border-radius: 0.375rem 0 0 0.375rem;
        }
        .input-group .form-control, .input-group .form-select {
            border-left: none;
        }
        .btn-icon {
            padding: 0.5rem 0.75rem;
            font-size: 1rem;
        }
        /* Custom styles for badge colors (if needed) */
        .badge.text-bg-success { background-color: #28a745 !important; }
        .badge.text-bg-secondary { background-color: #6c757d !important; }
        .badge.text-bg-primary { background-color: #007bff !important; }
        .badge.text-bg-info { background-color: #17a2b8 !important; color: white !important;}
        .badge.text-bg-warning { background-color: #ffc107 !important; color: #343a40 !important;}
        .badge.text-bg-danger { background-color: #dc3545 !important; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light py-3 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="#">
            <i class="bi bi-book-half me-2"></i> Gestión de Cursos
        </a>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="btn btn-outline-secondary" href="dashboard_directora.php">
                        <i class="bi bi-arrow-left me-1"></i> Volver al Dashboard
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="mb-4 text-center text-primary">Administración de Cursos</h1>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $success_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-5">
        <div class="card-header">
            <h5 class="mb-0 text-primary">
                <?php if ($id_editar): ?>
                    <i class="bi bi-pencil-square me-2"></i> Editar Curso
                <?php else: ?>
                    <i class="bi bi-journal-plus me-2"></i> Agregar Nuevo Curso
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" class="row g-4 align-items-end">
                <input type="hidden" name="id_curso" value="<?= htmlspecialchars($datos_editar['ID_CURSO'] ?? '') ?>">

                <div class="col-md-4">
                    <label for="id_carrera" class="form-label fw-bold">Carrera Asociada</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-mortarboard-fill"></i></span>
                        <select name="id_carrera" id="id_carrera" class="form-select form-select-lg" required>
                            <option value="">Seleccione una carrera</option>
                            <?php
                            $selected_carrera_id = $datos_editar['ID_CARRERA'] ?? '';
                            foreach ($carreras as $carrera):
                                $is_selected = ($carrera['ID_CARRERA'] == $selected_carrera_id) ? 'selected' : '';
                                ?>
                                <option value="<?= htmlspecialchars($carrera['ID_CARRERA']) ?>" <?= $is_selected ?>>
                                    <?= htmlspecialchars($carrera['NOMBRE']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="nombre" class="form-label fw-bold">Nombre del Curso</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-book-fill"></i></span>
                        <input type="text" name="nombre" id="nombre" class="form-control form-control-lg" placeholder="Ej. Álgebra Lineal" required value="<?= htmlspecialchars($datos_editar['NOMBRE'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="descripcion" class="form-label fw-bold">Descripción</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                        <input type="text" name="descripcion" id="descripcion" class="form-control form-control-lg" placeholder="Ej. Conceptos básicos y aplicaciones" required value="<?= htmlspecialchars($datos_editar['DESCRIPCION'] ?? '') ?>">
                    </div>
                </div>

                <div class="col-12 text-end mt-4">
                    <?php if ($id_editar): ?>
                        <a href="cursos.php" class="btn btn-outline-secondary btn-lg me-2">
                            <i class="bi bi-x-circle me-2"></i>Cancelar Edición
                        </a>
                    <?php endif; ?>
                    <button type="submit" name="<?= $id_editar ? 'actualizar' : 'agregar' ?>" class="btn btn-<?= $id_editar ? 'primary' : 'success' ?> btn-lg">
                        <?php if ($id_editar): ?>
                            <i class="bi bi-arrow-repeat me-2"></i> Actualizar Curso
                        <?php else: ?>
                            <i class="bi bi-plus-lg me-2"></i> Agregar Curso
                        <?php endif; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mt-5">
        <div class="card-header">
            <h5 class="mb-0 text-primary"><i class="bi bi-collection-fill me-2"></i> Listado de Cursos Registrados</h5>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark text-center">
                    <tr>
                        <th style="width: 10%;">ID</th>
                        <th style="width: 20%;">Carrera</th>
                        <th style="width: 25%;">Nombre del Curso</th>
                        <th style="width: 25%;">Descripción</th>
                        <th style="width: 20%;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($cursos)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay cursos registrados aún.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cursos as $curso): ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($curso['ID_CURSO']) ?></td>
                                <td><?= htmlspecialchars($curso['NOMBRE_CARRERA']) ?></td>
                                <td><?= htmlspecialchars($curso['NOMBRE']) ?></td>
                                <td><?= htmlspecialchars($curso['DESCRIPCION']) ?></td>
                                <td class="text-center">
                                    <a href="cursos.php?editar=<?= htmlspecialchars($curso['ID_CURSO']) ?>" class="btn btn-sm btn-warning btn-icon me-2" title="Editar Curso">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="cursos.php?eliminar=<?= htmlspecialchars($curso['ID_CURSO']) ?>" class="btn btn-sm btn-danger btn-icon" title="Eliminar Curso" onclick="return confirm('¿Está seguro de eliminar este curso? Esta acción no se puede deshacer y puede afectar inscripciones o asignaciones a docentes.')">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>