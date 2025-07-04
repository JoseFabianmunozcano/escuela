<?php
global $conn;
include '../includes/auth.php';

// --- INICIO DE TU LÓGICA PHP (sin cambios) ---
if ($_SESSION['rol'] !== 'directora') {
    header("Location: ../views/login.php");
    exit;
}

include '../includes/conexion.php';

$id_editar = null;
$datos_editar = null;

function calcularEdad($fecha_nac) {
    if (empty($fecha_nac)) return 'N/A';
    try {
        $nac = DateTime::createFromFormat('Y-m-d', $fecha_nac);
        if (!$nac) return 'N/A';
        $hoy = new DateTime();
        return $hoy->diff($nac)->y;
    } catch (Exception $e) {
        return 'N/A';
    }
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
    $fecha_nac = !empty($_POST['fecha_nac']) ? $_POST['fecha_nac'] : null;
    $sexo = $_POST['sexo'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $id_carrera = $_POST['id_carrera'];
    $estado = $_POST['estado'];

    $sql = "INSERT INTO alumnos (id_alumno, nombre, fecha_nacimiento, sexo, correo, telefono, direccion, id_carrera, estado, fecha_inscripcion)
            VALUES (alumnos_seq.NEXTVAL, :nombre, TO_DATE(:fecha_nac, 'YYYY-MM-DD'), :sexo, :correo, :telefono, :direccion, :id_carrera, :estado, SYSDATE)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':fecha_nac', $fecha_nac);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':id_carrera', $id_carrera);
    $stmt->bindParam(':estado', $estado);
    $stmt->execute();
    header("Location: alumnos.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_alumno = $_POST['id_alumno'];
    // ... (resto de tu lógica de actualización sin cambios)
    $nombre = $_POST['nombre'];
    $fecha_nac = !empty($_POST['fecha_nac']) ? $_POST['fecha_nac'] : null;
    $sexo = $_POST['sexo'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $id_carrera = $_POST['id_carrera'];
    $estado = $_POST['estado'];

    $sql = "UPDATE alumnos SET nombre = :nombre, fecha_nacimiento = TO_DATE(:fecha_nac, 'YYYY-MM-DD'), sexo = :sexo, correo = :correo, telefono = :telefono,
            direccion = :direccion, id_carrera = :id_carrera, estado = :estado WHERE id_alumno = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':fecha_nac', $fecha_nac);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':id_carrera', $id_carrera);
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

$sql = "SELECT a.*, TO_CHAR(a.fecha_nacimiento, 'DD/MM/YYYY') AS fecha_nacimiento_fmt, c.nombre AS carrera FROM alumnos a
        JOIN carreras c ON a.id_carrera = c.id_carrera ORDER BY a.id_alumno ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql_carreras = "SELECT id_carrera, nombre FROM carreras";
$stmt = $conn->prepare($sql_carreras);
$stmt->execute();
$carreras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Variables para el diseño dinámico
$form_title = $id_editar ? '📝 Editar Alumno' : '🧑‍🎓 Agregar Nuevo Alumno';
$button_text = $id_editar ? 'Actualizar' : 'Agregar';
$button_class = $id_editar ? 'btn-primary' : 'btn-success';
// --- FIN DE LA LÓGICA PHP ---
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Alumnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: rgba(105, 104, 104, 0.3); }
        .card { box-shadow: 0 0.5rem 1rem rgba(0,0,0,.1); border: none; }
        .table-hover tbody tr:hover { background-color: #f1f1f1; }
        .btn-icon { padding: 0.375rem 0.75rem; }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-people-fill"></i> Gestión de Alumnos</h2>
        <a href="dashboard_directora.php" class="btn btn-secondary d-flex align-items-center">
            <i class="bi bi-arrow-left me-2"></i>Volver al Panel
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><?= $form_title ?></h5>
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="id_alumno" value="<?= htmlspecialchars($datos_editar['ID_ALUMNO'] ?? '') ?>">

                <div class="col-md-8">
                    <label class="form-label">Nombre Completo</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej. Ana Sofía Pérez" required value="<?= htmlspecialchars($datos_editar['NOMBRE'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nac" class="form-control" required value="<?= isset($datos_editar['FECHA_NACIMIENTO']) ? date('Y-m-d', strtotime($datos_editar['FECHA_NACIMIENTO'])) : '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sexo</label>
                    <select name="sexo" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        <option value="M" <?= (isset($datos_editar['SEXO']) && $datos_editar['SEXO'] == 'M') ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= (isset($datos_editar['SEXO']) && $datos_editar['SEXO'] == 'F') ? 'selected' : '' ?>>Femenino</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control" placeholder="ejemplo@correo.com" required value="<?= htmlspecialchars($datos_editar['CORREO'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" placeholder="Ej. 5512345678" required value="<?= htmlspecialchars($datos_editar['TELEFONO'] ?? '') ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Dirección</label>
                    <input type="text" name="direccion" class="form-control" placeholder="Calle, Número, Colonia, Municipio" required value="<?= htmlspecialchars($datos_editar['DIRECCION'] ?? '') ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Carrera</label>
                    <select name="id_carrera" class="form-select" required>
                        <option value="">Seleccione carrera</option>
                        <?php foreach ($carreras as $carrera): ?>
                            <option value="<?= $carrera['ID_CARRERA'] ?>" <?= (isset($datos_editar['ID_CARRERA']) && $datos_editar['ID_CARRERA'] == $carrera['ID_CARRERA']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($carrera['NOMBRE']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="">Seleccionar...</option>
                        <option value="Activo" <?= (isset($datos_editar['ESTADO']) && $datos_editar['ESTADO'] == 'Activo') ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= (isset($datos_editar['ESTADO']) && $datos_editar['ESTADO'] == 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>

                <div class="col-12 text-end">
                    <?php if ($id_editar): ?>
                        <a href="alumnos.php" class="btn btn-secondary">Cancelar Edición</a>
                    <?php endif; ?>
                    <button type="submit" name="<?= $id_editar ? 'actualizar' : 'agregar' ?>" class="btn <?= $button_class ?>">
                        <?= $button_text ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Listado de Alumnos Registrados</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark text-center">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Edad</th>
                        <th>Sexo</th>
                        <th>Contacto</th>
                        <th>Dirección</th>
                        <th>Carrera</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($alumnos)): ?>
                        <tr><td colspan="9" class="text-center">No hay alumnos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($alumnos as $a): ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($a['ID_ALUMNO']) ?></td>
                                <td><?= htmlspecialchars($a['NOMBRE']) ?></td>
                                <td class="text-center"><?= calcularEdad($a['FECHA_NACIMIENTO']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($a['SEXO']) ?></td>
                                <td>
                                    <small class="d-block"><?= htmlspecialchars($a['CORREO']) ?></small>
                                    <small class="d-block text-muted"><?= htmlspecialchars($a['TELEFONO']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($a['DIRECCION']) ?></td>
                                <td><?= htmlspecialchars($a['CARRERA']) ?></td>
                                <td class="text-center">
                                    <?php
                                    $estado_class = $a['ESTADO'] == 'Activo' ? 'text-bg-success' : 'text-bg-secondary';
                                    echo "<span class='badge {$estado_class}'>" . htmlspecialchars($a['ESTADO']) . "</span>";
                                    ?>
                                </td>
                                <td class="text-center">
                                    <a href="alumnos.php?editar=<?= $a['ID_ALUMNO'] ?>" class="btn btn-sm btn-warning btn-icon" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="alumnos.php?eliminar=<?= $a['ID_ALUMNO'] ?>" class="btn btn-sm btn-danger btn-icon" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar a este alumno?')">
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