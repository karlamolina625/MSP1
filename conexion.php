<?php
try {
    $conn = new PDO("pgsql:host=localhost;port=5432;dbname=MSP", "postgres", "1234");
    // Configurar PDO para que lance excepciones en errores
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexión exitosa a PostgreSQL"; // solo para debug
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>