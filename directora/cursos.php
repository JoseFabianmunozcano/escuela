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
    $stmt->execute();
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
    $stmt->execute();

    header("Location: cursos.php");
    exit;
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $sql = "DELETE FROM cursos WHERE id_curso = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_eliminar);
    $stmt->execute();
    header("Location: cursos.php");
    exit;
}

// Obtener cursos con nombre de carrera
$sql = "SELECT c.id_curso, c.nombre, c.descripcion, ca.nombre AS nombre_carrera
        FROM cursos c
        JOIN carreras ca ON c.id_carrera = ca.id_carrera";
$stmt = $conn->prepare($sql);
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener lista de carreras para el formulario
$sql_carreras = "SELECT id_carrera, nombre FROM carreras";
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
</head>
<body>
<div class="container mt-4">
    <h2>Gestión de Cursos</h2>
    <form method="POST" class="row g-3 mt-3">
        <input type="hidden" name="id_curso" value="<?= $datos_editar['ID_CURSO'] ?? '' ?>">
        <div class="col-md-3">
            <select name="id_carrera" class="form-select" required>
                <option value="">Seleccione carrera</option>
                <?php foreach ($carreras as $carrera): ?>
                    <option value="<?= $carrera['ID_CARRERA'] ?>" <?= isset($datos_editar['ID_CARRERA']) && $datos_editar['ID_CARRERA'] == $carrera['ID_CARRERA'] ? 'selected' : '' ?>>
                        <?= $carrera['NOMBRE'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" name="nombre" class="form-control" placeholder="Nombre del curso" required value="<?= $datos_editar['NOMBRE'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="descripcion" class="form-control" placeholder="Descripción" required value="<?= $datos_editar['DESCRIPCION'] ?? '' ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" name="<?= $id_editar ? 'actualizar' : 'agregar' ?>" class="btn btn-<?= $id_editar ? 'primary' : 'success' ?> w-100">
                <?= $id_editar ? 'Actualizar' : 'Agregar' ?>
            </button>
        </div>
    </form>

    <table class="table table-bordered table-striped mt-4">
        <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Carrera</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($cursos as $curso): ?>
            <tr>
                <td><?= $curso['ID_CURSO'] ?></td>
                <td><?= $curso['NOMBRE_CARRERA'] ?></td>
                <td><?= $curso['NOMBRE'] ?></td>
                <td><?= $curso['DESCRIPCION'] ?></td>
                <td>
                    <a href="cursos.php?editar=<?= $curso['ID_CURSO'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="cursos.php?eliminar=<?= $curso['ID_CURSO'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este curso?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
