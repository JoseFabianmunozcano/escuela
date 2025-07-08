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
$error_message = ''; // Variable to store error messages

if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $sql = "SELECT * FROM usuarios WHERE id_usuario = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_editar);
    try {
        $stmt->execute();
        $datos_editar = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message = "Error al cargar datos del usuario: " . htmlspecialchars($e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $password = $_POST['password']; // Get plain password
    $rol = $_POST['rol'];

    // Basic validation for new user
    if (empty($nombre) || empty($correo) || empty($password) || empty($rol)) {
        $error_message = "Todos los campos son obligatorios para registrar un nuevo usuario.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $sql_check_email = "SELECT COUNT(*) FROM usuarios WHERE correo = :correo";
        $stmt_check = $conn->prepare($sql_check_email);
        $stmt_check->bindParam(':correo', $correo);
        $stmt_check->execute();
        if ($stmt_check->fetchColumn() > 0) {
            $error_message = "El correo electrónico ya está registrado.";
        } else {
            $sql = "INSERT INTO usuarios (nombre, correo, password, rol, estado, fecharegistro) VALUES (:nombre, :correo, :password, :rol, 1, SYSDATE)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':rol', $rol);
            try {
                $stmt->execute();
                header("Location: usuarios.php");
                exit;
            } catch (PDOException $e) {
                $error_message = "Error al agregar usuario: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_usuario = $_POST['id_usuario'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];
    $estado = $_POST['estado'];

    // Basic validation for update
    if (empty($nombre) || empty($correo) || empty($rol)) {
        $error_message = "Nombre, correo y rol son obligatorios para actualizar el usuario.";
    } else {
        // Check if email already exists for another user
        $sql_check_email = "SELECT COUNT(*) FROM usuarios WHERE correo = :correo AND id_usuario != :id_usuario";
        $stmt_check = $conn->prepare($sql_check_email);
        $stmt_check->bindParam(':correo', $correo);
        $stmt_check->bindParam(':id_usuario', $id_usuario);
        $stmt_check->execute();
        if ($stmt_check->fetchColumn() > 0) {
            $error_message = "El correo electrónico ya está registrado para otro usuario.";
        } else {
            $sql = "UPDATE usuarios SET nombre = :nombre, correo = :correo, rol = :rol, estado = :estado WHERE id_usuario = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':rol', $rol);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id_usuario);
            try {
                $stmt->execute();
                header("Location: usuarios.php");
                exit;
            } catch (PDOException $e) {
                $error_message = "Error al actualizar usuario: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $sql = "DELETE FROM usuarios WHERE id_usuario = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_eliminar);
    try {
        $stmt->execute();
        header("Location: usuarios.php");
        exit;
    } catch (PDOException $e) {
        $error_message = "Error al eliminar usuario: " . htmlspecialchars($e->getMessage());
    }
}

// Obtener usuarios con fecha de registro formateada (if exists in your DB schema)
$sql = "SELECT id_usuario, nombre, correo, rol, estado, TO_CHAR(fecharegistro, 'DD/MM/YYYY HH24:MI') AS fecha_registro_fmt FROM usuarios ORDER BY id_usuario ASC";
$stmt = $conn->prepare($sql);
try {
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error al cargar listado de usuarios: " . htmlspecialchars($e->getMessage());
    $usuarios = []; // Ensure $usuarios is an empty array to prevent issues in foreach
}


// Variables para el diseño dinámico
$form_title = $id_editar ? 'Editar Información de Usuario' : 'Registrar Nuevo Usuario';
$button_text = $id_editar ? 'Actualizar Usuario' : 'Registrar Usuario';
$button_class = $id_editar ? 'btn-primary' : 'btn-success';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
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
        /* Custom styles for badge colors */
        .badge.text-bg-success { background-color: #28a745 !important; }
        .badge.text-bg-secondary { background-color: #6c757d !important; }
        .badge.text-bg-primary { background-color: #007bff !important; }
        .badge.text-bg-warning { background-color: #ffc107 !important; color: #343a40 !important;}
        .badge.text-bg-danger { background-color: #dc3545 !important; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light py-3 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="#">
            <i class="bi bi-person-circle me-2"></i> Gestión de Usuarios
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
    <h1 class="mb-4 text-center text-primary">Administración de Usuarios</h1>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-5">
        <div class="card-header">
            <h5 class="mb-0 text-primary">
                <?php if ($id_editar): ?>
                    <i class="bi bi-person-fill-gear me-2"></i> Editar Información de Usuario
                <?php else: ?>
                    <i class="bi bi-person-plus me-2"></i> Registrar Nuevo Usuario
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" class="row g-4">
                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($datos_editar['ID_USUARIO'] ?? '') ?>">

                <div class="col-md-4">
                    <label for="nombre" class="form-label fw-bold">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" name="nombre" id="nombre" class="form-control form-control-lg" placeholder="Ej. Juan Pérez" required value="<?= htmlspecialchars($datos_editar['NOMBRE'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="correo" class="form-label fw-bold">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                        <input type="email" name="correo" id="correo" class="form-control form-control-lg" placeholder="ejemplo@correo.com" required value="<?= htmlspecialchars($datos_editar['CORREO'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="rol" class="form-label fw-bold">Rol</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
                        <select name="rol" id="rol" class="form-select form-select-lg" required>
                            <option value="">Seleccione un rol</option>
                            <option value="directora" <?= (isset($datos_editar['ROL']) && $datos_editar['ROL'] == 'directora') ? 'selected' : '' ?>>Directora</option>
                            <option value="coordinador" <?= (isset($datos_editar['ROL']) && $datos_editar['ROL'] == 'coordinador') ? 'selected' : '' ?>>Coordinador</option>
                            <option value="cobranza" <?= (isset($datos_editar['ROL']) && $datos_editar['ROL'] == 'cobranza') ? 'selected' : '' ?>>Cobranza</option>
                            <option value="docente" <?= (isset($datos_editar['ROL']) && $datos_editar['ROL'] == 'docente') ? 'selected' : '' ?>>Docente</option>
                        </select>
                    </div>
                </div>

                <?php if (!$id_editar): ?>
                    <div class="col-md-6">
                        <label for="password" class="form-label fw-bold">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                            <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Contraseña para el usuario" required>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-md-6">
                        <label for="estado" class="form-label fw-bold">Estado del Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-toggle-on"></i></span>
                            <select name="estado" id="estado" class="form-select form-select-lg" required>
                                <option value="1" <?= (isset($datos_editar['ESTADO']) && $datos_editar['ESTADO'] == 1) ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= (isset($datos_editar['ESTADO']) && $datos_editar['ESTADO'] == 0) ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="col-12 text-end mt-4">
                    <?php if ($id_editar): ?>
                        <a href="usuarios.php" class="btn btn-outline-secondary btn-lg me-2">
                            <i class="bi bi-x-circle me-2"></i>Cancelar Edición
                        </a>
                    <?php endif; ?>
                    <button type="submit" name="<?= $id_editar ? 'actualizar' : 'agregar' ?>" class="btn <?= $button_class ?> btn-lg">
                        <?php if ($id_editar): ?>
                            <i class="bi bi-arrow-repeat me-2"></i> <?= $button_text ?>
                        <?php else: ?>
                            <i class="bi bi-person-plus me-2"></i> <?= $button_text ?>
                        <?php endif; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mt-5">
        <div class="card-header">
            <h5 class="mb-0 text-primary"><i class="bi bi-card-list me-2"></i> Listado de Usuarios Registrados</h5>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark text-center">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha de Registro</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No hay usuarios registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($usuario['ID_USUARIO']) ?></td>
                                <td><?= htmlspecialchars($usuario['NOMBRE']) ?></td>
                                <td><?= htmlspecialchars($usuario['CORREO']) ?></td>
                                <td class="text-center">
                                    <?php
                                    $rol_class = 'text-bg-secondary';
                                    if ($usuario['ROL'] == 'directora') $rol_class = 'text-bg-primary';
                                    else if ($usuario['ROL'] == 'coordinador') $rol_class = 'text-bg-info';
                                    else if ($usuario['ROL'] == 'cobranza') $rol_class = 'text-bg-warning';
                                    echo "<span class='badge {$rol_class}'>" . htmlspecialchars(ucfirst($usuario['ROL'])) . "</span>";
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $estado_class = ($usuario['ESTADO'] == 1) ? 'text-bg-success' : 'text-bg-danger';
                                    echo "<span class='badge {$estado_class}'>" . ($usuario['ESTADO'] == 1 ? 'Activo' : 'Inactivo') . "</span>";
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?= htmlspecialchars($usuario['FECHA_REGISTRO_FMT'] ?? 'N/A') ?>
                                </td>
                                <td class="text-center">
                                    <a href="usuarios.php?editar=<?= htmlspecialchars($usuario['ID_USUARIO']) ?>" class="btn btn-sm btn-warning btn-icon" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="usuarios.php?eliminar=<?= htmlspecialchars($usuario['ID_USUARIO']) ?>" class="btn btn-sm btn-danger btn-icon" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario? Esta acción es irreversible.')">
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