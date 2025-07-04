<?php
global $conn;
include '../includes/auth.php';

if ($_SESSION['rol'] !== 'directora') {
    header("Location: ../../views/login.php"); // Asegúrate de que esta ruta sea correcta
    exit;
}

include '../includes/conexion.php'; // Asegúrate de que la ruta a tu conexión PDO sea correcta

$id_editar = null;
$datos_editar = null;
$mensaje_exito = '';
$mensaje_error = '';

try {
    // --- Lógica para Editar (Cargar datos) ---
    if (isset($_GET['editar'])) {
        $id_editar = $_GET['editar'];
        $sql = "SELECT id_inscripcion, id_alumno, id_curso FROM inscripciones WHERE id_inscripcion = :id_inscripcion";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_inscripcion', $id_editar, PDO::PARAM_INT);
        $stmt->execute();
        $datos_editar = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datos_editar) {
            $mensaje_error = "Inscripción no encontrada.";
            $id_editar = null; // Resetear para que el formulario actúe como "agregar"
        }
    }

    // --- Lógica para Agregar ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
        $id_alumno = $_POST['id_alumno'];
        $id_curso = $_POST['id_curso'];

        // Verificar si la inscripción ya existe antes de agregar
        $sql_check = "SELECT COUNT(*) FROM inscripciones WHERE id_alumno = :id_alumno AND id_curso = :id_curso";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
        $stmt_check->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
        $stmt_check->execute();
        if ($stmt_check->fetchColumn() > 0) {
            $mensaje_error = "Error: Este alumno ya está inscrito en este curso.";
        } else {
            // Usa inscripciones_seq.NEXTVAL para Oracle IDENTITY
            $sql = "INSERT INTO inscripciones (id_inscripcion, id_alumno, id_curso, fecha_inscripcion) VALUES (inscripciones_seq.NEXTVAL, :id_alumno, :id_curso, SYSDATE)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
            $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
            $stmt->execute();
            $mensaje_exito = "Inscripción agregada con éxito.";
            // Limpiar datos del formulario después de agregar exitosamente
            $_POST = array(); // Esto evita que los valores persistan en los selects
        }
    }

    // --- Lógica para Actualizar ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
        $id_inscripcion = $_POST['id_inscripcion'];
        $id_alumno = $_POST['id_alumno'];
        $id_curso = $_POST['id_curso'];

        // Verificar si la nueva combinación alumno-curso ya existe en otra inscripción
        $sql_check = "SELECT COUNT(*) FROM inscripciones WHERE id_alumno = :id_alumno AND id_curso = :id_curso AND id_inscripcion != :id_inscripcion";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
        $stmt_check->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
        $stmt_check->bindParam(':id_inscripcion', $id_inscripcion, PDO::PARAM_INT);
        $stmt_check->execute();
        if ($stmt_check->fetchColumn() > 0) {
            $mensaje_error = "Error: Esta combinación de alumno y curso ya existe en otra inscripción.";
        } else {
            $sql = "UPDATE inscripciones SET id_alumno = :id_alumno, id_curso = :id_curso WHERE id_inscripcion = :id_inscripcion";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
            $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
            $stmt->bindParam(':id_inscripcion', $id_inscripcion, PDO::PARAM_INT);
            $stmt->execute();
            $mensaje_exito = "Inscripción actualizada con éxito.";
            $id_editar = null; // Para que el formulario vuelva a modo "agregar"
            // Redireccionar para limpiar la URL y evitar reenvío de formulario
            header("Location: inscripciones.php?msg=updated");
            exit;
        }
    }

    // --- Lógica para Eliminar ---
    if (isset($_GET['eliminar'])) {
        $id_eliminar = $_GET['eliminar'];
        $sql = "DELETE FROM inscripciones WHERE id_inscripcion = :id_eliminar";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_eliminar', $id_eliminar, PDO::PARAM_INT);
        $stmt->execute();
        $mensaje_exito = "Inscripción eliminada con éxito.";
        header("Location: inscripciones.php?msg=deleted"); // Redirigir para limpiar URL
        exit;
    }

    // --- Mensajes de URL (para redirecciones de éxito) ---
    if (isset($_GET['msg'])) {
        if ($_GET['msg'] === 'updated') {
            $mensaje_exito = "Inscripción actualizada con éxito.";
        } elseif ($_GET['msg'] === 'deleted') {
            $mensaje_exito = "Inscripción eliminada con éxito.";
        }
    }


    // --- Obtener datos para los selects (Alumnos y Cursos) ---
    $stmt_alumnos = $conn->prepare("SELECT id_alumno, nombre FROM alumnos ORDER BY nombre");
    $stmt_alumnos->execute();
    $alumnos = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);

    $stmt_cursos = $conn->prepare("SELECT id_curso, nombre, descripcion FROM cursos ORDER BY nombre");
    $stmt_cursos->execute();
    $cursos = $stmt_cursos->fetchAll(PDO::FETCH_ASSOC);

    // --- Obtener todas las inscripciones con nombres de alumno y curso ---
    $sql_inscripciones = "SELECT i.id_inscripcion, a.nombre AS alumno_nombre, c.nombre AS curso_nombre, i.fecha_inscripcion
                          FROM inscripciones i
                          JOIN alumnos a ON i.id_alumno = a.id_alumno
                          JOIN cursos c ON i.id_curso = c.id_curso
                          ORDER BY i.fecha_inscripcion DESC";
    $stmt_inscripciones = $conn->prepare($sql_inscripciones);
    $stmt_inscripciones->execute();
    $inscripciones = $stmt_inscripciones->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Captura cualquier error de la base de datos
    $mensaje_error = "Error en la operación de base de datos: " . $e->getMessage();
    // En un entorno de producción, registrar el error y mostrar un mensaje genérico al usuario
    // error_log("Error en inscripciones.php: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inscripciones - Directora</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
            min-height: calc(1.5em + 1rem + 2px);
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

