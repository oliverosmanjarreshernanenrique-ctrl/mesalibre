<?php
include_once 'config/Database.php';
include_once 'models/Mesa.php';
include_once 'models/Licor.php';

$database = new Database();
$db = $database->getConnection();

$mesaObj = new Mesa($db);
$stmtMesas = $mesaObj->obtenerMesas();

$licorObj = new Licor($db);
$stmtLicores = $licorObj->obtenerLicores();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración | MesaLibre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Barra lateral de navegación -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse text-white p-3 min-vh-100">
                <h4 class="text-center py-2">MesaLibre</h4>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a href="#" class="nav-link text-white">Dashboard</a></li>
                    <li class="nav-item mb-2"><a href="#" class="nav-link text-white">Billar Virtual</a></li>
                    <li class="nav-item mb-2"><a href="#" class="nav-link text-white active">Mesas</a></li>
                    <li class="nav-item mb-2"><a href="#" class="nav-link text-white">Inventario Licores</a></li>
                    <li class="nav-item mb-2"><a href="#" class="nav-link text-white">Configuración</a></li>
                </ul>
            </nav>

            <!-- Contenido principal del panel -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2>Panel de Control - Gestión de Billar Virtual</h2>
                <hr>
                
                <div class="row">
                    <!-- Tabla de Mesas -->
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-primary text-white">Mesas Registradas</div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th># Mesa</th>
                                            <th>Tipo</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $stmtMesas->fetch(PDO::FETCH_ASSOC)): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['numero_mesa']); ?></td>
                                                <td><?= htmlspecialchars($row['tipo_mesa']); ?></td>
                                                <td><span class="badge bg-success"><?= htmlspecialchars($row['estado']); ?></span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Licores -->
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-secondary text-white">Inventario de Licores</div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Licor</th>
                                            <th>Marca</th>
                                            <th>Stock</th>
                                            <th>Precio</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $stmtLicores->fetch(PDO::FETCH_ASSOC)): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['nombre_licor']); ?></td>
                                                <td><?= htmlspecialchars($row['marca']); ?></td>
                                                <td><?= htmlspecialchars($row['cantidad']); ?></td>
                                                <td>$<?= htmlspecialchars($row['precio_venta']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>