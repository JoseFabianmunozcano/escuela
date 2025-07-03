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

if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    $sql = "SELECT * FROM docentes WHERE id_docente = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_editar);
    try {
        $stmt->execute();
        $datos_editar = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al cargar docente para edición: " . $e->getMessage() . "</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $especialidad = $_POST['especialidad'];
    $id_usuario = $_POST['id_usuario'];

    $sql = "INSERT INTO docentes (id_docente, nombre, especialidad, id_usuario)
            VALUES (docentes_seq.NEXTVAL, :nombre, :especialidad, :id_usuario)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':especialidad', $especialidad);
    $stmt->bindParam(':id_usuario', $id_usuario);
    try {
        $stmt->execute();
        header("Location: docentes.php"); // Redirect to prevent form re-submission
        exit;
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al agregar docente: " . $e->getMessage() . "</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_docente = $_POST['id_docente'];
    $nombre = $_POST['nombre'];
    $especialidad = $_POST['especialidad'];
    $id_usuario = $_POST['id_usuario'];

    $sql = "UPDATE docentes SET nombre = :nombre, especialidad = :especialidad, id_usuario = :id_usuario WHERE id_docente = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':especialidad', $especialidad);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':id', $id_docente);
    try {
        $stmt->execute();
        header("Location: docentes.php");
        exit;
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al actualizar docente: " . $e->getMessage() . "</div>";
    }
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $sql = "DELETE FROM docentes WHERE id_docente = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_eliminar);
    try {
        $stmt->execute();
        header("Location: docentes.php");
        exit;
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al eliminar docente: " . $e->getMessage() . "</div>";
    }
}

// Obtener docentes con nombre de usuario
$sql = "SELECT d.id_docente, d.nombre, d.especialidad, u.nombre AS nombre_usuario
        FROM docentes d
        JOIN usuarios u ON d.id_usuario = u.id_usuario
        ORDER BY d.nombre"; // Order by name for consistent display
$stmt = $conn->prepare($sql);
try {
    $stmt->execute();
    $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar listado de docentes: " . $e->getMessage() . "</div>";
    $docentes = []; // Ensure $docentes is an empty array to prevent issues in foreach
}

// Lista de usuarios para seleccionar en el formulario
// Consider filtering users to only those with 'docente' role if applicable
$sql_usuarios = "SELECT id_usuario, nombre FROM usuarios WHERE rol = 'docente' ORDER BY nombre"; // Assuming 'rol' column exists in 'usuarios'
$stmt = $conn->prepare($sql_usuarios);
try {
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar lista de usuarios: " . $e->getMessage() . "</div>";
    $usuarios = []; // Ensure $usuarios is an empty array
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Docentes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: rgba(105, 104, 104, 0.3); /* Light grey background */
        }
        .navbar {
            background-color: #ffffff !important; /* White navbar */
            border-bottom: 1px solid #e9ecef; /* Subtle border */
        }
        .card {
            border: none; /* Remove default card border */
            border-radius: 0.75rem; /* More rounded corners for cards */
        }
        .table-dark th {
            background-color: #343a40; /* Darker header for table */
            color: white;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light py-3 shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="#">
            <i class="fas fa-chalkboard-teacher me-2"></i> Gestión de Docentes
        </a>
        <div class="collapse navbar-collapse justify-content-end">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="btn btn-outline-secondary" href="dashboard_directora.php">
                        <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="mb-4 text-center">Administración de Docentes</h1>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white pt-4 pb-0">
            <h5 class="card-title text-center text-primary"><?= $id_editar ? 'Editar Docente' : 'Agregar Nuevo Docente' ?></h5>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3 align-items-center justify-content-center">
                <input type="hidden" name="id_docente" value="<?= htmlspecialchars($datos_editar['ID_DOCENTE'] ?? '') ?>">
                <div class="col-md-3">
                    <input type="text" name="nombre" class="form-control form-control-lg" placeholder="Nombre del docente" required value="<?= htmlspecialchars($datos_editar['NOMBRE'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="especialidad" class="form-control form-control-lg" placeholder="Especialidad" required value="<?= htmlspecialchars($datos_editar['ESPECIALIDAD'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select name="id_usuario" class="form-select form-select-lg" required>
                        <option value="">Seleccione usuario</option>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= htmlspecialchars($usuario['ID_USUARIO']) ?>" <?= isset($datos_editar['ID_USUARIO']) && $datos_editar['ID_USUARIO'] == $usuario['ID_USUARIO'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($usuario['NOMBRE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" name="<?= $id_editar ? 'actualizar' : 'agregar' ?>" class="btn btn-<?= $id_editar ? 'primary' : 'success' ?> btn-lg w-100">
                        <?php if ($id_editar): ?>
                            <i class="fas fa-save me-2"></i> Actualizar
                        <?php else: ?>
                            <i class="fas fa-plus me-2"></i> Agregar
                        <?php endif; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mt-5">
        <div class="card-header bg-white pt-4 pb-0">
            <h5 class="card-title text-center text-primary">Listado de Docentes</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped mt-3">
                    <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Especialidad</th>
                        <th>Usuario Asignado</th>
                        <th class="text-center">Acciones</th>
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
                                <td><?= htmlspecialchars($docente['ID_DOCENTE']) ?></td>
                                <td><?= htmlspecialchars($docente['NOMBRE']) ?></td>
                                <td><?= htmlspecialchars($docente['ESPECIALIDAD']) ?></td>
                                <td><?= htmlspecialchars($docente['NOMBRE_USUARIO']) ?></td>
                                <td class="text-center">
                                    <a href="docentes.php?editar=<?= htmlspecialchars($docente['ID_DOCENTE']) ?>" class="btn btn-sm btn-warning me-2">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="docentes.php?eliminar=<?= htmlspecialchars($docente['ID_DOCENTE']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este docente? Esta acción no se puede deshacer.')">
                                        <i class="fas fa-trash-alt"></i> Eliminar
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