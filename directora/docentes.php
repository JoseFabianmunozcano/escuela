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
    $stmt->execute();
    $datos_editar = $stmt->fetch(PDO::FETCH_ASSOC);
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
    $stmt->execute();
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
    $stmt->execute();

    header("Location: docentes.php");
    exit;
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $sql = "DELETE FROM docentes WHERE id_docente = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_eliminar);
    $stmt->execute();
    header("Location: docentes.php");
    exit;
}

// Obtener docentes con nombre de usuario
$sql = "SELECT d.id_docente, d.nombre, d.especialidad, u.nombre AS nombre_usuario
        FROM docentes d
        JOIN usuarios u ON d.id_usuario = u.id_usuario";
$stmt = $conn->prepare($sql);
$stmt->execute();
$docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lista de usuarios para seleccionar en el formulario
$sql_usuarios = "SELECT id_usuario, nombre FROM usuarios";
$stmt = $conn->prepare($sql_usuarios);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Docentes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Gestión de Docentes</h2>
    <form method="POST" class="row g-3 mt-3">
        <input type="hidden" name="id_docente" value="<?= $datos_editar['ID_DOCENTE'] ?? '' ?>">
        <div class="col-md-3">
            <input type="text" name="nombre" class="form-control" placeholder="Nombre del docente" required value="<?= $datos_editar['NOMBRE'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="especialidad" class="form-control" placeholder="Especialidad" required value="<?= $datos_editar['ESPECIALIDAD'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <select name="id_usuario" class="form-select" required>
                <option value="">Seleccione usuario</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['ID_USUARIO'] ?>" <?= isset($datos_editar['ID_USUARIO']) && $datos_editar['ID_USUARIO'] == $usuario['ID_USUARIO'] ? 'selected' : '' ?>>
                        <?= $usuario['NOMBRE'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
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
            <th>Especialidad</th>
            <th>Usuario</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($docentes as $docente): ?>
            <tr>
                <td><?= $docente['ID_DOCENTE'] ?></td>
                <td><?= $docente['NOMBRE'] ?></td>
                <td><?= $docente['ESPECIALIDAD'] ?></td>
                <td><?= $docente['NOMBRE_USUARIO'] ?></td>
                <td>
                    <a href="docentes.php?editar=<?= $docente['ID_DOCENTE'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="docentes.php?eliminar=<?= $docente['ID_DOCENTE'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este docente?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>