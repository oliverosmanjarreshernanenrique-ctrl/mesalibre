<?php
class Billar {
    private $conn;
    private $table_name = "billar_virtual";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los billares
    public function obtenerTodos() {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Obtener un billar específico por su ID
    public function obtenerPorId($id_billar) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_billar = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_billar]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Insertar un nuevo billar
    public function insertar($nombre_billar, $ciudad, $estado_operativo = 'Disponible') {
        $query = "INSERT INTO " . $this->table_name . " (nombre_billar, ciudad, estado_operativo) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$nombre_billar, $ciudad, $estado_operativo]);
    }

    // Eliminar billar y sus registros relacionados (mesas y licores)
    public function eliminar($id_billar) {
        // Eliminar mesas asociadas al billar
        $stmtMesas = $this->conn->prepare("DELETE FROM mesas WHERE id_billar = ?");
        $stmtMesas->execute([$id_billar]);

        // Eliminar licores asociados al billar
        $stmtLicores = $this->conn->prepare("DELETE FROM inventario_licores WHERE id_billar = ?");
        $stmtLicores->execute([$id_billar]);

        // Eliminar el billar principal
        $query = "DELETE FROM " . $this->table_name . " WHERE id_billar = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id_billar]);
    }
}