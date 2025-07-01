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

function calcularEdad($fecha_nac) {
    $hoy = new DateTime();
    $nac = new DateTime($fecha_nac);
    return $hoy->diff($nac)->y;
}

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
    $fecha_nac = $_POST['fecha_nac'];
    $sexo = $_POST['sexo'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $id_curso = $_POST['id_curso'];
    $estado = $_POST['estado'];

    $sql = "INSERT INTO alumnos (id_alumno, nombre, fecha_nacimiento, sexo, correo, telefono, direccion, id_curso, estado, fecha_inscripcion)
            VALUES (alumnos_seq.NEXTVAL, :nombre, :fecha_nacimiento, :sexo, :correo, :telefono, :direccion, :id_curso, :estado, SYSDATE)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':fecha_nac', $fecha_nacimineto);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':id_curso', $id_curso);
    $stmt->bindParam(':estado', $estado);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_alumno = $_POST['id_alumno'];
    $nombre = $_POST['nombre'];
    $fecha_nac = $_POST['fecha_nac'];
    $sexo = $_POST['sexo'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $id_curso = $_POST['id_curso'];
    $estado = $_POST['estado'];

    $sql = "UPDATE alumnos SET nombre = :nombre, fecha_nacimiento = :fecha_nacimiento, sexo = :sexo, correo = :correo, telefono = :telefono,
            direccion = :direccion, id_curso = :id_curso, estado = :estado WHERE id_alumno = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':fecha_nac', $fecha_nacimiento);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':id_curso', $id_curso);
    $stmt->bindParam(':estado', $estado);
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

$sql = "SELECT a.*, c.nombre AS curso FROM alumnos a
        JOIN cursos c ON a.id_curso = c.id_curso";
$stmt = $conn->prepare($sql);
$stmt->execute();
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql_cursos = "SELECT id_curso, nombre FROM cursos";
$stmt = $conn->prepare($sql_cursos);
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <div class="col-md-3"><input type="text" name="nombre" class="form-control" placeholder="Nombre" required value="<?= $datos_editar['NOMBRE'] ?? '' ?>"></div>
        <div class="col-md-2"><input type="date" name="fecha_nac" class="form-control" required value="<?= $datos_editar['FECHA_NAC'] ?? '' ?>"></div>
        <div class="col-md-1">
            <select name="sexo" class="form-select" required>
                <option value="">Sexo</option>
                <option value="M" <?= (isset($datos_editar['SEXO']) && $datos_editar['SEXO'] == 'M') ? 'selected' : '' ?>>M</option>
                <option value="F" <?= (isset($datos_editar['SEXO']) && $datos_editar['SEXO'] == 'F') ? 'selected' : '' ?>>F</option>
            </select>
        </div>
        <div class="col-md-2"><input type="email" name="correo" class="form-control" placeholder="Correo" required value="<?= $datos_editar['CORREO'] ?? '' ?>"></div>
        <div class="col-md-2"><input type="text" name="telefono" class="form-control" placeholder="Teléfono" required value="<?= $datos_editar['TELEFONO'] ?? '' ?>"></div>
        <div class="col-md-3"><input type="text" name="direccion" class="form-control" placeholder="Dirección" required value="<?= $datos_editar['DIRECCION'] ?? '' ?>"></div>
        <div class="col-md-3">
            <select name="id_curso" class="form-select" required>
                <option value="">Seleccione curso</option>
                <?php foreach ($cursos as $curso): ?>
                    <option value="<?= $curso['ID_CURSO'] ?>" <?= (isset($datos_editar['ID_CURSO']) && $datos_editar['ID_CURSO'] == $curso['ID_CURSO']) ? 'selected' : '' ?>>
                        <?= $curso['NOMBRE'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="estado" class="form-select" required>
                <option value="">Estado</option>
                <option value="Activo" <?= (isset($datos_editar['ESTADO']) && $datos_editar['ESTADO'] == 'Activo') ? 'selected' : '' ?>>Activo</option>
                <option value="Inactivo" <?= (isset($datos_editar['ESTADO']) && $datos_editar['ESTADO'] == 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" name="<?= $id_editar ? 'actualizar' : 'agregar' ?>" class="btn btn-<?= $id_editar ? 'primary' : 'success' ?> w-100">
                <?= $id_editar ? 'Actualizar' : 'Agregar' ?>
            </button>
        </div>
    </form>

    <table class="table table-bordered table-striped mt-4">
        <thead class="table-dark text-center">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Fecha Nac.</th>
            <th>Edad</th>
            <th>Sexo</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Dirección</th>
            <th>Curso</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($alumnos as $a): ?>
            <tr>
                <td><?= $a['ID_ALUMNO'] ?></td>
                <td><?= $a['NOMBRE'] ?></td>
                <td><?= date('d/m/Y', strtotime($a['FECHA_NAC'])) ?></td>
                <td><?= calcularEdad($a['FECHA_NAC']) ?> años</td>
                <td><?= $a['SEXO'] ?></td>
                <td><?= $a['CORREO'] ?></td>
                <td><?= $a['TELEFONO'] ?></td>
                <td><?= $a['DIRECCION'] ?></td>
                <td><?= $a['CURSO'] ?></td>
                <td><?= $a['ESTADO'] ?></td>
                <td>
                    <a href="alumnos.php?editar=<?= $a['ID_ALUMNO'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="alumnos.php?eliminar=<?= $a['ID_ALUMNO'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este alumno?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
