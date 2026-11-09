<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $esValido = false;
            // Permitir acceso directo al admin con 'admin123' o verificar hash estándar para los demás
            if ($user['rol'] === 'admin' && $password === 'admin123') {
                $esValido = true;
            } elseif (password_verify($password, $user['password'])) {
                $esValido = true;
            }

            if ($esValido) {
                $_SESSION['id_usuario'] = $user['id_usuario'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['rol'] = $user['rol'];

                if ($user['rol'] === 'admin') {
                    header("Location: index.php");
                } else {
                    header("Location: cliente.php");
                }
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "El usuario no existe.";
        }
    } else {
        $error = "Por favor ingrese su usuario y contraseña.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MesaLibre | Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center py-5" style="min-height: 100vh;">
    <div class="container" style="max-width: 400px;">
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white text-center py-3">
                <h4 class="mb-0"><i class="bi bi-shield-lock-fill"></i> MesaLibre - Login</h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" name="username" placeholder="Ingrese su usuario" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3"><i class="bi bi-box-arrow-in-right"></i> Entrar</button>
                    
                    <div class="text-center">
                        <p class="mb-1 text-muted">¿Eres cliente nuevo?</p>
                        <a href="registro.php" class="btn btn-outline-success btn-sm w-100">Crear una cuenta</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>