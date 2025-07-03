<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to right, #ece9e6, #ffffff); /* Subtle gradient */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .register-card {
            max-width: 550px; /* Slightly wider for more input fields */
            width: 100%;
            border: none;
            border-radius: 1rem;
        }
        .card-header {
            border-bottom: none;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card register-card shadow-lg">
                <div class="card-header bg-success text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i> Registro de Usuario
                    </h3>
                </div>
                <div class="card-body p-4">
                    <form action="../index.php" method="POST">
                        <input type="hidden" name="action" value="registro">

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="nombre" id="nombre" class="form-control form-control-lg" placeholder="Tu nombre completo" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="correo" id="correo" class="form-control form-control-lg" placeholder="tu@ejemplo.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Crea tu contraseña" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="rol" class="form-label">Rol</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                <select name="rol" id="rol" class="form-select form-select-lg" required>
                                    <option value="">Seleccione un rol</option>
                                    <option value="directora">Directora</option>
                                    <option value="coordinador">Coordinador</option>
                                    <option value="cobranza">Cobranza</option>
                                </select>
                            </div>
                        </div>

                        <input type="hidden" name="estado" value="1">

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-user-plus me-2"></i> Registrar
                            </button>
                        </div>
                        <div class="text-center">
                            <a href="login.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> Iniciar sesión
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