<?php
/**
 * Script para ejecutar comandos SQL y configurar MySQL
 * Ejecuta: php ejecutar_sql.php
 */

$host = 'localhost'; // Intentar primero con localhost
$user = 'root';
$password = 'Sorpresa2024';

echo "=== Configurando MySQL para Samaria ERP ===\n\n";

// Intentar con localhost primero
$pdo = null;
$hosts = ['localhost', '127.0.0.1'];

foreach ($hosts as $tryHost) {
    try {
        echo "Intentando conectar desde: $tryHost...\n";
        $pdo = new PDO("mysql:host=$tryHost", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✓ Conexión exitosa desde $tryHost\n\n";
        $host = $tryHost;
        break;
    } catch (PDOException $e) {
        echo "✗ Error desde $tryHost: " . $e->getMessage() . "\n";
        continue;
    }
}

if (!$pdo) {
    echo "\n✗ No se pudo conectar a MySQL desde ningún host.\n\n";
    echo "Por favor, ejecuta estos comandos SQL manualmente en MySQL:\n";
    echo "========================================\n\n";
    echo "-- 1. Otorgar permisos desde cualquier host\n";
    echo "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'Sorpresa2024' WITH GRANT OPTION;\n\n";
    echo "-- 2. Otorgar permisos desde la IP específica\n";
    echo "CREATE USER IF NOT EXISTS 'root'@'172.20.0.1' IDENTIFIED BY 'Sorpresa2024';\n";
    echo "GRANT ALL PRIVILEGES ON *.* TO 'root'@'172.20.0.1' WITH GRANT OPTION;\n\n";
    echo "-- 3. Aplicar cambios\n";
    echo "FLUSH PRIVILEGES;\n\n";
    echo "-- 4. Crear base de datos\n";
    echo "CREATE DATABASE IF NOT EXISTS samariac_samaria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n\n";
    echo "========================================\n";
    echo "\nPuedes ejecutarlos en:\n";
    echo "- phpMyAdmin (http://localhost/phpmyadmin)\n";
    echo "- MySQL Workbench\n";
    echo "- HeidiSQL\n";
    echo "- Línea de comandos MySQL\n";
    exit(1);
}

try {
    // Ver usuarios actuales
    echo "1. Verificando usuarios existentes...\n";
    $stmt = $pdo->query("SELECT user, host FROM mysql.user WHERE user = 'root'");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($users) > 0) {
        foreach ($users as $userRow) {
            echo "   - Usuario: {$userRow['user']} desde host: {$userRow['host']}\n";
        }
    } else {
        echo "   - No se encontraron usuarios root\n";
    }
    echo "\n";
    
    // Otorgar permisos desde cualquier host
    echo "2. Otorgando permisos al usuario root desde cualquier host ('%')...\n";
    try {
        // Primero intentar crear el usuario si no existe
        $pdo->exec("CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY '$password'");
        $pdo->exec("GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION");
        echo "   ✓ Permisos otorgados desde '%'\n";
    } catch (PDOException $e) {
        // Si falla, intentar actualizar permisos existentes
        try {
            $pdo->exec("GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY '$password' WITH GRANT OPTION");
            echo "   ✓ Permisos actualizados desde '%'\n";
        } catch (PDOException $e2) {
            echo "   ⚠ Advertencia: " . $e2->getMessage() . "\n";
        }
    }
    
    // Otorgar permisos desde la IP específica
    echo "3. Otorgando permisos desde IP 172.20.0.1...\n";
    try {
        $pdo->exec("CREATE USER IF NOT EXISTS 'root'@'172.20.0.1' IDENTIFIED BY '$password'");
        $pdo->exec("GRANT ALL PRIVILEGES ON *.* TO 'root'@'172.20.0.1' WITH GRANT OPTION");
        echo "   ✓ Permisos otorgados desde '172.20.0.1'\n";
    } catch (PDOException $e) {
        echo "   ⚠ Advertencia: " . $e->getMessage() . "\n";
    }
    
    // Aplicar cambios
    echo "\n4. Aplicando cambios...\n";
    $pdo->exec("FLUSH PRIVILEGES");
    echo "   ✓ Privilegios actualizados\n\n";
    
    // Crear base de datos
    echo "5. Creando base de datos 'samariac_samaria'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS samariac_samaria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✓ Base de datos creada o ya existe\n\n";
    
    // Verificar conexión a la base de datos
    echo "6. Verificando conexión a la base de datos...\n";
    $pdoDb = new PDO("mysql:host=$host;dbname=samariac_samaria", $user, $password);
    $pdoDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✓ Conexión a 'samariac_samaria' exitosa\n\n";
    
    echo "=== Configuración completada exitosamente ===\n";
    echo "\nAhora puedes ejecutar:\n";
    echo "  php artisan migrate\n";
    echo "  php artisan db:seed --class=RolePermissionSeeder\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
    echo "Por favor, ejecuta los comandos SQL manualmente (ver arriba)\n";
    exit(1);
}
