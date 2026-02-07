<?php
/**
 * Script para probar la ruta directamente
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Simular una petición HTTP GET a /
$request = Illuminate\Http\Request::create('/', 'GET');

// Forzar que acepte HTML, no JSON
$request->headers->set('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');

try {
    $response = $app->handle($request);
    
    echo "=== Resultado de la Petición ===\n\n";
    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Content-Type: " . $response->headers->get('Content-Type') . "\n\n";
    
    $content = $response->getContent();
    echo "Longitud del contenido: " . strlen($content) . " caracteres\n\n";
    
    if (strpos($content, '<!DOCTYPE') !== false || strpos($content, '<html') !== false) {
        echo "✓ La respuesta es HTML\n";
        echo "Primeros 300 caracteres:\n";
        echo substr($content, 0, 300) . "\n";
    } elseif (strpos($content, '{"detail"') !== false) {
        echo "✗ La respuesta es JSON (error)\n";
        echo "Contenido completo:\n";
        echo $content . "\n";
    } else {
        echo "? Tipo de respuesta desconocido\n";
        echo "Primeros 300 caracteres:\n";
        echo substr($content, 0, 300) . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
