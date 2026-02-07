<?php
/**
 * Script para probar diferentes credenciales de MySQL
 * Ejecuta: php probar_credenciales.php
 */

echo "=== Probando Credenciales de MySQL ===\n\n";

$host = '127.0.0.1';
$port = 3306;
$database = 'samariac_samaria';

// Lista de credenciales comunes a probar
$credentials = [
    ['user' => 'root', 'password' => 'root'],
    ['user' => 'root', 'password' => ''],
    ['user' => 'root', 'password' => 'Sorpresa2024'],
    ['user' => 'root', 'password' => 'password'],
    ['user' => 'root', 'password' => '123456'],
];

echo "Probando diferentes combinaciones de credenciales...\n\n";

foreach ($credentials as $cred) {
    $user = $cred['user'];
    $password = $cred['password'];
    $passwordDisplay = $password === '' ? '(vacía)' : $password;
    
    echo "Probando: usuario='$user', contraseña='$passwordDisplay'...\n";
    
    try {
        // Probar conexión básica
        $pdo = new PDO("mysql:host=$host;port=$port", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "  ✓ Conexión exitosa!\n";
        
        // Probar conexión a la base de datos
        try {
            $pdoDb = new PDO("mysql:host=$host;port=$port;dbname=$database", $user, $password);
            $pdoDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "  ✓ Conexión a la base de datos '$database' exitosa!\n\n";
            
            echo "=== CREDENCIALES CORRECTAS ENCONTRADAS ===\n";
            echo "Usuario: $user\n";
            echo "Contraseña: $passwordDisplay\n\n";
            echo "Actualiza tu archivo .env con:\n";
            echo "DB_USERNAME=$user\n";
            echo "DB_PASSWORD=$password\n";
            
            exit(0);
        } catch (PDOException $e) {
            echo "  ⚠ Conexión OK pero la base de datos '$database' no existe o no tienes permisos\n";
            echo "  Error: " . $e->getMessage() . "\n\n";
        }
        
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Access denied') !== false) {
            echo "  ✗ Acceso denegado (credenciales incorrectas)\n\n";
        } else {
            echo "  ✗ Error: " . $e->getMessage() . "\n\n";
        }
    }
}

echo "=== No se encontraron credenciales válidas ===\n\n";
echo "Opciones:\n";
echo "1. Verifica la contraseña de MySQL en tu instalación\n";
echo "2. Si usas XAMPP/WAMP, la contraseña por defecto suele estar vacía\n";
echo "3. Si instalaste MySQL manualmente, usa la contraseña que configuraste\n";
echo "4. Puedes restablecer la contraseña de MySQL si es necesario\n\n";
echo "Para restablecer la contraseña de root en MySQL:\n";
echo "  - Busca en la documentación de MySQL\n";
echo "  - O usa el instalador de MySQL para cambiar la contraseña\n";
