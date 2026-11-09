<?php
class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        // Obtener la URL de conexión que proporciona Render o usar valores locales
        $database_url = getenv('DATABASE_URL');

        try {
            if ($database_url) {
                // Parsear la URL de Render para extraer los datos de PostgreSQL
                $dbopts = parse_url($database_url);
                $host = $dbopts["host"];
                $port = isset($dbopts["port"]) ? $dbopts["port"] : "5432";
                $dbname = ltrim($dbopts["path"], '/');
                $username = $dbopts["user"];
                $password = $dbopts["pass"];

                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
                $this->conn = new PDO($dsn, $username, $password);
            } else {
                // Conexión local por defecto para XAMPP (si pruebas en tu equipo)
                $this->conn = new PDO("pgsql:host=localhost;port=5432;dbname=mesalibre", "postgres", "");
            }

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>