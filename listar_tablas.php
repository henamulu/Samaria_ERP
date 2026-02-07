<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

$tables = DB::select("SHOW TABLES");
echo "=== TABLAS EN samariac_samaria ===\n\n";
foreach($tables as $t) {
    $name = array_values((array)$t)[0];
    if (strpos($name, "sam_") === 0) {
        $count = DB::table($name)->count();
        echo "$name: $count registros\n";
    }
}

