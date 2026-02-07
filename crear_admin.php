<?php
require __DIR__ . "/vendor/autoload.php";

$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    $user = User::where("user_name", "admin")->first();
    
    if ($user) {
        echo "Usuario admin ya existe\n";
        $user->user_password = Hash::make("admin123");
        $user->save();
        echo "Password actualizado\n";
    } else {
        $user = User::create([
            "user_name" => "admin",
            "user_email" => "admin@samaria.com",
            "user_password" => Hash::make("admin123"),
            "firstname" => "Administrador",
            "lastname" => "Sistema",
            "user_status" => "Active",
            "status" => "Old",
            "date_registered" => date("Y-m-d"),
        ]);
        echo "Usuario creado\n";
    }
    
    if (!$user->hasRole("Admin")) {
        $user->assignRole("Admin");
        echo "Rol Admin asignado\n";
    }
    
    echo "\n=== CREDENCIALES ===\n";
    echo "Usuario: admin\n";
    echo "Password: admin123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

