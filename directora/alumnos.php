<?php
// Asegúrate de que auth.php incluya session_start() o lo llame aquí si no lo hace.
global $conn;
include '../includes/auth.php'; // auth.php debería iniciar la sesión y manejar la autenticación


if ($_SESSION['rol'] !== 'directora') {
    header("Location: ../views/login.php"); // Ruta corregida si es necesario
    exit;
}

include '../includes/conexion.php'; // Aquí se espera que $conn se defina

$id_editar = null;
$datos_editar = null;

// Inicialización de variables para mensajes de éxito y error
$mensaje_exito = '';
$mensaje_error = '';

/**
 * Calcula la edad a partir de una fecha de nacimiento.
 * @param string $fecha_nac_str Fecha de nacimiento en formato string (ej. 'YYYY-MM-DD').
 * @return int|string La edad en años o 'N/A' si hay un error.
 */
function calcularEdad($fecha_nac_str) {
    if (empty($fecha_nac_str)) return 'N/A';
    try {
        $nac = new DateTime($fecha_nac_str);
        $hoy = new DateTime();
        return $hoy->diff($nac)->y;
    } catch (Exception $e) {
        return 'N/A';
    }
}

// Lógica para cargar datos del alumno para edición
if (isset($_GET['editar'])) {
    $id_editar = $_GET['editar'];
    // Nombres de columna en mayúsculas para Oracle si ATTR_CASE es UPPER
    $sql = "SELECT ID_ALUMNO, NOMBRE, TO_CHAR(FECHA_NACIMIENTO, 'YYYY-MM-DD') AS FECHA_NACIMIENTO_ISO, SEXO, CORREO, TELEFONO, DIRECCION, ID_CARRERA, ESTADO FROM ALUMNOS WHERE ID_ALUMNO = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_editar, PDO::PARAM_INT); // Especificar tipo INT para seguridad
    try {
        $stmt->execute();
        $datos_editar = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$datos_editar) {
            $mensaje_error = "Alumno no encontrado para edición.";
            $id_editar = null; // Resetear para que el formulario actúe como "agregar"
        }
    } catch (PDOException $e) {
        $mensaje_error = "Error al cargar datos para edición: " . htmlspecialchars($e->getMessage());
    }
}

// Lógica para agregar un nuevo alumno
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $fecha_nac = !empty($_POST['fecha_nac']) ? $_POST['fecha_nac'] : null;
    $sexo = $_POST['sexo'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $id_carrera = $_POST['id_carrera'];
    $estado = $_POST['estado'];

    // Asegúrate de que ALUMNOS_SEQ sea el nombre correcto de tu secuencia y FECHA_INSCRIPCION el nombre de la columna
    $sql = "INSERT INTO ALUMNOS (ID_ALUMNO, NOMBRE, FECHA_NACIMIENTO, SEXO, CORREO, TELEFONO, DIRECCION, ID_CARRERA, ESTADO, FECHA_INSCRIPCION)
            VALUES (ALUMNOS_SEQ.NEXTVAL, :nombre, TO_DATE(:fecha_nac, 'YYYY-MM-DD'), :sexo, :correo, :telefono, :direccion, :id_carrera, :estado, SYSDATE)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':fecha_nac', $fecha_nac);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':id_carrera', $id_carrera, PDO::PARAM_INT); // Especificar tipo INT
    $stmt->bindParam(':estado', $estado);
    try {
        $stmt->execute();
        // Redirigir para mostrar mensaje de éxito y limpiar el formulario
        header("Location: alumnos.php?msg=added");
        exit;
    } catch (PDOException $e) {
        $mensaje_error = "Error al agregar alumno: " . htmlspecialchars($e->getMessage());
    }
}

