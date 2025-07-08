<?php
global $conn;
session_start();
include 'includes/conexion.php'; // Asegúrate que $conn sea PDO

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'];

    if ($action === "login") {
        $correo = $_POST['correo'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT NOMBRE, PASSWORD, ROL FROM USUARIOS WHERE CORREO = :correo AND ESTADO = 1");
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($password, $row['PASSWORD'])) {
            $_SESSION['usuario'] = $row['NOMBRE'];
            $_SESSION['rol'] = $row['ROL'];

            // Redirección según el rol
            switch (strtolower($_SESSION['rol'])) {
                case 'directora':
                    header("Location: directora/dashboard_directora.php");
                    break;
                case 'coordinador':
                    header("Location: coordinador/dashboard_coordinador.php");
                    break;
                case 'cobranza':
                    header("Location: cobranza/dashboard_cobranza.php");
                    break;
                case 'docente':
                    header("Location: docente/dashboard_docente.php");
                    break;
                default:
                    echo "<script>alert('Rol desconocido'); window.location='views/login.php';</script>";
                    exit;
            }
        } else {
            echo "<script>alert('Credenciales inválidas'); window.location='views/login.php';</script>";
            exit;
        }

    } elseif ($action === "registro") {
        $nombre = $_POST['nombre'];
        $correo = $_POST['correo'];
        $passHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $rol = $_POST['rol'];

        // Validar rol permitido
        $roles_permitidos = ['directora', 'coordinador', 'cobranza', 'docente'];
        if (!in_array($rol, $roles_permitidos)) {
            echo "<script>alert('Rol inválido'); window.history.back();</script>";
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO USUARIOS (ID_USUARIO, NOMBRE, CORREO, PASSWORD, ROL, ESTADO)
                                VALUES (USUARIOS_SEQ.NEXTVAL, :nombre, :correo, :pass, :rol, 1)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':pass', $passHash);
        $stmt->bindParam(':rol', $rol);

        if ($stmt->execute()) {
            echo "<script>alert('Registro exitoso'); window.location='views/login.php';</script>";
            exit;
        } else {
            $error = $stmt->errorInfo();
            echo "<script>alert('Error al registrar: " . $error[2] . "'); window.history.back();</script>";
            exit;
        }
    }
}
?>
