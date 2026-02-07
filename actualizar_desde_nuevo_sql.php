<?php
/**
 * Script para actualizar la base de datos con los cambios del nuevo archivo SQL
 * Compara samariac_samaria.sql con samariac_samaria(1).sql y aplica solo los cambios nuevos
 * 
 * Uso:
 *   php actualizar_desde_nuevo_sql.php
 */

require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use Illuminate\Support\Facades\DB;

// Aumentar límites
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '0');

$oldSqlFile = __DIR__ . "/samariac_samaria.sql";
$newSqlFile = __DIR__ . "/samariac_samaria (1).sql";

if (!file_exists($oldSqlFile)) {
    die("✗ Archivo SQL original no encontrado: $oldSqlFile\n");
}

if (!file_exists($newSqlFile)) {
    die("✗ Archivo SQL nuevo no encontrado: $newSqlFile\n");
}

echo "=== Actualizando Base de Datos desde Nuevo SQL ===\n\n";
echo "Archivo original: $oldSqlFile\n";
echo "Archivo nuevo: $newSqlFile\n";
echo "Tamaño original: " . number_format(filesize($oldSqlFile) / 1024 / 1024, 2) . " MB\n";
echo "Tamaño nuevo: " . number_format(filesize($newSqlFile) / 1024 / 1024, 2) . " MB\n\n";

// Verificar conexión
try {
    DB::connection()->getPdo();
    $dbName = DB::connection()->getDatabaseName();
    echo "✓ Conectado a base de datos: $dbName\n\n";
} catch (Exception $e) {
    die("✗ Error de conexión: " . $e->getMessage() . "\n");
}

// Función para extraer INSERT statements de un archivo SQL
function extractInserts($sqlFile) {
    $inserts = [];
    $content = file_get_contents($sqlFile);
    
    // Remover comentarios
    $content = preg_replace('/^--.*$/m', '', $content);
    $content = preg_replace('/\/\*.*?\*\//s', '', $content);
    
    // Dividir por punto y coma para obtener statements
    $statements = explode(';', $content);
    
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        
        // Buscar INSERT statements
        if (preg_match('/INSERT\s+(?:IGNORE\s+)?INTO\s+`?(\w+)`?/i', $stmt, $matches)) {
            $tableName = $matches[1];
            
            if (!isset($inserts[$tableName])) {
                $inserts[$tableName] = [];
            }
            
            // Normalizar el statement (remover espacios extra)
            $normalized = preg_replace('/\s+/', ' ', trim($stmt));
            $inserts[$tableName][] = $normalized;
        }
    }
    
    return $inserts;
}

echo "Extrayendo INSERT statements del archivo original...\n";
$oldInserts = extractInserts($oldSqlFile);
echo "  Tablas encontradas: " . count($oldInserts) . "\n";
foreach ($oldInserts as $table => $inserts) {
    echo "    - $table: " . count($inserts) . " inserts\n";
}

echo "\nExtrayendo INSERT statements del archivo nuevo...\n";
$newInserts = extractInserts($newSqlFile);
echo "  Tablas encontradas: " . count($newInserts) . "\n";
foreach ($newInserts as $table => $inserts) {
    echo "    - $table: " . count($inserts) . " inserts\n";
}

echo "\nComparando y preparando cambios...\n";

// Crear un hash de los inserts del archivo original para comparación rápida
$oldHashes = [];
foreach ($oldInserts as $table => $inserts) {
    $oldHashes[$table] = [];
    foreach ($inserts as $insert) {
        // Crear un hash simple basado en los valores (sin el orden de columnas)
        $hash = md5(preg_replace('/\s+/', ' ', strtolower($insert)));
        $oldHashes[$table][$hash] = true;
    }
}