// Lógica para actualizar un alumno existente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id_alumno = $_POST['id_alumno'];
    $nombre = $_POST['nombre'];
    $fecha_nac = !empty($_POST['fecha_nac']) ? $_POST['fecha_nac'] : null;
    $sexo = $_POST['sexo'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $id_carrera = $_POST['id_carrera'];
    $estado = $_POST['estado'];

    $sql = "UPDATE ALUMNOS SET NOMBRE = :nombre, FECHA_NACIMIENTO = TO_DATE(:fecha_nac, 'YYYY-MM-DD'), SEXO = :sexo, CORREO = :correo, TELEFONO = :telefono,
            DIRECCION = :direccion, ID_CARRERA = :id_carrera, ESTADO = :estado WHERE ID_ALUMNO = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':fecha_nac', $fecha_nac);
    $stmt->bindParam(':sexo', $sexo);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':id_carrera', $id_carrera, PDO::PARAM_INT);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id', $id_alumno, PDO::PARAM_INT);
    try {
        $stmt->execute();
        // Redirigir para mostrar mensaje de éxito
        header("Location: alumnos.php?msg=updated");
        exit;
    } catch (PDOException $e) {
        $mensaje_error = "Error al actualizar alumno: " . htmlspecialchars($e->getMessage());
    }
}

// Lógica para eliminar un alumno
if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $sql = "DELETE FROM ALUMNOS WHERE ID_ALUMNO = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id_eliminar, PDO::PARAM_INT);
    try {
        $stmt->execute();
        // Redirigir para mostrar mensaje de éxito
        header("Location: alumnos.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $mensaje_error = "Error al eliminar alumno: " . htmlspecialchars($e->getMessage());
    }
}

// Manejo de mensajes de éxito desde la URL (después de redirecciones)
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'updated') {
        $mensaje_exito = "Alumno actualizado con éxito.";
    } elseif ($_GET['msg'] === 'deleted') {
        $mensaje_exito = "Alumno eliminado con éxito.";
    } elseif ($_GET['msg'] === 'added') {
        $mensaje_exito = "Alumno agregado con éxito.";
    }
}

// Consulta para obtener la lista de alumnos
$alumnos = []; // Inicializar para evitar errores si la consulta falla
try {
    // CAMBIO CLAVE: Usar LEFT JOIN para incluir alumnos que puedan no tener una carrera coincidente
    // Asegúrate de que los nombres de las tablas y columnas son correctos en Oracle (ej. ALUMNOS, ID_ALUMNO, FECHA_NACIMIENTO, CARRERAS, ID_CARRERA, NOMBRE)
    $sql = "SELECT A.ID_ALUMNO, A.NOMBRE, TO_CHAR(A.FECHA_NACIMIENTO, 'YYYY-MM-DD') AS FECHA_NACIMIENTO_ISO, A.SEXO, A.CORREO, A.TELEFONO, A.DIRECCION, A.ESTADO, C.NOMBRE AS CARRERA
            FROM ALUMNOS A
            LEFT JOIN CARRERAS C ON A.ID_CARRERA = C.ID_CARRERA
            ORDER BY A.ID_ALUMNO ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Opcional: Para depuración, puedes descomentar estas líneas temporalmente
    // echo "<div class='alert alert-info'>Número de alumnos encontrados: " . count($alumnos) . "</div>";
    // echo "<pre>"; print_r($alumnos); echo "</pre>";

} catch (PDOException $e) {
    // Si la carga de alumnos falla, asegúrate de que el mensaje de error sea visible
    $mensaje_error .= ($mensaje_error ? "<br>" : "") . "Error al cargar listado de alumnos: " . htmlspecialchars($e->getMessage());
    error_log("Error al cargar alumnos en alumnos.php: " . $e->getMessage()); // Registrar en el log de errores
    $alumnos = []; // Asegurar que $alumnos sea un array vacío para evitar problemas en el bucle foreach
}