<nav class="navbar navbar-expand-lg py-3 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard_directora.php">
            <i class="bi bi-mortarboard-fill me-2"></i>
            Sistema Escolar
        </a>
        <div class="d-flex align-items-center">
                <span class="navbar-text me-3 fw-bold">
                   Bienvenida, <span class="text-primary"><?= htmlspecialchars($_SESSION['usuario']); ?></span>
                </span>
            <a href="../views/logout.php" class="btn btn-outline-danger d-flex align-items-center">
                <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
            </a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0 text-primary">Gestión de Inscripciones</h1>
        <a href="dashboard_directora.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver al Dashboard
        </a>
    </div>

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

    <h3 class="mb-3"><?= $id_editar ? 'Editar Inscripción' : 'Registrar Nueva Inscripción' ?></h3>
    <form action="inscripciones.php" method="POST" class="mb-5 p-4 border rounded shadow-sm bg-light">
        <input type="hidden" name="id_inscripcion" value="<?= htmlspecialchars($datos_editar['ID_INSCRIPCION'] ?? '') ?>">

        <div class="row g-3">
            <div class="col-md-6">
                <label for="alumno_id" class="form-label">Alumno</label>
                <select class="form-select" id="alumno_id" name="id_alumno" required>
                    <option value="">-- Seleccione un alumno --</option>
                    <?php foreach ($alumnos as $alumno): ?>
                        <option value="<?= htmlspecialchars($alumno['ID_ALUMNO']); ?>"
                            <?= (isset($datos_editar['ID_ALUMNO']) && $datos_editar['ID_ALUMNO'] == $alumno['ID_ALUMNO']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($alumno['NOMBRE']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="curso_id" class="form-label">Curso</label>
                <select class="form-select" id="curso_id" name="id_curso" required>
                    <option value="">-- Seleccione un curso --</option>
                    <?php foreach ($cursos as $curso): ?>
                        <option value="<?= htmlspecialchars($curso['ID_CURSO']); ?>"
                            <?= (isset($datos_editar['ID_CURSO']) && $datos_editar['ID_CURSO'] == $curso['ID_CURSO']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($curso['NOMBRE']); ?> (<?= htmlspecialchars($curso['DESCRIPCION']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12 text-center">
                <button type="submit" name="<?= $id_editar ? 'actualizar' : 'agregar' ?>" class="btn btn-<?= $id_editar ? 'primary' : 'success' ?> d-flex align-items-center justify-content-center mx-auto">
                    <i class="bi bi-<?= $id_editar ? 'arrow-repeat' : 'plus-circle' ?> me-2"></i>
                    <?= $id_editar ? 'Actualizar Inscripción' : 'Inscribir Alumno' ?>
                </button>
                <?php if ($id_editar): ?>
                    <a href="inscripciones.php" class="btn btn-secondary mt-2">Cancelar Edición</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <h3 class="mb-3 mt-5">Inscripciones Existentes</h3>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Alumno</th>
                <th scope="col">Curso</th>
                <th scope="col">Fecha de Inscripción</th>
                <th scope="col" class="text-center">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (count($inscripciones) > 0): ?>
                <?php foreach ($inscripciones as $inscripcion): ?>
                    <tr>
                        <td><?= htmlspecialchars($inscripcion['ID_INSCRIPCION']); ?></td>
                        <td><?= htmlspecialchars($inscripcion['ALUMNO_NOMBRE']); ?></td>
                        <td><?= htmlspecialchars($inscripcion['CURSO_NOMBRE']); ?></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($inscripcion['FECHA_INSCRIPCION']))); ?></td>
                        <td class="text-center">
                            <a href="inscripciones.php?editar=<?= htmlspecialchars($inscripcion['ID_INSCRIPCION']); ?>" class="btn btn-sm btn-warning me-1" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="inscripciones.php?eliminar=<?= htmlspecialchars($inscripcion['ID_INSCRIPCION']); ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro de que desea eliminar esta inscripción? Esta acción es irreversible.');">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center py-4">No hay inscripciones registradas.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>