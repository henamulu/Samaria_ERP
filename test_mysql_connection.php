<?php
/**
 * Script para probar la conexión a MySQL
 * Ejecuta: php test_mysql_connection.php
 */

echo "=== Probando Conexión a MySQL ===\n\n";

$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$password = 'root';
$database = 'samariac_samaria';

// Probar conexión básica
echo "1. Probando conexión básica a MySQL...\n";
try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✓ Conexión exitosa a MySQL\n\n";
} catch (PDOException $e) {
    echo "   ✗ Error de conexión: " . $e->getMessage() . "\n\n";
    echo "Posibles causas:\n";
    echo "  - MySQL no está corriendo\n";
    echo "  - MySQL está escuchando en otro puerto\n";
    echo "  - Las credenciales son incorrectas\n";
    echo "  - MySQL está bloqueando conexiones desde esta IP\n\n";
    exit(1);
}

// Probar conexión a la base de datos
echo "2. Probando conexión a la base de datos '$database'...\n";
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✓ Conexión a la base de datos exitosa\n\n";
} catch (PDOException $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
    echo "La base de datos '$database' podría no existir.\n";
    echo "Ejecuta: CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n\n";
    exit(1);
}

// Verificar tablas
echo "3. Verificando tablas en la base de datos...\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   ✓ Encontradas " . count($tables) . " tablas\n";
    if (count($tables) > 0) {
        echo "   Primeras 5 tablas: " . implode(', ', array_slice($tables, 0, 5)) . "\n";
    }
    echo "\n";
} catch (PDOException $e) {
    echo "   ⚠ Error al listar tablas: " . $e->getMessage() . "\n\n";
}

echo "=== Conexión verificada exitosamente ===\n";
echo "\nSi Laravel aún no puede conectarse, verifica:\n";
echo "1. Que el archivo .env tenga las credenciales correctas\n";
echo "2. Que MySQL esté corriendo\n";
echo "3. Que el puerto 3306 esté abierto\n";