// Consulta para obtener la lista de carreras (para el dropdown del formulario)
$carreras = []; // Inicializar para evitar errores si la consulta falla
try {
    // Nombres de columna en mayúsculas
    $sql_carreras = "SELECT ID_CARRERA, NOMBRE FROM CARRERAS ORDER BY NOMBRE";
    $stmt = $conn->prepare($sql_carreras);
    $stmt->execute();
    $carreras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si la carga de carreras falla, añadir al mensaje de error existente
    $mensaje_error .= ($mensaje_error ? "<br>" : "") . "Error al cargar lista de carreras: " . htmlspecialchars($e->getMessage());
    error_log("Error al cargar carreras en alumnos.php: " . $e->getMessage()); // Registrar en el log de errores
    $carreras = []; // Asegurar que $carreras sea un array vacío
}

// Variables para el diseño dinámico del formulario
$form_title = $id_editar ? 'Editar Información del Alumno' : 'Registrar Nuevo Alumno';
$button_text = $id_editar ? 'Actualizar Alumno' : 'Registrar Alumno';
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
            min-height: calc(1.5em + 1rem + 2px); /* Adjust height for larger inputs */
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
            <i class="bi bi-people-fill me-2"></i> Gestión de Alumnos
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
    <h1 class="mb-4 text-center text-primary">Administración de Alumnos</h1>

    <?php if ($mensaje_exito): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $mensaje_exito ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($mensaje_error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $mensaje_error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-5">
        <div class="card-header">
            <h5 class="mb-0 text-primary">
                <?php if ($id_editar): ?>
                    <i class="bi bi-pencil-square me-2"></i> Editar Información del Alumno
                <?php else: ?>
                    <i class="bi bi-person-plus me-2"></i> Registrar Nuevo Alumno
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" class="row g-4">
                <input type="hidden" name="id_alumno" value="<?= htmlspecialchars($datos_editar['ID_ALUMNO'] ?? '') ?>">

                <div class="col-md-7">
                    <label for="nombre" class="form-label fw-bold">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" name="nombre" id="nombre" class="form-control form-control-lg" placeholder="Ej. Ana Sofía Pérez López" required value="<?= htmlspecialchars($datos_editar['NOMBRE'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="fecha_nac" class="form-label fw-bold">Fecha de Nacimiento</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-date-fill"></i></span>
                        <input type="text" name="fecha_nac" id="fecha_nac" class="form-control form-control-lg flatpickr" placeholder="YYYY-MM-DD" required value="<?= htmlspecialchars($datos_editar['FECHA_NACIMIENTO_ISO'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <label for="sexo" class="form-label fw-bold">Sexo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-gender-ambiguous"></i></span>
                        <select name="sexo" id="sexo" class="form-select form-select-lg" required>
                            <option value="">Sel...</option>
                            <option value="M" <?= (isset($datos_editar['SEXO']) && $datos_editar['SEXO'] == 'M') ? 'selected' : '' ?>>Masculino</option>
                            <option value="F" <?= (isset($datos_editar['SEXO']) && $datos_editar['SEXO'] == 'F') ? 'selected' : '' ?>>Femenino</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="correo" class="form-label fw-bold">Correo Electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                        <input type="email" name="correo" id="correo" class="form-control form-control-lg" placeholder="ejemplo@correo.com" required value="<?= htmlspecialchars($datos_editar['CORREO'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="telefono" class="form-label fw-bold">Teléfono</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                        <input type="text" name="telefono" id="telefono" class="form-control form-control-lg" placeholder="Ej. 5512345678" required value="<?= htmlspecialchars($datos_editar['TELEFONO'] ?? '') ?>">
                    </div>
                </div>

                <div class="col-12">
                    <label for="direccion" class="form-label fw-bold">Dirección</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                        <input type="text" name="direccion" id="direccion" class="form-control form-control-lg" placeholder="Calle, Número, Colonia, Municipio, Estado" required value="<?= htmlspecialchars($datos_editar['DIRECCION'] ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="id_carrera" class="form-label fw-bold">Carrera</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-journals"></i></span>
                        <select name="id_carrera" id="id_carrera" class="form-select form-select-lg" required>
                            <option value="">Seleccione carrera</option>
                            <?php foreach ($carreras as $carrera): ?>
                                <option value="<?= htmlspecialchars($carrera['ID_CARRERA']) ?>" <?= (isset($datos_editar['ID_CARRERA']) && $datos_editar['ID_CARRERA'] == $carrera['ID_CARRERA']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($carrera['NOMBRE']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="estado" class="form-label fw-bold">Estado del Alumno</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-toggle-on"></i></span>
                        <select name="estado" id="estado" class="form-select form-select-lg" required>
                            <option value="">Seleccionar...</option>
                            <option value="Activo" <?= (isset($datos_editar['ESTADO']) && $datos_editar['ESTADO'] == 'Activo') ? 'selected' : '' ?>>Activo</option>
                            <option value="Inactivo" <?= (isset($datos_editar['ESTADO']) && $datos_editar['ESTADO'] == 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 text-end mt-4">
                    <?php if ($id_editar): ?>
                        <a href="alumnos.php" class="btn btn-outline-secondary btn-lg me-2">
                            <i class="bi bi-x-circle me-2"></i>Cancelar Edición
                        </a>
                    <?php endif; ?>
                    <button type="submit" name="<?= $id_editar ? 'actualizar' : 'agregar' ?>" class="btn <?= $button_class ?> btn-lg">
                        <?php if ($id_editar): ?>
                            <i class="bi bi-arrow-repeat me-2"></i> <?= $button_text ?>
                        <?php else: ?>
                            <i class="bi bi-plus-circle me-2"></i> <?= $button_text ?>
                        <?php endif; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm mt-5">
        <div class="card-header">
            <h5 class="mb-0 text-primary"><i class="bi bi-card-list me-2"></i> Listado de Alumnos Registrados</h5>
        </div>
        <div class="card-body p-4">
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
                        <tr><td colspan="9" class="text-center py-4 text-muted">No hay alumnos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($alumnos as $a): ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($a['ID_ALUMNO']) ?></td>
                                <td><?= htmlspecialchars($a['NOMBRE']) ?></td>
                                <td class="text-center"><?= calcularEdad($a['FECHA_NACIMIENTO_ISO']) ?></td>
                                <td class="text-center">
                                    <?php
                                    if ($a['SEXO'] == 'M') {
                                        echo '<i class="bi bi-gender-male text-primary" title="Masculino"></i> M';
                                    } else if ($a['SEXO'] == 'F') {
                                        echo '<i class="bi bi-gender-female text-danger" title="Femenino"></i> F';
                                    } else {
                                        echo htmlspecialchars($a['SEXO']);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <small class="d-block"><i class="bi bi-envelope-fill me-1 text-muted"></i><?= htmlspecialchars($a['CORREO']) ?></small>
                                    <small class="d-block text-muted"><i class="bi bi-telephone-fill me-1 text-muted"></i><?= htmlspecialchars($a['TELEFONO']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($a['DIRECCION']) ?></td>
                                <td><?= htmlspecialchars($a['CARRERA']) ?></td>
                                <td class="text-center">
                                    <?php
                                    $estado_class = ($a['ESTADO'] == 'Activo') ? 'text-bg-success' : 'text-bg-secondary';
                                    echo "<span class='badge {$estado_class}'>" . htmlspecialchars($a['ESTADO']) . "</span>";
                                    ?>
                                </td>
                                <td class="text-center">
                                    <a href="alumnos.php?editar=<?= htmlspecialchars($a['ID_ALUMNO']) ?>" class="btn btn-sm btn-warning btn-icon" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <a href="alumnos.php?eliminar=<?= htmlspecialchars($a['ID_ALUMNO']) ?>" class="btn btn-sm btn-danger btn-icon" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar a este alumno? Esta acción es irreversible.')">
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script> <script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#fecha_nac", {
            dateFormat: "Y-m-d", // Formato que coincide con el requisito de tu base de datos
            locale: "es",      // Usar localización en español
            altInput: true,    // Mostrar una fecha amigable en el input
            altFormat: "d F, Y", // Ej: "03 Julio, 2025"
            enableTime: false, // Sin selección de hora
            maxDate: "today"   // No permitir fechas futuras
        });
    });
</script>
</body>
</html>