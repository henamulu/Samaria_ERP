<?php
/**
 * Script para importar/actualizar la base de datos desde samariac_samaria.sql
 * 
 * Uso:
 *   php importar_sql.php           - Pregunta si desea limpiar la BD
 *   php importar_sql.php --clean   - Limpia la BD automáticamente antes de importar
 *   php importar_sql.php -c       - Mismo que --clean
 *   php importar_sql.php --fresh   - Mismo que --clean
 */

require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use Illuminate\Support\Facades\DB;

$sqlFile = __DIR__ . "/samariac_samaria(1).sql";

if (!file_exists($sqlFile)) {
    die("✗ Archivo SQL no encontrado: $sqlFile\n");
}

echo "=== Importando Base de Datos desde SQL ===\n\n";
echo "Archivo: $sqlFile\n";
echo "Tamaño: " . number_format(filesize($sqlFile) / 1024 / 1024, 2) . " MB\n\n";

// Verificar conexión
try {
    DB::connection()->getPdo();
    $dbName = DB::connection()->getDatabaseName();
    echo "✓ Conectado a base de datos: $dbName\n\n";
} catch (Exception $e) {
    die("✗ Error de conexión: " . $e->getMessage() . "\n");
}

// Verificar si se pasó el parámetro --clean o -c
$cleanDatabase = false;
if (isset($argv)) {
    foreach ($argv as $arg) {
        if ($arg === '--clean' || $arg === '-c' || $arg === '--fresh') {
            $cleanDatabase = true;
            break;
        }
    }
}

// Si no se pasó el parámetro, preguntar interactivamente
if (!$cleanDatabase) {
    echo "¿Desea limpiar la base de datos antes de importar? (s/n): ";
    $handle = fopen("php://stdin", "r");
    if ($handle) {
        $line = trim(fgets($handle));
        fclose($handle);
        $cleanDatabase = (strtolower($line) === 's' || strtolower($line) === 'y' || strtolower($line) === 'si' || strtolower($line) === 'yes');
    }
}

if ($cleanDatabase) {
    echo "\n⚠️  LIMPIANDO BASE DE DATOS...\n";
    echo "Esto eliminará TODAS las tablas existentes.\n";
    
    // Si no se pasó --clean, pedir confirmación
    if (!isset($argv) || !in_array('--clean', $argv) && !in_array('-c', $argv) && !in_array('--fresh', $argv)) {
        echo "¿Está seguro? Escriba 'CONFIRMAR' para continuar: ";
        $handle = fopen("php://stdin", "r");
        if ($handle) {
            $confirm = trim(fgets($handle));
            fclose($handle);
            if ($confirm !== 'CONFIRMAR') {
                echo "✗ Operación cancelada.\n";
                exit(0);
            }
        }
    } else {
        echo "Limpiando automáticamente (--clean especificado)...\n";
    }
    
    try {
        $pdo = DB::connection()->getPdo();
        
        // Desactivar verificaciones de claves foráneas
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // Obtener todas las tablas
        $tables = DB::select("SHOW TABLES");
        $tableKey = "Tables_in_$dbName";
        
        $dropped = 0;
        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            try {
                $pdo->exec("DROP TABLE IF EXISTS `$tableName`");
                $dropped++;
                if ($dropped % 10 == 0) {
                    echo "  Eliminadas: $dropped tablas...\n";
                }
            } catch (Exception $e) {
                echo "  ⚠️  No se pudo eliminar tabla $tableName: " . $e->getMessage() . "\n";
            }
        }
        
        // Reactivar verificaciones de claves foráneas
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        echo "✓ Base de datos limpiada: $dropped tablas eliminadas\n\n";
    } catch (Exception $e) {
        die("✗ Error al limpiar base de datos: " . $e->getMessage() . "\n");
    }
} else {
    echo "Continuando sin limpiar la base de datos...\n\n";
}

// Aumentar límite de memoria para archivos grandes
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '0'); // Sin límite de tiempo

echo "Procesando archivo SQL línea por línea...\n";

