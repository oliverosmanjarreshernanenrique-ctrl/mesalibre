<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();

$mensaje = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $cedula = $_POST['cedula'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($nombre) && !empty($cedula) && !empty($username) && !empty($password)) {
        // Verificar si el usuario o la cédula ya existen
        $check = $db->prepare("SELECT id_usuario FROM usuarios WHERE username = ? OR cedula = ?");
        $check->execute([$username, $cedula]);
        
        if ($check->rowCount() > 0) {
            $error = "El nombre de usuario o la cédula ya se encuentran registrados.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO usuarios (nombre, cedula, telefono, username, password, rol) VALUES (?, ?, ?, ?, ?, 'cliente')";
            $stmt = $db->prepare($query);
            
            if ($stmt->execute([$nombre, $cedula, $telefono, $username, $passwordHash])) {
                $mensaje = "¡Registro exitoso! Ya puedes iniciar sesión.";
            } else {
                $error = "Ocurrió un error al registrarse.";
            }
        }
    } else {
        $error = "Por favor completa todos los campos obligatorios.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MesaLibre | Registro de Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center py-5" style="min-height: 100vh;">
    <div class="container" style="max-width: 450px;">
        <div class="card shadow border-0">
            <div class="card-header bg-success text-white text-center py-3">
                <h4 class="mb-0"><i class="bi bi-person-plus-fill"></i> Registro de Cliente</h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-success"><?= $mensaje ?> <a href="login.php" class="alert-link">Iniciar sesión</a></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" name="nombre" placeholder="Ej. Carlos Pérez" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cédula de Ciudadanía</label>
                        <input type="text" class="form-control" name="cedula" placeholder="Ej. 1020304050" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono / Celular</label>
                        <input type="text" class="form-control" name="telefono" placeholder="Ej. 3001234567" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" name="username" placeholder="Ej. carlos99" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 mb-3"><i class="bi bi-check-circle"></i> Registrarse</button>
                    <div class="text-center">
                        <a href="login.php" class="text-decoration-none">¿Ya tienes una cuenta? Inicia sesión aquí</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>