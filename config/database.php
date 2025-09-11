<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'neuroeduca_sistema_crm');

function connectDatabase()
{
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("SET SESSION group_concat_max_len = 1000000;");
        $pdo->exec("SET collation_connection = 'utf8mb4_unicode_ci'");

        return $pdo;
    } catch (PDOException $e) {
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }
}

function isDatabaseConnected($pdo)
{
    return $pdo !== null;
}

function closeDatabase(&$pdo)
{
    $pdo = null;
}

try {
    $pdo = connectDatabase();
    if (isDatabaseConnected($pdo)) {
    } else {
        echo "No se pudo conectar a la base de datos.";
    }
    closeDatabase($pdo);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
