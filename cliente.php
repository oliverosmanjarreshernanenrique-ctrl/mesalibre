<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_mesa'])) {
    $id_mesa = $_POST['id_mesa'];
    $cliente_nombre = $_POST['cliente_nombre'] ?? '';
    $cedula = $_POST['cedula'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $hora_uso = $_POST['hora_uso'] ?? '';
    
    if (empty($hora_uso)) {
        $error_msg = "Por favor selecciona una hora válida.";
    } else {
        $query = "UPDATE mesas SET estado = 'Ocupada', cliente_nombre = :cliente_nombre, telefono = :telefono, hora_uso = :hora_uso WHERE id_mesa = :id_mesa";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':cliente_nombre' => $cliente_nombre . " (CC: " . $cedula . ")",
            ':telefono' => $telefono,
            ':hora_uso' => $hora_uso,
            ':id_mesa' => $id_mesa
        ]);
        
        header("Location: cliente.php?reservado=1");
        exit();
    }
}

$stmtBillares = $db->query("SELECT * FROM billar_virtual WHERE estado_operativo = 'Disponible'");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MesaLibre | Zona de Clientes - OLV S.A.S</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; color: #333; }
        .navbar-custom { background-color: #1b263b; }
        .card-billar { border: 1px solid #dee2e6; border-radius: 10px; background-color: #fff; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .card-header-billar { background: linear-gradient(135deg, #1b263b, #415a77); color: #fff; border-top-left-radius: 9px !important; border-top-right-radius: 9px !important; }
        .card-mesa { border: 1px solid #dcdcdc; border-radius: 8px; background: #fff; overflow: hidden; }
        /* Banner superior formal y elegante para la mesa que nunca falla */
        .mesa-banner {
            height: 100px;
            background: linear-gradient(135deg, #0f4c3a, #1b4332);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-bottom: 3px solid #d4af37;
        }
        .table-custom th { background-color: #1b263b; color: #fff; font-size: 0.9rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-custom shadow-sm px-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1 fw-bold"><i class="bi bi-controller me-2 text-info"></i> MesaLibre <span class="badge bg-primary fs-6 ms-2">OLV S.A.S</span></span>
            <div class="d-flex align-items-center gap-3">
                <span class="text-light small"><i class="bi bi-person-circle me-1"></i> Zona de Clientes</span>
                <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <?php if (isset($_GET['reservado'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> ¡Mesa reservada con éxito! Sus datos y cédula se han registrado para el administrador.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error_msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="mb-4">
            <h2 class="text-dark fw-bold"><i class="bi bi-geo-alt-fill text-danger me-2"></i> Billares, Mesas y Licores Disponibles</h2>
            <p class="text-muted">Consulta la ubicación exacta de los establecimientos de **OLV S.A.S**, reserva tu mesa y revisa las bebidas disponibles.</p>
        </div>

        <div class="alert alert-light border shadow-sm py-2 mb-4 text-primary">
            <small><i class="bi bi-info-circle me-1"></i> Horario permitido para reservas: de <strong>9:00 AM</strong> a <strong>3:00 AM</strong>.</small>
        </div>

        <div class="row">
            <div class="col-12">
                <?php 
                $hasBillares = false;
                while ($billar = $stmtBillares->fetch(PDO::FETCH_ASSOC)) {
                    $hasBillares = true;
                    $id_billar = $billar['id_billar'];
                    
                    $ciudad = $billar['ciudad'] ?? 'Montería, Córdoba';
                    $direccion = $billar['direccion'] ?? 'Dirección no especificada';
                    $barrio = $billar['barrio'] ?? 'Zona General';
                ?>
                    <div class="card card-billar shadow-sm mb-4">
                        <div class="card-header card-header-billar d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3 px-4">
                            <div>
                                <h4 class="mb-1 fw-bold"><i class="bi bi-shop me-2 text-warning"></i> <?= htmlspecialchars($billar['nombre_billar']) ?></h4>
                                <span class="text-white-50 small"><i class="bi bi-geo-alt text-warning me-1"></i> <strong>Barrio:</strong> <?= htmlspecialchars($barrio) ?> | <strong>Calle:</strong> <?= htmlspecialchars($direccion) ?></span>
                            </div>
                            <div class="mt-2 mt-md-0">
                                <span class="badge bg-light text-dark px-3 py-2 fw-semibold"><i class="bi bi-pin-map-fill text-danger me-1"></i> <?= htmlspecialchars($ciudad) ?></span>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <div class="row">
                                <!-- Mesas -->
                                <div class="col-lg-7 border-end pe-lg-4 mb-4 mb-lg-0">
                                    <h5 class="text-secondary mb-3 fw-bold"><i class="bi bi-grid-3x3 me-1 text-primary"></i> Mesas Disponibles:</h5>
                                    <div class="row">
                                        <?php
                                        $stmtMesas = $db->prepare("SELECT * FROM mesas WHERE id_billar = ?");
                                        $stmtMesas->execute([$id_billar]);
                                        $mesas = $stmtMesas->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        if (count($mesas) > 0) {
                                            foreach ($mesas as $mesa) {
                                                $disponible = ($mesa['estado'] === 'Disponible');
                                        ?>
                                            <div class="col-md-4 mb-3">
                                                <div class="card card-mesa h-100 shadow-sm text-center">
                                                    <!-- Banner superior formal de mesa que carga instantáneamente sin errores -->
                                                    <div class="mesa-banner">
                                                        <i class="bi bi-circle-fill fs-3 text-warning mb-1"></i>
                                                        <small class="fw-bold text-uppercase tracking-wider" style="font-size: 10px; letter-spacing: 1px;">Mesa Oficial</small>
                                                    </div>
                                                    
                                                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                                                        <div>
                                                            <h6 class="card-title fw-bold text-dark mb-1"><?= htmlspecialchars($mesa['numero_mesa']) ?></h6>
                                                            <p class="card-text mb-3">
                                                                <span class="badge bg-<?= $disponible ? 'success' : 'danger' ?> px-2 py-1">
                                                                    <?= $mesa['estado'] ?>
                                                                </span>
                                                            </p>
                                                        </div>
                                                        <div>
                                                            <?php if ($disponible) { ?>
                                                                <button type="button" class="btn btn-sm btn-outline-success w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalReserva<?= $mesa['id_mesa'] ?>">
                                                                    <i class="bi bi-check2-circle"></i> Elegir Mesa
                                                                </button>
                                                            <?php } else { ?>
                                                                <button class="btn btn-sm btn-secondary w-100" disabled>Ocupada</button>
                                                            <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal de Reserva -->
                                            <?php if ($disponible) { ?>
                                            <div class="modal fade" id="modalReserva<?= $mesa['id_mesa'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content shadow">
                                                        <form method="POST">
                                                            <div class="modal-header bg-dark text-white">
                                                                <h5 class="modal-title"><i class="bi bi-calendar-check text-success me-2"></i> Reservar <?= htmlspecialchars($mesa['numero_mesa']) ?></h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <input type="hidden" name="id_mesa" value="<?= $mesa['id_mesa'] ?>">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold text-secondary small">Nombre Completo</label>
                                                                    <input type="text" class="form-control" name="cliente_nombre" placeholder="Ej. Carlos Pérez" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold text-secondary small">Cédula de Ciudadanía</label>
                                                                    <input type="text" class="form-control" name="cedula" placeholder="Ej. 1020304050" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold text-secondary small">Teléfono / Contacto</label>
                                                                    <input type="text" class="form-control" name="telefono" placeholder="Ej. 3001234567" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-semibold text-secondary small">Hora en que se utilizará</label>
                                                                    <select class="form-select" name="hora_uso" required>
                                                                        <option value="">Seleccione una hora permitida...</option>
                                                                        <optgroup label="Mañana y Tarde (AM / PM)">
                                                                            <option value="09:00 AM">09:00 AM</option>
                                                                            <option value="10:00 AM">10:00 AM</option>
                                                                            <option value="11:00 AM">11:00 AM</option>
                                                                            <option value="12:00 PM">12:00 PM (Mediodía)</option>
                                                                            <option value="01:00 PM">01:00 PM</option>
                                                                            <option value="02:00 PM">02:00 PM</option>
                                                                            <option value="03:00 PM">03:00 PM</option>
                                                                            <option value="04:00 PM">04:00 PM</option>
                                                                            <option value="05:00 PM">05:00 PM</option>
                                                                            <option value="06:00 PM">06:00 PM</option>
                                                                            <option value="07:00 PM">07:00 PM</option>
                                                                            <option value="08:00 PM">08:00 PM</option>
                                                                            <option value="09:00 PM">09:00 PM</option>
                                                                            <option value="10:00 PM">10:00 PM</option>
                                                                            <option value="11:00 PM">11:00 PM</option>
                                                                        </optgroup>
                                                                        <optgroup label="Madrugada (Permitido hasta las 3:00 AM)">
                                                                            <option value="12:00 AM">12:00 AM (Medianoche)</option>
                                                                            <option value="01:00 AM">01:00 AM</option>
                                                                            <option value="02:00 AM">02:00 AM</option>
                                                                            <option value="03:00 AM">03:00 AM (Cierre)</option>
                                                                        </optgroup>
                                                                    </select>
                                                                    <small class="text-muted d-mt-1">Atención: No se permite agendar entre las 4:00 AM y las 8:00 AM.</small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-success btn-sm">Confirmar Reserva</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php } ?>

                                        <?php 
                                            }
                                        } else {
                                        ?>
                                            <p class="text-muted fst-italic">No hay mesas registradas en este billar todavía.</p>
                                        <?php } ?>
                                    </div>
                                </div>

                                <!-- Licores -->
                                <div class="col-lg-5 ps-lg-4">
                                    <h5 class="text-secondary mb-3 fw-bold"><i class="bi bi-cup-straw me-1 text-warning"></i> Licores del Establecimiento:</h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered align-middle rounded overflow-hidden">
                                            <thead class="table-custom">
                                                <tr>
                                                    <th class="py-2">Bebida</th>
                                                    <th class="py-2">Precio</th>
                                                    <th class="py-2 text-center">Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $stmtL = $db->prepare("SELECT * FROM inventario_licores WHERE id_billar = ?");
                                                $stmtL->execute([$id_billar]);
                                                $licores = $stmtL->fetchAll(PDO::FETCH_ASSOC);

                                                if (count($licores) > 0) {
                                                    foreach ($licores as $licor) {
                                                        $nomL = $licor['nombre_licor'] ?? ($licor['nombre'] ?? '');
                                                        $stockL = $licor['stock'] ?? ($licor['cantidad'] ?? 0);
                                                ?>
                                                    <tr>
                                                        <td><strong><?= htmlspecialchars($nomL) ?></strong></td>
                                                        <td class="text-success fw-semibold">$<?= number_format($licor['precio'] ?? 0, 2) ?></td>
                                                        <td class="text-center"><span class="badge bg-<?= $stockL > 0 ? 'primary' : 'danger' ?>"><?= $stockL ?> und</span></td>
                                                    </tr>
                                                <?php 
                                                    }
                                                } else {
                                                ?>
                                                    <tr><td colspan="3" class="text-center text-muted fst-italic py-3">No hay licores registrados.</td></tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 text-muted small border-top mt-5 bg-white">
        <p class="mb-0">MesaLibre - Zona de Clientes | <strong>OLV S.A.S</strong> &copy; 2026</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>