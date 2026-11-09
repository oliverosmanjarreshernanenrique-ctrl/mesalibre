<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once 'config/Database.php';
include_once 'models/Billar.php';
include_once 'models/Mesa.php';

$database = new Database();
$db = $database->getConnection();

$id_billar = $_GET['id_billar'] ?? null;

if (!$id_billar) {
    header("Location: index.php");
    exit();
}

$billarObj = new Billar($db);
$billar = $billarObj->obtenerPorId($id_billar);

if (!$billar) {
    header("Location: index.php");
    exit();
}

$mesaObj = new Mesa($db);

// 1. Procesar acciones POST (Crear o Actualizar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $numero_mesa = $_POST['numero_mesa'] ?? '';
    $estado = $_POST['estado'] ?? 'Disponible';
    $id_mesa = $_POST['id_mesa'] ?? null;

    if ($accion === 'crear' && !empty($numero_mesa)) {
        $mesaObj->insertar($id_billar, $numero_mesa, $estado);
    } elseif ($accion === 'actualizar' && !empty($id_mesa)) {
        $mesaObj->actualizar($id_mesa, $numero_mesa, $estado);
        
        // Si el admin cambia la mesa a "Disponible", limpiamos los datos del cliente
        if ($estado === 'Disponible') {
            $stmtClean = $db->prepare("UPDATE mesas SET cliente_nombre = NULL, telefono = NULL, hora_uso = NULL WHERE id_mesa = ?");
            $stmtClean->execute([$id_mesa]);
        }
    }

    header("Location: mesas.php?id_billar=" . $id_billar);
    exit();
}

// 2. Procesar eliminación por GET
if (isset($_GET['eliminar'])) {
    $mesaObj->eliminar($_GET['eliminar']);
    header("Location: mesas.php?id_billar=" . $id_billar);
    exit();
}

$stmtMesas = $mesaObj->obtenerPorBillar($id_billar);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Mesas | <?= htmlspecialchars($billar['nombre_billar']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-grid-3x3"></i> MesaLibre - Administrar Mesas</span>
            <a href="index.php" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Volver al Panel</a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row mb-4">
            <div class="col">
                <h2>Establecimiento: <span class="text-primary"><?= htmlspecialchars($billar['nombre_billar']) ?></span></h2>
                <p class="text-muted mb-0">Ciudad: <?= htmlspecialchars($billar['ciudad']) ?></p>
            </div>
        </div>

        <div class="row">
            <!-- Formulario para Agregar Mesa -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Agregar Nueva Mesa</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="accion" value="crear">
                            <div class="mb-3">
                                <label class="form-label">Número o Nombre de la Mesa</label>
                                <input type="text" class="form-control" name="numero_mesa" placeholder="Ej. Mesa 01" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Estado Inicial</label>
                                <select class="form-select" name="estado">
                                    <option value="Disponible">Disponible</option>
                                    <option value="Ocupada">Ocupada</option>
                                    <option value="Mantenimiento">Mantenimiento</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100"><i class="bi bi-save"></i> Registrar Mesa</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Listado de Mesas del Establecimiento -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-list-nested"></i> Mesas Registradas y Ocupantes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Mesa</th>
                                        <th>Estado Actual</th>
                                        <th>Información del Cliente</th>
                                        <th>Acciones / Modificar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $hasMesas = false;
                                    while ($m = $stmtMesas->fetch(PDO::FETCH_ASSOC)) {
                                        $hasMesas = true;
                                    ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($m['numero_mesa']) ?></strong></td>
                                            <td>
                                                <span class="badge bg-<?= $m['estado'] == 'Disponible' ? 'success' : ($m['estado'] == 'Ocupada' ? 'danger' : 'warning') ?>">
                                                    <?= $m['estado'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($m['estado'] === 'Ocupada' && !empty($m['cliente_nombre'])): ?>
                                                    <small class="d-block"><strong>👤 <?= htmlspecialchars($m['cliente_nombre']) ?></strong></small>
                                                    <small class="d-block text-muted">📞 <?= htmlspecialchars($m['telefono']) ?></small>
                                                    <small class="d-block text-primary">
                                                        🕒 <?= !empty($m['hora_uso']) ? date("h:i A", strtotime($m['hora_uso'])) : '' ?>
                                                    </small>
                                                <?php else: ?>
                                                    <span class="text-muted fst-italic">Sin reserva activa</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" class="d-inline-flex gap-2 align-items-center">
                                                    <input type="hidden" name="accion" value="actualizar">
                                                    <input type="hidden" name="id_mesa" value="<?= $m['id_mesa'] ?>">
                                                    <input type="text" name="numero_mesa" value="<?= htmlspecialchars($m['numero_mesa']) ?>" class="form-control form-control-sm" style="width: 80px;" required>
                                                    <select name="estado" class="form-select form-select-sm" style="width: 115px;">
                                                        <option value="Disponible" <?= $m['estado'] == 'Disponible' ? 'selected' : '' ?>>Disponible</option>
                                                        <option value="Ocupada" <?= $m['estado'] == 'Ocupada' ? 'selected' : '' ?>>Ocupada</option>
                                                        <option value="Mantenimiento" <?= $m['estado'] == 'Mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Actualizar"><i class="bi bi-arrow-repeat"></i></button>
                                                    <a href="mesas.php?id_billar=<?= $id_billar ?>&eliminar=<?= $m['id_mesa'] ?>" onclick="return confirm('¿Seguro que deseas eliminar esta mesa?');" class="btn btn-sm btn-danger" title="Eliminar"><i class="bi bi-trash"></i></a>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php 
                                    } 
                                    ?>
                                    
                                    <?php if (!$hasMesas): ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No hay mesas registradas en este establecimiento todavía.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>