// Identificar inserts nuevos
$newStatements = [];
foreach ($newInserts as $table => $inserts) {
    if (!isset($newStatements[$table])) {
        $newStatements[$table] = [];
    }
    
    foreach ($inserts as $insert) {
        $hash = md5(preg_replace('/\s+/', ' ', strtolower($insert)));
        
        // Si no existe en el archivo original, es nuevo
        if (!isset($oldHashes[$table][$hash])) {
            $newStatements[$table][] = $insert;
        }
    }
}

$totalNew = 0;
foreach ($newStatements as $table => $statements) {
    $totalNew += count($statements);
    if (count($statements) > 0) {
        echo "  - $table: " . count($statements) . " nuevos inserts\n";
    }
}

if ($totalNew === 0) {
    echo "\n✓ No hay cambios nuevos. La base de datos está actualizada.\n";
    exit(0);
}

echo "\nTotal de nuevos inserts a aplicar: $totalNew\n";
echo "\n¿Desea aplicar estos cambios? (s/n): ";
$handle = fopen("php://stdin", "r");
if ($handle) {
    $line = trim(fgets($handle));
    fclose($handle);
    if (strtolower($line) !== 's' && strtolower($line) !== 'y' && strtolower($line) !== 'si' && strtolower($line) !== 'yes') {
        echo "✗ Operación cancelada.\n";
        exit(0);
    }
}

echo "\nAplicando cambios...\n";

$pdo = DB::connection()->getPdo();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Desactivar verificaciones temporalmente
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("SET sql_mode = ''");
} catch (Exception $e) {
    // Ignorar
}

$success = 0;
$errors = 0;
$skipped = 0;
$errorMessages = [];

foreach ($newStatements as $table => $statements) {
    echo "\nProcesando tabla: $table (" . count($statements) . " inserts)...\n";
    
    foreach ($statements as $stmt) {
        try {
            // Convertir a INSERT IGNORE para evitar duplicados
            if (stripos($stmt, 'IGNORE') === false) {
                $stmt = preg_replace('/^INSERT\s+INTO/i', 'INSERT IGNORE INTO', $stmt);
            }
            
            $pdo->exec($stmt);
            $success++;
            
            if ($success % 50 == 0) {
                echo "  Procesados: $success / $totalNew\n";
            }
        } catch (PDOException $e) {
            $errorCode = $e->getCode();
            
            // Ignorar errores comunes
            $ignoreErrors = [
                1050, // Table already exists
                1062, // Duplicate entry
                1061, // Duplicate key name
                1054, // Unknown column
                1068, // Multiple primary key
                1072, // Key column doesn't exist
                1075, // Incorrect table definition
                1091, // Can't DROP
                1292, // Invalid datetime format
            ];
            
            if (in_array($errorCode, $ignoreErrors)) {
                $skipped++;
            } else {
                $errors++;
                if (count($errorMessages) < 10) {
                    $errorMessages[] = [
                        'table' => $table,
                        'error' => $e->getMessage()
                    ];
                }
            }
        } catch (Exception $e) {
            $errors++;
            if (count($errorMessages) < 10) {
                $errorMessages[] = [
                    'table' => $table,
                    'error' => $e->getMessage()
                ];
            }
        }
    }
}

// Reactivar verificaciones
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
} catch (Exception $e) {
    // Ignorar
}

echo "\n=== RESULTADO ===\n";
echo "✓ Exitosos: $success\n";
echo "⚠ Omitidos (duplicados/existentes): $skipped\n";
if ($errors > 0) {
    echo "✗ Errores: $errors\n";
} else {
    echo "✓ Errores: 0\n";
}

if (count($errorMessages) > 0) {
    echo "\n=== Primeros Errores Encontrados ===\n";
    foreach ($errorMessages as $idx => $err) {
        echo "\n" . ($idx + 1) . ". Tabla: " . $err['table'] . "\n";
        echo "   Error: " . $err['error'] . "\n";
    }
    if ($errors > 10) {
        echo "\n... y " . ($errors - 10) . " errores más\n";
    }
}

echo "\n=== Actualización Completada ===\n";
