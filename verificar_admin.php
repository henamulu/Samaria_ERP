<?php
/**
 * Script para verificar y crear el usuario admin si no existe
 * Ejecuta: php verificar_admin.php
 */

require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== Verificando Usuario Admin ===\n\n";

try {
    // Verificar conexión
    DB::connection()->getPdo();
    $dbName = DB::connection()->getDatabaseName();
    echo "✓ Conectado a base de datos: $dbName\n\n";
} catch (Exception $e) {
    die("✗ Error de conexión: " . $e->getMessage() . "\n");
}

// Verificar si la tabla sessions existe, si no, crearla
try {
    $sessionsExists = DB::select("SHOW TABLES LIKE 'sessions'");
    if (empty($sessionsExists)) {
        echo "Creando tabla sessions...\n";
        DB::statement("
            CREATE TABLE IF NOT EXISTS sessions (
                id varchar(255) NOT NULL PRIMARY KEY,
                user_id bigint unsigned NULL,
                ip_address varchar(45) NULL,
                user_agent text NULL,
                payload longtext NOT NULL,
                last_activity int NOT NULL,
                INDEX sessions_user_id_index (user_id),
                INDEX sessions_last_activity_index (last_activity)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✓ Tabla sessions creada\n\n";
    } else {
        echo "✓ Tabla sessions ya existe\n\n";
    }
} catch (Exception $e) {
    echo "⚠️  Advertencia al verificar/crear tabla sessions: " . $e->getMessage() . "\n\n";
}

// Verificar si existe el usuario admin
$admin = DB::table('sam_user')
    ->where('user_name', 'admin')
    ->first();

if ($admin) {
    echo "✓ Usuario 'admin' ya existe\n";
    echo "  ID: {$admin->id}\n";
    echo "  Email: {$admin->user_email}\n";
    echo "  Estado: {$admin->user_status}\n";
    echo "  Rol: {$admin->role}\n\n";
    
    // Verificar si la contraseña es admin123
    if (Hash::check('admin123', $admin->user_password)) {
        echo "✓ La contraseña ya está configurada como 'admin123'\n";
    } else {
        echo "⚠️  La contraseña NO es 'admin123'\n";
        echo "¿Desea actualizar la contraseña a 'admin123'? (s/n): ";
        $handle = fopen("php://stdin", "r");
        if ($handle) {
            $line = trim(fgets($handle));
            fclose($handle);
            if (strtolower($line) === 's' || strtolower($line) === 'y' || strtolower($line) === 'si' || strtolower($line) === 'yes') {
                $updateData = ['user_password' => Hash::make('admin123')];
                
                // Solo agregar updated_at si existe
                $columns = DB::select("SHOW COLUMNS FROM sam_user");
                $columnInfo = [];
                foreach ($columns as $col) {
                    $columnInfo[$col->Field] = true;
                }
                if (isset($columnInfo['updated_at'])) {
                    $updateData['updated_at'] = now();
                }
                
                DB::table('sam_user')
                    ->where('id', $admin->id)
                    ->update($updateData);
                echo "✓ Contraseña actualizada a 'admin123'\n";
            }
        }
    }
} else {
    echo "✗ Usuario 'admin' NO existe\n";
    echo "Creando usuario admin...\n";
    
    try {
        // Verificar qué columnas tiene la tabla y cuáles son NOT NULL
        $columns = DB::select("SHOW COLUMNS FROM sam_user");
        $columnInfo = [];
        foreach ($columns as $col) {
            $columnInfo[$col->Field] = [
                'null' => $col->Null === 'YES',
                'default' => $col->Default,
                'type' => $col->Type
            ];
        }
        
        $userData = [
            'user_name' => 'admin',
            'user_email' => 'admin@samaria.com',
            'user_password' => Hash::make('admin123'),
            'user_status' => 'Active',
            'role' => 'Admin',
            'status' => 'Active',
            'firstname' => 'Admin',
            'lastname' => 'User',
            'registered_by' => 'System',
            'date_registered' => now()->format('Y-m-d'),
        ];
        
        // Agregar campos NOT NULL que no tienen default
        foreach ($columnInfo as $field => $info) {
            if (!$info['null'] && $info['default'] === null && !isset($userData[$field])) {
                // Si es NOT NULL y no tiene default, proporcionar un valor
                if (in_array($field, ['department', 'branch', 'type', 'middlename', 'company_name', 'phone_no'])) {
                    $userData[$field] = ''; // String vacío para campos de texto
                } elseif (strpos($info['type'], 'int') !== false) {
                    $userData[$field] = 0; // 0 para campos numéricos
                } else {
                    $userData[$field] = ''; // String vacío por defecto
                }
            }
        }
        
        // Solo agregar created_at y updated_at si existen
        if (isset($columnInfo['created_at'])) {
            $userData['created_at'] = now();
        }
        if (isset($columnInfo['updated_at'])) {
            $userData['updated_at'] = now();
        }
        
        $userId = DB::table('sam_user')->insertGetId($userData);
        
        echo "✓ Usuario admin creado exitosamente\n";
        echo "  ID: $userId\n";
        echo "  Usuario: admin\n";
        echo "  Contraseña: admin123\n";
        echo "  Email: admin@samaria.com\n";
        echo "  Rol: Admin\n\n";
        
        // Si se usa Spatie roles, asignar el rol
        try {
            $user = \App\Models\User::find($userId);
            if ($user && method_exists($user, 'assignRole')) {
                $user->assignRole('Admin');
                echo "✓ Rol 'Admin' asignado (Spatie)\n";
            }
        } catch (Exception $e) {
            // Ignorar si Spatie no está configurado
        }
        
    } catch (Exception $e) {
        die("✗ Error al crear usuario admin: " . $e->getMessage() . "\n");
    }
}

echo "\n=== Verificación Completada ===\n";
