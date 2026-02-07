<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use Illuminate\Support\Facades\DB;

$sqlFile = __DIR__ . "/samariac_samaria.sql";

if (!file_exists($sqlFile)) {
    die("Archivo SQL no encontrado: $sqlFile\n");
}

echo "=== IMPORTANDO USUARIOS ===\n\n";

// Leer el archivo SQL
$sql = file_get_contents($sqlFile);

// Buscar todas las líneas que contienen INSERT INTO sam_user
$lines = explode("\n", $sql);
$insertLines = [];
$inInsert = false;
$currentInsert = '';

foreach ($lines as $lineNum => $line) {
    // Detectar inicio de INSERT
    if (preg_match('/INSERT\s+INTO\s+`?sam_user`?/i', $line)) {
        $inInsert = true;
        $currentInsert = trim($line);
        continue;
    }
    
    // Si estamos en un INSERT, agregar líneas hasta encontrar el punto y coma
    if ($inInsert) {
        $currentInsert .= ' ' . trim($line);
        
        // Si la línea termina con punto y coma, guardar el INSERT completo
        if (substr(rtrim($line), -1) === ';') {
            $insertLines[] = $currentInsert;
            $inInsert = false;
            $currentInsert = '';
        }
    }
}

echo "Encontrados " . count($insertLines) . " INSERT statements de usuarios\n\n";

if (empty($insertLines)) {
    echo "No se encontraron INSERT statements de usuarios.\n";
    exit(1);
}

// Ejecutar los INSERTs
$pdo = DB::connection()->getPdo();
$success = 0;
$errors = 0;

// Primero, eliminar usuarios existentes (excepto el admin que creamos)
echo "Eliminando usuarios existentes (excepto admin)...\n";
try {
    DB::table('sam_user')->where('user_name', '!=', 'admin')->delete();
    echo "Usuarios eliminados.\n\n";
} catch (Exception $e) {
    echo "Advertencia al eliminar usuarios: " . $e->getMessage() . "\n\n";
}

echo "Ejecutando INSERTs...\n";

foreach ($insertLines as $i => $stmt) {
    try {
        // Limpiar el statement
        $stmt = trim($stmt);
        if (empty($stmt) || strlen($stmt) < 10) {
            continue;
        }
        
        // Asegurar que termine con punto y coma
        if (substr($stmt, -1) !== ';') {
            $stmt .= ';';
        }
        
        // Reemplazar fechas inválidas '0000-00-00' con NULL
        $stmt = preg_replace("/'0000-00-00'/", "NULL", $stmt);
        
        // Reemplazar '0000-00-00' sin comillas con NULL
        $stmt = preg_replace("/0000-00-00/", "NULL", $stmt);
        
        // Solo ejecutar si es realmente un INSERT de sam_user (no sam_user_activity)
        if (preg_match('/INSERT\s+INTO\s+`?sam_user`?\s*\(/i', $stmt) && 
            !preg_match('/sam_user_activity/i', $stmt)) {
            
            // Cambiar INSERT INTO por INSERT IGNORE INTO para evitar errores de duplicados
            $stmt = preg_replace('/INSERT\s+INTO/i', 'INSERT IGNORE INTO', $stmt);
            
            // Reemplazar strings vacíos en campos únicos con valores únicos temporales
            // Esto es para evitar errores de constraint en user_email vacío
            $stmt = preg_replace("/,\s*''\s*,/", ", CONCAT('temp_', UNIX_TIMESTAMP(), '_', @row_number:=@row_number+1),", $stmt);
            
            try {
                $pdo->exec($stmt);
            } catch (Exception $e) {
                // Si aún hay error, intentar sin IGNORE pero con manejo de duplicados
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    // Intentar con REPLACE INTO en lugar de INSERT
                    $stmt = preg_replace('/INSERT\s+IGNORE\s+INTO/i', 'REPLACE INTO', $stmt);
                    try {
                        $pdo->exec($stmt);
                    } catch (Exception $e2) {
                        throw $e2; // Re-lanzar el error original
                    }
                } else {
                    throw $e;
                }
            }
        } else {
            continue; // Saltar si no es un INSERT de sam_user
        }
        $success++;
        
        if ($success % 5 == 0) {
            echo "Procesados: $success / " . count($insertLines) . "\n";
        }
    } catch (Exception $e) {
        $errors++;
        if ($errors <= 10) {
            echo "Error en INSERT " . ($i + 1) . ": " . substr($e->getMessage(), 0, 150) . "\n";
        }
    }
}

echo "\n=== RESULTADO ===\n";
echo "Exitosos: $success\n";
echo "Errores: $errors\n\n";

// Verificar usuarios importados
$count = DB::table('sam_user')->count();
echo "Total de usuarios en la base de datos: $count\n";

$activeUsers = DB::table('sam_user')
    ->where('user_status', 'Active')
    ->where('status', 'Old')
    ->whereNotNull('user_name')
    ->where('user_name', '!=', '')
    ->count();

echo "Usuarios activos (Active + Old): $activeUsers\n";
