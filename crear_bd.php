<?php
/**
 * Script para crear la base de datos
 * Ejecuta: php crear_bd.php
 */

$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$password = 'Sorpresa2024';
$database = 'samariac_samaria';

echo "=== Creando Base de Datos ===\n\n";

try {
    // Conectar sin especificar base de datos
    echo "1. Conectando a MySQL...\n";
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✓ Conexión exitosa\n\n";
    
    // Crear base de datos
    echo "2. Creando base de datos '$database'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✓ Base de datos creada o ya existe\n\n";
    
    // Verificar conexión a la base de datos
    echo "3. Verificando conexión a la base de datos...\n";
    $pdoDb = new PDO("mysql:host=$host;port=$port;dbname=$database", $user, $password);
    $pdoDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✓ Conexión a '$database' exitosa\n\n";
    
    // Listar bases de datos para confirmar
    echo "4. Verificando que la base de datos existe...\n";
    $stmt = $pdo->query("SHOW DATABASES LIKE '$database'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        echo "   ✓ Base de datos '$database' confirmada\n\n";
    }
    
    echo "=== Base de datos creada exitosamente ===\n";
    echo "\nAhora puedes ejecutar:\n";
    echo "  php artisan migrate\n";
    echo "  php artisan db:seed --class=RolePermissionSeeder\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
