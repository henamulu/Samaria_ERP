<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

echo "=== BASES DE DATOS DISPONIBLES ===\n\n";
$dbs = DB::select("SHOW DATABASES");
foreach($dbs as $db) {
    echo "- " . $db->Database . "\n";
}

echo "\n=== TABLAS sam_* CON DATOS ===\n";
$tables = DB::select("SHOW TABLES");
foreach($tables as $t) {
    $name = array_values((array)$t)[0];
    $count = DB::table($name)->count();
    if ($count > 0) {
        echo "$name: $count registros\n";
    }
}

