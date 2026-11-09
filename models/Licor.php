<?php
class Licor {
    private $conn;
    private $table_name = "inventario_licores";

    public function __construct($db) {
        $this->conn = $db;
    }

    private function detectarColumnas() {
        $cols = ['id' => 'id_licor', 'nombre' => 'nombre_licor', 'stock' => 'stock'];
        try {
            $stmt = $this->conn->query("DESCRIBE " . $this->table_name);
            $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (in_array('id', $columnas)) $cols['id'] = 'id';
            if (in_array('nombre', $columnas)) $cols['nombre'] = 'nombre';
            if (in_array('cantidad', $columnas)) $cols['stock'] = 'cantidad';
        } catch (Exception $e) {}
        return $cols;
    }

    public function obtenerPorBillar($id_billar) {
        $cols = $this->detectarColumnas();
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_billar = ? ORDER BY " . $cols['id'] . " DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_billar]);
        return $stmt;
    }

    public function insertar($id_billar, $nombre_licor, $precio, $stock) {
        $cols = $this->detectarColumnas();
        $query = "INSERT INTO " . $this->table_name . " (id_billar, {$cols['nombre']}, precio, {$cols['stock']}) VALUES (:id_billar, :nombre, :precio, :stock)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':id_billar' => $id_billar,
            ':nombre' => $nombre_licor,
            ':precio' => $precio,
            ':stock' => $stock
        ]);
    }

    public function obtenerPorId($id_licor) {
        $cols = $this->detectarColumnas();
        $query = "SELECT * FROM " . $this->table_name . " WHERE {$cols['id']} = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_licor]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($id_licor, $nombre_licor, $precio, $stock) {
        $cols = $this->detectarColumnas();
        $query = "UPDATE " . $this->table_name . " SET {$cols['nombre']} = :nombre, precio = :precio, {$cols['stock']} = :stock WHERE {$cols['id']} = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':nombre' => $nombre_licor,
            ':precio' => $precio,
            ':stock' => $stock,
            ':id' => $id_licor
        ]);
    }

    public function eliminar($id_licor) {
        $cols = $this->detectarColumnas();
        $query = "DELETE FROM " . $this->table_name . " WHERE {$cols['id']} = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id_licor]);
    }
}
?>