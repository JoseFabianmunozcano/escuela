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
    $sql = "SELECT ID_CARRERA, NOMBRE FROM carreras WHERE id_carrera = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_editar);
    try {
        $stmt->execute();
        $datos_editar = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$datos_editar) {
            $error_message = "Carrera no encontrada para edición.";
            $id_editar = null; // Reset id_editar if not found
        }
    } catch (PDOException $e) {
        $error_message = "Error al cargar datos de la carrera: " . htmlspecialchars($e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre']);

    if (empty($nombre)) {
        $error_message = "El nombre de la carrera es obligatorio.";
    } else {
        // Check if carrera already exists
        $sql_check = "SELECT COUNT(*) FROM carreras WHERE LOWER(nombre) = LOWER(:nombre)";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bindParam(':nombre', $nombre);
        try {
            $stmt_check->execute();
            if ($stmt_check->fetchColumn() > 0) {
                $error_message = "Ya existe una carrera con ese nombre.";
            } else {
                // In Oracle, you usually use a sequence for auto-incrementing IDs
                $sql = "INSERT INTO carreras (id_carrera, nombre)
                        VALUES (carreras_seq.NEXTVAL, :nombre)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':nombre', $nombre);
                try {
                    $stmt->execute();
                    $success_message = "Carrera agregada exitosamente.";
                    header("Location: carreras.php?success=add"); // Redirect to prevent re-submission
                    exit;
                } catch (PDOException $e) {
                    $error_message = "Error al agregar carrera: " . htmlspecialchars($e->getMessage());
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al verificar carrera: " . htmlspecialchars($e->getMessage());
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_carrera = $_POST['id_carrera'];
    $nombre = trim($_POST['nombre']);

    if (empty($id_carrera) || empty($nombre)) {
        $error_message = "ID y nombre de la carrera son obligatorios para actualizar.";
    } else {
        // Check if carrera name already exists for another ID
        $sql_check = "SELECT COUNT(*) FROM carreras WHERE LOWER(nombre) = LOWER(:nombre) AND id_carrera != :id_carrera";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bindParam(':nombre', $nombre);
        $stmt_check->bindParam(':id_carrera', $id_carrera);
        try {
            $stmt_check->execute();
            if ($stmt_check->fetchColumn() > 0) {
                $error_message = "Ya existe otra carrera con ese nombre.";
            } else {
                $sql = "UPDATE carreras SET nombre = :nombre WHERE id_carrera = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':id', $id_carrera);
                try {
                    $stmt->execute();
                    $success_message = "Carrera actualizada exitosamente.";
                    header("Location: carreras.php?success=update");
                    exit;
                } catch (PDOException $e) {
                    $error_message = "Error al actualizar carrera: " . htmlspecialchars($e->getMessage());
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al verificar carrera para actualización: " . htmlspecialchars($e->getMessage());
        }
    }
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];

    // Check for foreign key constraints (e.g., if students are linked to this career)
    $sql_check_students = "SELECT COUNT(*) FROM alumnos WHERE id_carrera = :id";
    $stmt_check_students = $conn->prepare($sql_check_students);
    $stmt_check_students->bindParam(':id', $id_eliminar);
    $stmt_check_students->execute();

    if ($stmt_check_students->fetchColumn() > 0) {
        $error_message = "No se puede eliminar la carrera porque tiene alumnos asociados.";
    } else {
        $sql = "DELETE FROM carreras WHERE id_carrera = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id_eliminar);
        try {
            $stmt->execute();
            $success_message = "Carrera eliminada exitosamente.";
            header("Location: carreras.php?success=delete");
            exit;
        } catch (PDOException $e) {
            $error_message = "Error al eliminar carrera: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Check for success messages from redirects
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'add') {
        $success_message = "Carrera agregada exitosamente.";
    } elseif ($_GET['success'] == 'update') {
        $success_message = "Carrera actualizada exitosamente.";
    } elseif ($_GET['success'] == 'delete') {
        $success_message = "Carrera eliminada exitosamente.";
    }
}

$sql = "SELECT ID_CARRERA, NOMBRE FROM carreras ORDER BY ID_CARRERA";
$stmt = $conn->prepare($sql);
try {
    $stmt->execute();
    $carreras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error al cargar listado de carreras: " . htmlspecialchars($e->getMessage());
    $carreras = []; // Ensure $carreras is an empty array
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Carreras</title>
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
        /* Custom styles for badge colors (if needed, though not for careers directly) */
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
        <a class="navbar-brand fw-bold text-primary" href="dashboard_directora.php">
            <i class="bi bi-mortarboard-fill me-2"></i> Gestión de Carreras
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
    <h1 class="mb-4 text-center text-primary">Administración de Carreras</h1>

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
                    <i class="bi bi-pencil-square me-2"></i> Editar Carrera
                <?php else: ?>
                    <i class="bi bi-plus-circle me-2"></i> Agregar Nueva Carrera
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" class="row g-4 align-items-end"> <input type="hidden" name="id_carrera" value="<?= htmlspecialchars($datos_editar['ID_CARRERA'] ?? '') ?>">
                <div class="col-md-9">
                    <label for="nombre" class="form-label fw-bold">Nombre de la Carrera</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-book-fill"></i></span>
                        <input type="text" name="nombre" id="nombre" class="form-control form-control-lg" placeholder="Ej. Ingeniería en Sistemas Computacionales" required value="<?= htmlspecialchars($datos_editar['NOMBRE'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <?php if ($id_editar): ?>
                        <a href="carreras.php" class="btn btn-outline-secondary btn-lg w-100 mb-2 mb-md-0"> <i class="bi bi-x-circle me-2"></i>Cancelar
                        </a>
                    <?php endif; ?>
                    <button type="submit" name="<?= $id_editar ? 'actualizar' : 'agregar' ?>" class="btn btn-<?= $id_editar ? 'primary' : 'success' ?> btn-lg w-100">
                        <?php if ($id_editar): ?>
                            <i class="bi bi-arrow-repeat me-2"></i> Actualizar Carrera
                        <?php else: ?>
                            <i class="bi bi-plus-lg me-2"></i> Agregar Carrera
                        <?php endif; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mt-5">
        <div class="card-header">
            <h5 class="mb-0 text-primary"><i class="bi bi-list-columns-reverse me-2"></i> Listado de Carreras</h5>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark text-center">
                    <tr>
                        <th style="width: 10%;">ID</th>
                        <th>Nombre</th>
                        <th style="width: 25%;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($carreras)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">No hay carreras registradas aún.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($carreras as $carrera): ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($carrera['ID_CARRERA']) ?></td>
                                <td><?= htmlspecialchars($carrera['NOMBRE']) ?></td>
                                <td class="text-center">
                                    <a href="carreras.php?editar=<?= htmlspecialchars($carrera['ID_CARRERA']) ?>" class="btn btn-sm btn-warning btn-icon me-2" title="Editar Carrera">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="carreras.php?eliminar=<?= htmlspecialchars($carrera['ID_CARRERA']) ?>" class="btn btn-sm btn-danger btn-icon" title="Eliminar Carrera" onclick="return confirm('¿Está seguro de eliminar esta carrera? Esta acción no se puede deshacer y puede afectar a alumnos asociados.')">
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