<?php

try {
    // Conectar sin especificar base de datos
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', 'Sorpresa2024');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Conexión exitosa a MySQL\n";
    
    // Crear base de datos
    $pdo->exec('CREATE DATABASE IF NOT EXISTS samariac_samaria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "✓ Base de datos 'samariac_samaria' creada o ya existe\n";
    
    // Verificar que se puede conectar a la base de datos
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=samariac_samaria', 'root', 'Sorpresa2024');
    echo "✓ Conexión a la base de datos 'samariac_samaria' exitosa\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nPosibles soluciones:\n";
    echo "1. Verifica que MySQL esté corriendo\n";
    echo "2. Verifica que la contraseña sea correcta\n";
    echo "3. Verifica que el usuario 'root' tenga permisos\n";
    exit(1);
}