// Procesar el archivo línea por línea para evitar problemas de memoria
$statements = [];
$current = '';
$inString = false;
$stringChar = null;
$escaped = false;
$inComment = false;
$commentType = null; // 'line' o 'block'
$lineNumber = 0;

$handle = fopen($sqlFile, 'r');
if (!$handle) {
    die("✗ No se pudo abrir el archivo SQL\n");
}

while (($line = fgets($handle)) !== false) {
    $lineNumber++;
    
    // Mostrar progreso cada 10000 líneas
    if ($lineNumber % 10000 == 0) {
        echo "  Procesando línea $lineNumber... (statements encontrados: " . count($statements) . ")\n";
    }
    
    $line = rtrim($line, "\r\n");
    
    // Procesar comentarios de línea
    if (preg_match('/^--/', $line)) {
        continue; // Saltar comentarios de línea
    }
    
    // Procesar comentarios de bloque
    if (preg_match('/\/\*/', $line)) {
        $inComment = true;
        $commentType = 'block';
    }
    
    if ($inComment) {
        if (preg_match('/\*\//', $line)) {
            $inComment = false;
            $commentType = null;
        }
        // Remover la parte del comentario de la línea
        $line = preg_replace('/\/\*.*?\*\//', '', $line);
        if ($inComment) {
            $line = preg_replace('/\/\*.*$/', '', $line);
        }
        if (preg_match('/\*\//', $line)) {
            $line = preg_replace('/^.*?\*\//', '', $line);
        }
    }
    
    // Saltar líneas vacías después de procesar comentarios
    if (empty(trim($line))) {
        continue;
    }
    
    // Saltar comandos específicos de MySQL
    $skipPatterns = [
        '/^SET\s+SQL_MODE/i',
        '/^SET\s+time_zone/i',
        '/^SET\s+@OLD_CHARACTER_SET/i',
        '/^SET\s+NAMES/i',
        '/^USE\s+/i',
        '/^LOCK\s+TABLES/i',
        '/^UNLOCK\s+TABLES/i',
        '/^DELIMITER\s+/i',
        '/^START\s+TRANSACTION/i',
        '/^COMMIT/i',
    ];
    
    $shouldSkipLine = false;
    foreach ($skipPatterns as $pattern) {
        if (preg_match($pattern, $line)) {
            $shouldSkipLine = true;
            break;
        }
    }
    
    if ($shouldSkipLine) {
        continue;
    }
    
    // Procesar caracteres de la línea
    $length = strlen($line);
    for ($i = 0; $i < $length; $i++) {
        $char = $line[$i];
        
        if ($escaped) {
            $current .= $char;
            $escaped = false;
            continue;
        }
        
        if ($char === '\\' && $inString) {
            $escaped = true;
            $current .= $char;
            continue;
        }
        
        if (!$inString && ($char === '"' || $char === "'" || $char === '`')) {
            $inString = true;
            $stringChar = $char;
            $current .= $char;
        } elseif ($inString && $char === $stringChar) {
            $inString = false;
            $stringChar = null;
            $current .= $char;
        } elseif (!$inString && $char === ';') {
            $stmt = trim($current);
            if (!empty($stmt) && strlen($stmt) > 5) {
            // Saltar comandos que no se pueden ejecutar
            $skipCommands = [
                'SET SQL_MODE',
                'SET time_zone',
                'SET @OLD_CHARACTER_SET',
                'SET NAMES',
                'USE ',
                'LOCK TABLES',
                'UNLOCK TABLES',
            ];
            
            $shouldSkip = false;
            foreach ($skipCommands as $skip) {
                if (stripos($stmt, $skip) === 0) {
                    $shouldSkip = true;
                    break;
                }
            }
            
            // También saltar comandos ALTER TABLE que agregan PRIMARY KEY si la tabla ya tiene PK
            // (esto se detectará mejor durante la ejecución, pero podemos intentar detectarlo aquí)
            if (!$shouldSkip && preg_match('/ALTER\s+TABLE.*ADD\s+PRIMARY\s+KEY/i', $stmt)) {
                // No saltar aquí, dejar que se maneje durante la ejecución con el código de error 1068
            }
            
            if (!$shouldSkip) {
                $statements[] = $stmt;
            }
            }
            $current = '';
        } else {
            $current .= $char;
        }
    }
    
    // Agregar salto de línea si no terminó con ;
    if (!empty($current) && substr($current, -1) !== ';') {
        $current .= "\n";
    }
}

