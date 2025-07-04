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
    $sql = "SELECT ID_DOCENTE, NOMBRE, ESPECIALIDAD, ID_USUARIO FROM docentes WHERE id_docente = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_editar);
    try {
        $stmt->execute();
        $datos_editar = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$datos_editar) {
            $error_message = "Docente no encontrado para edición.";
            $id_editar = null; // Reset id_editar if not found
        }
    } catch (PDOException $e) {
        $error_message = "Error al cargar docente para edición: " . htmlspecialchars($e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = trim($_POST['nombre']);
    $especialidad = trim($_POST['especialidad']);
    $id_usuario = $_POST['id_usuario'];

    if (empty($nombre) || empty($especialidad) || empty($id_usuario)) {
        $error_message = "Todos los campos (Nombre, Especialidad, Usuario Asignado) son obligatorios.";
    } else {
        // Check if id_usuario is already assigned to a docente
        $sql_check_user = "SELECT COUNT(*) FROM docentes WHERE id_usuario = :id_usuario";
        $stmt_check_user = $conn->prepare($sql_check_user);
        $stmt_check_user->bindParam(':id_usuario', $id_usuario);
        try {
            $stmt_check_user->execute();
            if ($stmt_check_user->fetchColumn() > 0) {
                $error_message = "Este usuario ya está asignado a otro docente. Por favor, seleccione un usuario diferente.";
            } else {
                $sql = "INSERT INTO docentes (id_docente, nombre, especialidad, id_usuario)
                        VALUES (docentes_seq.NEXTVAL, :nombre, :especialidad, :id_usuario)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':especialidad', $especialidad);
                $stmt->bindParam(':id_usuario', $id_usuario);
                try {
                    $stmt->execute();
                    $success_message = "Docente agregado exitosamente.";
                    header("Location: docentes.php?success=add"); // Redirect to prevent form re-submission
                    exit;
                } catch (PDOException $e) {
                    $error_message = "Error al agregar docente: " . htmlspecialchars($e->getMessage());
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al verificar usuario: " . htmlspecialchars($e->getMessage());
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_docente = $_POST['id_docente'];
    $nombre = trim($_POST['nombre']);
    $especialidad = trim($_POST['especialidad']);
    $id_usuario = $_POST['id_usuario'];

    if (empty($id_docente) || empty($nombre) || empty($especialidad) || empty($id_usuario)) {
        $error_message = "Todos los campos son obligatorios para actualizar el docente.";
    } else {
        // Check if id_usuario is already assigned to another docente (excluding current docente)
        $sql_check_user = "SELECT COUNT(*) FROM docentes WHERE id_usuario = :id_usuario AND id_docente != :id_docente";
        $stmt_check_user = $conn->prepare($sql_check_user);
        $stmt_check_user->bindParam(':id_usuario', $id_usuario);
        $stmt_check_user->bindParam(':id_docente', $id_docente);
        try {
            $stmt_check_user->execute();
            if ($stmt_check_user->fetchColumn() > 0) {
                $error_message = "Este usuario ya está asignado a otro docente. Por favor, seleccione un usuario diferente.";
            } else {
                $sql = "UPDATE docentes SET nombre = :nombre, especialidad = :especialidad, id_usuario = :id_usuario WHERE id_docente = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':especialidad', $especialidad);
                $stmt->bindParam(':id_usuario', $id_usuario);
                $stmt->bindParam(':id', $id_docente);
                try {
                    $stmt->execute();
                    $success_message = "Docente actualizado exitosamente.";
                    header("Location: docentes.php?success=update");
                    exit;
                } catch (PDOException $e) {
                    $error_message = "Error al actualizar docente: " . htmlspecialchars($e->getMessage());
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error de base de datos al verificar usuario para actualización: " . htmlspecialchars($e->getMessage());
        }
    }
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];

    // Optional: Check if there are foreign key constraints (e.g., if docente is linked to courses/classes)
    // For example:
    // $sql_check_cursos = "SELECT COUNT(*) FROM cursos WHERE id_docente = :id";
    // $stmt_check_cursos = $conn->prepare($sql_check_cursos);
    // $stmt_check_cursos->bindParam(':id', $id_eliminar);
    // $stmt_check_cursos->execute();
    // if ($stmt_check_cursos->fetchColumn() > 0) {
    //     $error_message = "No se puede eliminar el docente porque está asignado a cursos.";
    // } else {
    $sql = "DELETE FROM docentes WHERE id_docente = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_eliminar);
    try {
        $stmt->execute();
        $success_message = "Docente eliminado exitosamente.";
        header("Location: docentes.php?success=delete");
        exit;
    } catch (PDOException $e) {
        $error_message = "Error al eliminar docente: " . htmlspecialchars($e->getMessage());
    }
    // }
}

// Check for success messages from redirects
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'add') {
        $success_message = "Docente agregado exitosamente.";
    } elseif ($_GET['success'] == 'update') {
        $success_message = "Docente actualizado exitosamente.";
    } elseif ($_GET['success'] == 'delete') {
        $success_message = "Docente eliminado exitosamente.";
    }
}


// Obtener docentes con nombre de usuario
// Use LEFT JOIN to include docentes even if their id_usuario is not found in usuarios table (less likely but safer)
$sql = "SELECT d.id_docente, d.nombre, d.especialidad, NVL(u.nombre, 'Usuario No Asignado') AS nombre_usuario
        FROM docentes d
        LEFT JOIN usuarios u ON d.id_usuario = u.id_usuario
        ORDER BY d.nombre";
$stmt = $conn->prepare($sql);
try {
    $stmt->execute();
    $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error al cargar listado de docentes: " . htmlspecialchars($e->getMessage());
    $docentes = [];
}

// Lista de usuarios para seleccionar en el formulario
// Filter users to those with 'docente' role and who are not already assigned to a docente
$sql_usuarios = "
    SELECT u.id_usuario, u.nombre
    FROM usuarios u
    WHERE u.rol = 'docente'
    AND u.id_usuario NOT IN (SELECT d2.id_usuario FROM docentes d2 WHERE d2.id_usuario IS NOT NULL AND (:current_docente_id IS NULL OR d2.id_docente != :current_docente_id))
    ORDER BY u.nombre";
$stmt = $conn->prepare($sql_usuarios);
$stmt->bindParam(':current_docente_id', $id_editar, PDO::PARAM_INT); // Pass null if adding, or current docente ID if editing
try {
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error al cargar lista de usuarios para asignación: " . htmlspecialchars($e->getMessage());
    $usuarios = [];
}
// Add the currently assigned user to the list if editing, so they can keep their assignment
if ($id_editar && $datos_editar && !empty($datos_editar['ID_USUARIO'])) {
    $current_user_assigned = false;
    foreach ($usuarios as $user) {
        if ($user['ID_USUARIO'] == $datos_editar['ID_USUARIO']) {
            $current_user_assigned = true;
            break;
        }
    }
    if (!$current_user_assigned) {
        // Fetch the name of the currently assigned user
        $sql_current_user = "SELECT id_usuario, nombre FROM usuarios WHERE id_usuario = :id_usuario";
        $stmt_current_user = $conn->prepare($sql_current_user);
        $stmt_current_user->bindParam(':id_usuario', $datos_editar['ID_USUARIO']);
        $stmt_current_user->execute();
        $current_user_data = $stmt_current_user->fetch(PDO::FETCH_ASSOC);
        if ($current_user_data) {
            // Add it to the beginning of the list, or in a sorted manner if preferred
            array_unshift($usuarios, $current_user_data);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Docentes</title>
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
        /* Custom styles for badge colors (if needed, though not for docentes directly) */
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
            <i class="bi bi-person-workspace me-2"></i> Gestión de Docentes
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
    <h1 class="mb-4 text-center text-primary">Administración de Docentes</h1>

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
                    <i class="bi bi-pencil-square me-2"></i> Editar Docente
                <?php else: ?>
                    <i class="bi bi-person-plus-fill me-2"></i> Agregar Nuevo Docente
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" class="row g-4 align-items-end">
                <input type="hidden" name="id_docente" value="<?= htmlspecialchars($datos_editar['ID_DOCENTE'] ?? '') ?>">

                <div class="col-md-4">
                    <label for="nombre" class="form-label fw-bold">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" name="nombre" id="nombre" class="form-control form-control-lg" placeholder="Ej. Dr. Carlos Soto" required value="<?= htmlspecialchars($datos_editar['NOMBRE'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="especialidad" class="form-label fw-bold">Especialidad</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-journal-richtext"></i></span>
                        <input type="text" name="especialidad" id="especialidad" class="form-control form-control-lg" placeholder="Ej. Matemáticas, Programación" required value="<?= htmlspecialchars($datos_editar['ESPECIALIDAD'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="id_usuario" class="form-label fw-bold">Usuario Asignado (Rol Docente)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                        <select name="id_usuario" id="id_usuario" class="form-select form-select-lg" required>
                            <option value="">Seleccione un usuario</option>
                            <?php
                            $selected_user_id = $datos_editar['ID_USUARIO'] ?? '';
                            foreach ($usuarios as $usuario):
                                $is_selected = ($usuario['ID_USUARIO'] == $selected_user_id) ? 'selected' : '';
                                ?>
                                <option value="<?= htmlspecialchars($usuario['ID_USUARIO']) ?>" <?= $is_selected ?>>
                                    <?= htmlspecialchars($usuario['NOMBRE']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-12 text-end mt-4">
                    <?php if ($id_editar): ?>
                        <a href="docentes.php" class="btn btn-outline-secondary btn-lg me-2">
                            <i class="bi bi-x-circle me-2"></i>Cancelar Edición
                        </a>
                    <?php endif; ?>
                    <button type="submit" name="<?= $id_editar ? 'actualizar' : 'agregar' ?>" class="btn btn-<?= $id_editar ? 'primary' : 'success' ?> btn-lg">
                        <?php if ($id_editar): ?>
                            <i class="bi bi-arrow-repeat me-2"></i> Actualizar Docente
                        <?php else: ?>
                            <i class="bi bi-person-plus-fill me-2"></i> Agregar Docente
                        <?php endif; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mt-5">
        <div class="card-header">
            <h5 class="mb-0 text-primary"><i class="bi bi-people-fill me-2"></i> Listado de Docentes Registrados</h5>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark text-center">
                    <tr>
                        <th style="width: 10%;">ID</th>
                        <th style="width: 25%;">Nombre</th>
                        <th style="width: 25%;">Especialidad</th>
                        <th style="width: 20%;">Usuario Asignado</th>
                        <th style="width: 20%;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($docentes)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No hay docentes registrados aún.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($docentes as $docente): ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($docente['ID_DOCENTE']) ?></td>
                                <td><?= htmlspecialchars($docente['NOMBRE']) ?></td>
                                <td><?= htmlspecialchars($docente['ESPECIALIDAD']) ?></td>
                                <td><?= htmlspecialchars($docente['NOMBRE_USUARIO']) ?></td>
                                <td class="text-center">
                                    <a href="docentes.php?editar=<?= htmlspecialchars($docente['ID_DOCENTE']) ?>" class="btn btn-sm btn-warning btn-icon me-2" title="Editar Docente">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="docentes.php?eliminar=<?= htmlspecialchars($docente['ID_DOCENTE']) ?>" class="btn btn-sm btn-danger btn-icon" title="Eliminar Docente" onclick="return confirm('¿Está seguro de eliminar este docente? Esta acción no se puede deshacer y puede afectar cursos asociados.')">
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