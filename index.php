<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Validar que el usuario sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include_once 'config/Database.php';
include_once 'models/Billar.php';

$database = new Database();
$db = $database->getConnection();

$billarObj = new Billar($db);

// Procesar eliminación de billar
if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $billarObj->eliminar($id_eliminar);
    header("Location: index.php");
    exit();
}

// Procesar formulario de creación de billar con barrio y dirección
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_billar'])) {
    $nombre_billar = $_POST['nombre_billar'] ?? '';
    $ciudad = $_POST['ciudad'] ?? '';
    $barrio = $_POST['barrio'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    if (!empty($nombre_billar) && !empty($ciudad)) {
        $stmtIns = $db->prepare("INSERT INTO billar_virtual (nombre_billar, ciudad, barrio, direccion, estado_operativo) VALUES (:nombre, :ciudad, :barrio, :direccion, 'Disponible')");
        $stmtIns->execute([
            ':nombre' => $nombre_billar,
            ':ciudad' => $ciudad,
            ':barrio' => $barrio,
            ':direccion' => $direccion
        ]);
        header("Location: index.php");
        exit();
    }
}

// Procesar edición de billar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_billar'])) {
    $id_billar = $_POST['id_billar'] ?? '';
    $nombre_billar = $_POST['nombre_billar'] ?? '';
    $ciudad = $_POST['ciudad'] ?? '';
    $barrio = $_POST['barrio'] ?? '';
    $direccion = $_POST['direccion'] ?? '';

    if (!empty($id_billar) && !empty($nombre_billar)) {
        $stmtUpd = $db->prepare("UPDATE billar_virtual SET nombre_billar = :nombre, ciudad = :ciudad, barrio = :barrio, direccion = :direccion WHERE id_billar = :id");
        $stmtUpd->execute([
            ':nombre' => $nombre_billar,
            ':ciudad' => $ciudad,
            ':barrio' => $barrio,
            ':direccion' => $direccion,
            ':id' => $id_billar
        ]);
        header("Location: index.php");
        exit();
    }
}

$stmtBillares = $billarObj->obtenerTodos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MesaLibre | Panel de Administración - OLV S.A.S</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <!-- Navbar con la marca de la empresa y el admin Hernan oliveros -->
    <nav class="navbar navbar-dark bg-dark shadow-sm px-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-shield-shaded me-2 text-warning"></i> MesaLibre <span class="badge bg-primary fs-6 ms-2">OLV S.A.S</span>
            </span>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white small"><i class="bi bi-person-badge-fill me-1"></i> Admin: <strong>Hernan Oliveros</strong></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h2>Gestión de Establecimientos (Billares)</h2>
                <p class="text-muted m-0">Sistema oficial de administración de <strong>OLV S.A.S</strong> para control de mesas e inventarios.</p>
            </div>
        </div>

        <div class="row">
            <!-- Formulario para Registrar Nuevo Billar -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i> Nuevo Establecimiento</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="crear_billar" value="1">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nombre del Billar</label>
                                <input type="text" class="form-control" name="nombre_billar" placeholder="Ej. Billar El Diamante" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Ciudad</label>
                                <input type="text" class="form-control" name="ciudad" placeholder="Ej. Montería" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Barrio</label>
                                <input type="text" class="form-control" name="barrio" placeholder="Ej. La Granja" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Dirección (Calle)</label>
                                <input type="text" class="form-control" name="direccion" placeholder="Ej. Calle 24 # 15-30" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100 py-2"><i class="bi bi-save me-1"></i> Registrar billar</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Listado de Billares Registrados -->
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-shop me-2"></i> Listado de Billares</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="py-3">Establecimiento</th>
                                        <th class="py-3">Ciudad / Ubicación</th>
                                        <th class="py-3 text-center">Mesas</th>
                                        <th class="py-3 text-center">Licores</th>
                                        <th class="py-3 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $hasBillares = false;
                                    // Reiniciar puntero o consultar nuevamente si es necesario para listar
                                    $stmtBillaresList = $billarObj->obtenerTodos();
                                    while ($billar = $stmtBillaresList->fetch(PDO::FETCH_ASSOC)) {
                                        $hasBillares = true;
                                        $barrio_val = $billar['barrio'] ?? '';
                                        $dir_val = $billar['direccion'] ?? '';
                                        $ubicacion_completa = htmlspecialchars($billar['ciudad']);
                                        if (!empty($barrio_val) || !empty($dir_val)) {
                                            $ubicacion_completa .= " (" . htmlspecialchars($barrio_val) . " - " . htmlspecialchars($dir_val) . ")";
                                        }
                                    ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($billar['nombre_billar']) ?></td>
                                            <td><small class="text-secondary"><?= $ubicacion_completa ?></small></td>
                                            <td class="text-center">
                                                <a href="mesas.php?id_billar=<?= $billar['id_billar'] ?>" class="btn btn-sm btn-outline-primary px-2">
                                                    <i class="bi bi-grid-3x3 me-1"></i> Mesas
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <a href="licores.php?id_billar=<?= $billar['id_billar'] ?>" class="btn btn-sm btn-outline-warning px-2">
                                                    <i class="bi bi-cup-straw me-1"></i> Licores
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <!-- Botón para abrir modal de Editar -->
                                                    <button type="button" class="btn btn-sm btn-outline-secondary px-2" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $billar['id_billar'] ?>">
                                                        <i class="bi bi-pencil-square"></i> Editar
                                                    </button>
                                                    <a href="index.php?eliminar=<?= $billar['id_billar'] ?>" class="btn btn-sm btn-outline-danger px-2" onclick="return confirm('¿Estás seguro de eliminar este establecimiento y todas sus mesas/licores?')">
                                                        <i class="bi bi-trash"></i> Eliminar
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal de Edición para cada Billar -->
                                        <div class="modal fade" id="modalEditar<?= $billar['id_billar'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header bg-dark text-white">
                                                            <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i> Editar Establecimiento</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <input type="hidden" name="editar_billar" value="1">
                                                            <input type="hidden" name="id_billar" value="<?= $billar['id_billar'] ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Nombre del Billar</label>
                                                                <input type="text" class="form-control" name="nombre_billar" value="<?= htmlspecialchars($billar['nombre_billar']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Ciudad</label>
                                                                <input type="text" class="form-control" name="ciudad" value="<?= htmlspecialchars($billar['ciudad']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Barrio</label>
                                                                <input type="text" class="form-control" name="barrio" value="<?= htmlspecialchars($billar['barrio'] ?? '') ?>" placeholder="Ej. La Granja" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Dirección (Calle)</label>
                                                                <input type="text" class="form-control" name="direccion" value="<?= htmlspecialchars($billar['direccion'] ?? '') ?>" placeholder="Ej. Calle 24 # 15-30" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" class="btn btn-primary btn-sm">Guardar Cambios</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?>
                                    <?>
                                    <?>
                                    <?>
                                    <?>
                                    <?>
                                    <?>
                                    <?>
                                    <?>
                                    <?>
                                    <?php 
                                    }
                                    if (!$hasBillares) {
                                    ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No hay establecimientos registrados todavía.</td>
                                        </tr>
                                    <?>
                                    <?>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 text-muted small">
        <p class="mb-0">MesaLibre - Desarrollado y administrado por <strong>Hernan Oliveros</strong> | <strong>OLV S.A.S</strong> &copy; 2026</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>