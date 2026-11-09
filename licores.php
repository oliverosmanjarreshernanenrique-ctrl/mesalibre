<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once 'config/Database.php';
include_once 'models/Billar.php';
include_once 'models/Licor.php';

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

$licorObj = new Licor($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? 'crear';
    $nombre_licor = $_POST['nombre_licor'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $id_licor = $_POST['id_licor'] ?? null;

    if ($accion === 'crear' && !empty($nombre_licor)) {
        $licorObj->insertar($id_billar, $nombre_licor, $precio, $stock);
    } elseif ($accion === 'actualizar' && !empty($id_licor)) {
        $licorObj->actualizar($id_licor, $nombre_licor, $precio, $stock);
    }

    header("Location: licores.php?id_billar=" . $id_billar);
    exit();
}

if (isset($_GET['eliminar'])) {
    $licorObj->eliminar($_GET['eliminar']);
    header("Location: licores.php?id_billar=" . $id_billar);
    exit();
}

$editLicor = null;
if (isset($_GET['editar'])) {
    $editLicor = $licorObj->obtenerPorId($_GET['editar']);
}

$stmtLicores = $licorObj->obtenerPorBillar($id_billar);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de Licores | <?= htmlspecialchars($billar['nombre_billar']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-cup-straw"></i> MesaLibre - Gestión de Licores</span>
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
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><?= $editLicor ? 'Editar Licor' : 'Registrar Nuevo Licor' ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="accion" value="<?= $editLicor ? 'actualizar' : 'crear' ?>">
                            <?php if ($editLicor): 
                                $idLKey = isset($editLicor['id_licor']) ? $editLicor['id_licor'] : ($editLicor['id'] ?? '');
                            ?>
                                <input type="hidden" name="id_licor" value="<?= $idLKey ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Nombre del Licor</label>
                                <input type="text" class="form-control" name="nombre_licor" value="<?= htmlspecialchars($editLicor['nombre_licor'] ?? ($editLicor['nombre'] ?? '')) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Precio ($)</label>
                                <input type="number" step="0.01" class="form-control" name="precio" value="<?= htmlspecialchars($editLicor['precio'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Stock / Cantidad</label>
                                <input type="number" class="form-control" name="stock" value="<?= htmlspecialchars($editLicor['stock'] ?? ($editLicor['cantidad'] ?? '')) ?>" required>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100"><i class="bi bi-save"></i> <?= $editLicor ? 'Actualizar Licor' : 'Guardar Licor' ?></button>
                            <?php if ($editLicor): ?>
                                <a href="licores.php?id_billar=<?= $id_billar ?>" class="btn btn-secondary w-100 mt-2">Cancelar</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Inventario Actual</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Licor</th>
                                        <th>Precio</th>
                                        <th>Stock</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $hasLicores = false;
                                    while ($l = $stmtLicores->fetch(PDO::FETCH_ASSOC)): 
                                        $hasLicores = true;
                                        $idL = $l['id_licor'] ?? ($l['id'] ?? '');
                                        $nomL = $l['nombre_licor'] ?? ($l['nombre'] ?? '');
                                        $stockL = $l['stock'] ?? ($l['cantidad'] ?? 0);
                                    ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($nomL) ?></strong></td>
                                            <td>$<?= number_format($l['precio'] ?? 0, 2) ?></td>
                                            <td><span class="badge bg-primary"><?= $stockL ?> und</span></td>
                                            <td>
                                                <a href="licores.php?id_billar=<?= $id_billar ?>&editar=<?= $idL ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                                <a href="licores.php?id_billar=<?= $id_billar ?>&eliminar=<?= $idL ?>" onclick="return confirm('¿Eliminar licor?');" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    <?php if (!$hasLicores): ?>
                                        <tr><td colspan="4" class="text-center text-muted py-3">No hay licores registrados.</td></tr>
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