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

    $sql = "INSERT INTO carreras (id_carrera, nombre)
            VALUES (carreras_seq.NEXTVAL, :nombre)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_carrera = $_POST['id_carrera'];
    $nombre = $_POST['nombre'];

    $sql = "UPDATE carreras SET nombre = :nombre WHERE id_carrera = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':id', $id_carrera);
    $stmt->execute();

    header("Location: carreras.php");
    exit;
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $sql = "DELETE FROM carreras WHERE id_carrera = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_eliminar);
    $stmt->execute();
    header("Location: carreras.php");
    exit;
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
</head>
<body>
<div class="container mt-4">
    <h2>Gestión de Carreras</h2>
    <form method="POST" class="row g-3 mt-3">
        <input type="hidden" name="id_carrera" value="<?= $datos_editar['ID_CARRERA'] ?? '' ?>">
        <div class="col-md-9">
            <input type="text" name="nombre" class="form-control" placeholder="Nombre de la carrera" required value="<?= $datos_editar['NOMBRE'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" name="<?= $id_editar ? 'actualizar' : 'agregar' ?>" class="btn btn-<?= $id_editar ? 'primary' : 'success' ?> w-100">
                <?= $id_editar ? 'Actualizar' : 'Agregar' ?>
            </button>
        </div>
    </form>

    <table class="table table-bordered table-striped mt-4">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($carreras as $carrera): ?>
            <tr>
                <td><?= $carrera['ID_CARRERA'] ?></td>
                <td><?= $carrera['NOMBRE'] ?></td>
                <td>
                    <a href="carreras.php?editar=<?= $carrera['ID_CARRERA'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="carreras.php?eliminar=<?= $carrera['ID_CARRERA'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta carrera?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
