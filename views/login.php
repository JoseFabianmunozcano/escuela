<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            /* Using Bootstrap's utility classes for background gradient */
            background: linear-gradient(to right, #ece9e6, #ffffff); /* A subtle gradient */
            min-height: 100vh; /* Ensure body takes full viewport height */
            display: flex; /* Use flexbox for centering content */
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
        }
        .login-card {
            max-width: 500px; /* Limit card width for better readability */
            width: 100%;
            border: none; /* Remove default card border */
            border-radius: 1rem; /* More rounded corners */
        }
        .card-header {
            border-bottom: none; /* Remove border from header */
            border-top-left-radius: 1rem; /* Match card border radius */
            border-top-right-radius: 1rem; /* Match card border radius */
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card login-card shadow-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-lock me-2"></i> Iniciar Sesión
                    </h3>
                </div>
                <div class="card-body p-4">
                    <form action="../index.php" method="POST">
                        <input type="hidden" name="action" value="login">
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" id="correo" name="correo" class="form-control form-control-lg" placeholder="tu@ejemplo.com" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="password" id="password" name="password" class="form-control form-control-lg" placeholder="Ingresa tu contraseña" required>
                            </div>
                        </div>
                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> Entrar
                            </button>
                        </div>
                        <div class="text-center">
                            <a href="registro.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-user-plus me-2"></i> Registrarse
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>