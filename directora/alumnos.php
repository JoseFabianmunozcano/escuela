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

    $sql = "SELECT * FROM alumnos WHERE id_alumno = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_editar);
    $stmt->execute();
    $datos_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $id_curso = $_POST['id_curso'];

    $sql = "INSERT INTO alumnos (id_alumno, nombre, fecha_nacimiento, id_curso)
            VALUES (alumnos_seq.NEXTVAL, :nombre, TO_DATE(:fecha_nacimiento, 'YYYY-MM-DD'), :id_curso)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
    $stmt->bindParam(':id_curso', $id_curso);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_alumno = $_POST['id_alumno'];
    $nombre = $_POST['nombre'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $id_curso = $_POST['id_curso'];

    $sql = "UPDATE alumnos SET nombre = :nombre, fecha_nacimiento = TO_DATE(:fecha_nacimiento, 'YYYY-MM-DD'), id_curso = :id_curso
            WHERE id_alumno = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
    $stmt->bindParam(':id_curso', $id_curso);
    $stmt->bindParam(':id', $id_alumno);
    $stmt->execute();

    header("Location: alumnos.php");
    exit;
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];

    $sql = "DELETE FROM alumnos WHERE id_alumno = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_eliminar);
    $stmt->execute();

    header("Location: alumnos.php");
    exit;
}

$sql = "SELECT a.id_alumno, a.nombre, TO_CHAR(a.fecha_nacimiento, 'YYYY-MM-DD') AS fecha_nacimiento, c.nombre AS curso
        FROM alumnos a
        JOIN cursos c ON a.id_curso = c.id_curso";
$stmt = $conn->prepare($sql);
$stmt->execute();
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sqlCursos = "SELECT id_curso, nombre FROM cursos";
$stmtCursos = $conn->prepare($sqlCursos);
$stmtCursos->execute();
$cursos = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Alumnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Gestión de Alumnos</h2>

    <form method="POST" class="row g-3 mt-3">
        <input type="hidden" name="id_alumno" value="<?= $datos_editar['ID_ALUMNO'] ?? '' ?>">

        <div class="col-md-4">
            <input type="text" name="nombre" class="form-control" placeholder="Nombre del alumno" required
                   value="<?= $datos_editar['NOMBRE'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <input type="date" name="fecha_nacimiento" class="form-control" required
                   value="<?= $datos_editar['FECHA_NACIMIENTO'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <select name="id_curso" class="form-select" required>
                <option value="">Seleccione curso</option>
                <?php foreach ($cursos as $curso): ?>
                    <option value="<?= $curso['ID_CURSO'] ?>"
                        <?= (isset($datos_editar['ID_CURSO']) && $datos_editar['ID_CURSO'] == $curso['ID_CURSO']) ? 'selected' : '' ?>>
                        <?= $curso['NOMBRE'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
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
                <th>Nombre</th>
                <th>Fecha Nac.</th>
                <th>Curso</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($alumnos as $al): ?>
            <tr>
                <td><?= $al['ID_ALUMNO'] ?></td>
                <td><?= $al['NOMBRE'] ?></td>
                <td><?= $al['FECHA_NACIMIENTO'] ?></td>
                <td><?= $al['CURSO'] ?></td>
                <td>
                    <a href="alumnos.php?editar=<?= $al['ID_ALUMNO'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="alumnos.php?eliminar=<?= $al['ID_ALUMNO'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Deseas eliminar este alumno?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
