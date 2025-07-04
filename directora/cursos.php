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
    $sql = "SELECT * FROM cursos WHERE id_curso = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_editar);
    $stmt->execute();
    $datos_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $id_carrera = $_POST['id_carrera'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $sql = "INSERT INTO cursos (id_curso, id_carrera, nombre, descripcion)
            VALUES (cursos_seq.NEXTVAL, :id_carrera, :nombre, :descripcion)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_carrera', $id_carrera);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':descripcion', $descripcion);
    try {
        $stmt->execute();
        header("Location: cursos.php"); // Redirect to prevent form re-submission
        exit;
    } catch (PDOException $e) {
        // Handle error, e.g., show a user-friendly message
        echo "<div class='alert alert-danger'>Error al agregar curso: " . $e->getMessage() . "</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_curso = $_POST['id_curso'];
    $id_carrera = $_POST['id_carrera'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $sql = "UPDATE cursos SET id_carrera = :id_carrera, nombre = :nombre, descripcion = :descripcion WHERE id_curso = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_carrera', $id_carrera);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':id', $id_curso);
    try {
        $stmt->execute();
        header("Location: cursos.php");
        exit;
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al actualizar curso: " . $e->getMessage() . "</div>";
    }
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $sql = "DELETE FROM cursos WHERE id_curso = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_eliminar);
    try {
        $stmt->execute();
        header("Location: cursos.php");
        exit;
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al eliminar curso: " . $e->getMessage() . "</div>";
    }
}

// Obtener cursos con nombre de carrera
$sql = "SELECT c.id_curso, c.nombre, c.descripcion, ca.nombre AS nombre_carrera
        FROM cursos c
        JOIN carreras ca ON c.id_carrera = ca.id_carrera
        ORDER BY c.id_curso"; // Added ORDER BY for consistent display
$stmt = $conn->prepare($sql);
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener lista de carreras para el formulario
$sql_carreras = "SELECT id_carrera, nombre FROM carreras ORDER BY nombre"; // Order by name for user-friendly dropdown
$stmt = $conn->prepare($sql_carreras);
$stmt->execute();
$carreras = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Cursos</title>
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
        <a class="navbar-brand fw-bold text-primary" href="dashboard_directora.php">
            <i class="fas fa-book-open me-2"></i> Gestión de Cursos
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
    <h1 class="mb-4 text-center">Administración de Cursos</h1>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white pt-4 pb-0">
            <h5 class="card-title text-center text-primary"><?= $id_editar ? 'Editar Curso' : 'Agregar Nuevo Curso' ?></h5>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3 align-items-center justify-content-center">
                <input type="hidden" name="id_curso" value="<?= htmlspecialchars($datos_editar['ID_CURSO'] ?? '') ?>">
                <div class="col-md-3">
                    <select name="id_carrera" class="form-select form-select-lg" required>
                        <option value="">Seleccione carrera</option>
                        <?php foreach ($carreras as $carrera): ?>
                            <option value="<?= htmlspecialchars($carrera['ID_CARRERA']) ?>" <?= isset($datos_editar['ID_CARRERA']) && $datos_editar['ID_CARRERA'] == $carrera['ID_CARRERA'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($carrera['NOMBRE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="nombre" class="form-control form-control-lg" placeholder="Nombre del curso" required value="<?= htmlspecialchars($datos_editar['NOMBRE'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="descripcion" class="form-control form-control-lg" placeholder="Descripción del curso" required value="<?= htmlspecialchars($datos_editar['DESCRIPCION'] ?? '') ?>">
                </div>
                <div class="col-md-2">
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
            <h5 class="card-title text-center text-primary">Listado de Cursos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped mt-3">
                    <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Carrera</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="text-center">Acciones</th>
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
                                <td><?= htmlspecialchars($curso['ID_CURSO']) ?></td>
                                <td><?= htmlspecialchars($curso['NOMBRE_CARRERA']) ?></td>
                                <td><?= htmlspecialchars($curso['NOMBRE']) ?></td>
                                <td><?= htmlspecialchars($curso['DESCRIPCION']) ?></td>
                                <td class="text-center">
                                    <a href="cursos.php?editar=<?= htmlspecialchars($curso['ID_CURSO']) ?>" class="btn btn-sm btn-warning me-2">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="cursos.php?eliminar=<?= htmlspecialchars($curso['ID_CURSO']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este curso? Esta acción no se puede deshacer.')">
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