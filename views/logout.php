<?php
session_start();              // Inicia sesión si aún no está iniciada
session_unset();              // Elimina todas las variables de sesión
session_destroy();            // Destruye la sesión actual

// Opcional: eliminar cookies si se usaron
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirige al login
header("Location: login.php");
exit;
?>
