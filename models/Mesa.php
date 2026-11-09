<?php
class Mesa {
    private $conn;
    private $table_name = "mesas";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerPorBillar($id_billar) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_billar = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_billar]);
        return $stmt;
    }

    public function insertar($id_billar, $numero_mesa, $estado) {
        $query = "INSERT INTO " . $this->table_name . " (id_billar, numero_mesa, estado) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id_billar, $numero_mesa, $estado]);
    }

    public function actualizar($id_mesa, $numero_mesa, $estado) {
        $query = "UPDATE " . $this->table_name . " SET numero_mesa = ?, estado = ? WHERE id_mesa = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$numero_mesa, $estado, $id_mesa]);
    }

    public function eliminar($id_mesa) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_mesa = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id_mesa]);
    }
}
?>