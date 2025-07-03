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
    $sql = "SELECT * FROM carreras WHERE id_carrera = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_editar);
    $stmt->execute();
    $datos_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];

    // In Oracle, you usually use a sequence for auto-incrementing IDs
    $sql = "INSERT INTO carreras (id_carrera, nombre)
            VALUES (carreras_seq.NEXTVAL, :nombre)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    try {
        $stmt->execute();
        // Redirect to prevent re-submission on refresh
        header("Location: carreras.php");
        exit;
    } catch (PDOException $e) {
        // Handle error, e.g., show a message to the user
        echo "<div class='alert alert-danger'>Error al agregar carrera: " . $e->getMessage() . "</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_carrera = $_POST['id_carrera'];
    $nombre = $_POST['nombre'];

    $sql = "UPDATE carreras SET nombre = :nombre WHERE id_carrera = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':id', $id_carrera);
    try {
        $stmt->execute();
        header("Location: carreras.php");
        exit;
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al actualizar carrera: " . $e->getMessage() . "</div>";
    }
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $sql = "DELETE FROM carreras WHERE id_carrera = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_eliminar);
    try {
        $stmt->execute();
        header("Location: carreras.php");
        exit;
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al eliminar carrera: " . $e->getMessage() . "</div>";
    }
}

$sql = "SELECT * FROM carreras ORDER BY id_carrera";
$stmt = $conn->prepare($sql);
$stmt->execute();
$carreras = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Carreras</title>
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
            <i class="fas fa-graduation-cap me-2"></i> Gestión de Carreras
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
    <h1 class="mb-4 text-center">Administración de Carreras</h1>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white pt-4 pb-0">
            <h5 class="card-title text-center text-primary"><?= $id_editar ? 'Editar Carrera' : 'Agregar Nueva Carrera' ?></h5>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3 justify-content-center">
                <input type="hidden" name="id_carrera" value="<?= $datos_editar['ID_CARRERA'] ?? '' ?>">
                <div class="col-md-7">
                    <input type="text" name="nombre" class="form-control form-control-lg" placeholder="Nombre de la carrera" required value="<?= $datos_editar['NOMBRE'] ?? '' ?>">
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
            <h5 class="card-title text-center text-primary">Listado de Carreras</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped mt-3">
                    <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th class="text-center">Acciones</th>
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
                                <td><?= htmlspecialchars($carrera['ID_CARRERA']) ?></td>
                                <td><?= htmlspecialchars($carrera['NOMBRE']) ?></td>
                                <td class="text-center">
                                    <a href="carreras.php?editar=<?= htmlspecialchars($carrera['ID_CARRERA']) ?>" class="btn btn-sm btn-warning me-2">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="carreras.php?eliminar=<?= htmlspecialchars($carrera['ID_CARRERA']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar esta carrera? Esta acción no se puede deshacer.')">
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