fclose($handle);

// Agregar el último statement si no terminó con ;
if (!empty(trim($current))) {
    $stmt = trim($current);
    if (strlen($stmt) > 5) {
        $statements[] = $stmt;
    }
}

echo "Total de statements encontrados: " . count($statements) . "\n";
echo "Total de líneas procesadas: $lineNumber\n\n";
echo "Ejecutando statements...\n";

$success = 0;
$errors = 0;
$skipped = 0;
$errorMessages = [];

$pdo = DB::connection()->getPdo();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Desactivar verificaciones de claves foráneas y modo estricto temporalmente
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    // Desactivar modo estricto para permitir fechas '0000-00-00' y otros valores antiguos
    $pdo->exec("SET sql_mode = ''");
    // O alternativamente, solo desactivar las partes problemáticas:
    // $pdo->exec("SET sql_mode = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
} catch (Exception $e) {
    // Ignorar si no se puede ejecutar
}

foreach ($statements as $i => $stmt) {
    try {
        // Convertir CREATE TABLE a CREATE TABLE IF NOT EXISTS
        if (preg_match('/^CREATE\s+TABLE\s+`?(\w+)`?/i', $stmt, $matches)) {
            $tableName = $matches[1];
            if (stripos($stmt, 'IF NOT EXISTS') === false) {
                $stmt = preg_replace('/^CREATE\s+TABLE\s+/i', 'CREATE TABLE IF NOT EXISTS ', $stmt);
            }
        }
        
        // Convertir INSERT a INSERT IGNORE para evitar errores de duplicados
        if (preg_match('/^INSERT\s+INTO/i', $stmt) && stripos($stmt, 'IGNORE') === false) {
            $stmt = preg_replace('/^INSERT\s+INTO/i', 'INSERT IGNORE INTO', $stmt);
        }
        
        $pdo->exec($stmt);
        $success++;
        
        if ($success % 100 == 0) {
            echo "  Procesados: $success / " . count($statements) . " statements\n";
        }
    } catch (PDOException $e) {
        $errorCode = $e->getCode();
        $errorMsg = $e->getMessage();
        
        // Ignorar errores comunes que no son críticos
        $ignoreErrors = [
            1050, // Table already exists
            1062, // Duplicate entry
            1061, // Duplicate key name
            1054, // Unknown column (puede ser por diferencias de versión)
            1068, // Multiple primary key defined (la tabla ya tiene PK)
            1072, // Key column doesn't exist in table
            1075, // Incorrect table definition; there can be only one auto column
            1091, // Can't DROP; check that column/key exists
            1292, // Invalid datetime format (fechas '0000-00-00' en versiones antiguas)
        ];
        
        if (in_array($errorCode, $ignoreErrors)) {
            $skipped++;
        } else {
            $errors++;
            if (count($errorMessages) < 10) {
                $errorMessages[] = [
                    'statement' => substr($stmt, 0, 100) . '...',
                    'error' => $errorMsg
                ];
            }
        }
    } catch (Exception $e) {
        $errors++;
        if (count($errorMessages) < 10) {
            $errorMessages[] = [
                'statement' => substr($stmt, 0, 100) . '...',
                'error' => $e->getMessage()
            ];
        }
    }
}

// Reactivar verificaciones de claves foráneas y modo estricto
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    // Reactivar modo estricto (opcional, puedes dejarlo desactivado si prefieres)
    // $pdo->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
} catch (Exception $e) {
    // Ignorar si no se puede ejecutar
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
        echo "\n" . ($idx + 1) . ". Statement: " . $err['statement'] . "\n";
        echo "   Error: " . $err['error'] . "\n";
    }
    if ($errors > 10) {
        echo "\n... y " . ($errors - 10) . " errores más\n";
    }
}

echo "\n=== Importación Completada ===\n";

