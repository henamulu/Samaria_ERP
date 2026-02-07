<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICANDO USUARIOS ===\n\n";

$count = DB::table('sam_user')->count();
echo "Total de usuarios en la base de datos: $count\n\n";

if ($count > 0) {
    $users = DB::table('sam_user')
        ->select('id', 'user_name', 'user_email', 'user_status', 'status', 'firstname', 'lastname')
        ->whereNotNull('user_name')
        ->where('user_name', '!=', '')
        ->limit(10)
        ->get();
    
    echo "Primeros usuarios encontrados:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-5s %-20s %-30s %-10s %-10s %s\n", "ID", "Username", "Email", "Status", "Type", "Nombre");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($users as $user) {
        $nombre = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
        printf(
            "%-5s %-20s %-30s %-10s %-10s %s\n",
            $user->id ?? 'NULL',
            $user->user_name ?? 'NULL',
            substr($user->user_email ?? 'NULL', 0, 30),
            $user->user_status ?? 'NULL',
            $user->status ?? 'NULL',
            $nombre ?: 'NULL'
        );
    }
    
    echo "\n";
    
    // Verificar usuarios activos
    $activeUsers = DB::table('sam_user')
        ->where('user_status', 'Active')
        ->where('status', 'Old')
        ->whereNotNull('user_name')
        ->where('user_name', '!=', '')
        ->count();
    
    echo "Usuarios activos (Active + Old): $activeUsers\n";
    
    // Buscar usuario admin
    $admin = DB::table('sam_user')
        ->where('user_name', 'admin')
        ->first();
    
    if ($admin) {
        echo "\nUsuario 'admin' encontrado:\n";
        echo "  ID: " . ($admin->id ?? 'NULL') . "\n";
        echo "  Email: " . ($admin->user_email ?? 'NULL') . "\n";
        echo "  Status: " . ($admin->user_status ?? 'NULL') . "\n";
    } else {
        echo "\nUsuario 'admin' NO encontrado. Ejecuta: php crear_admin.php\n";
    }
} else {
    echo "No hay usuarios en la base de datos.\n";
    echo "Ejecuta: php crear_admin.php para crear un usuario admin\n";
}
