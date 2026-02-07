# Script para actualizar la contraseña en .env
$envPath = Join-Path $PSScriptRoot ".env"

if (Test-Path $envPath) {
    $content = Get-Content $envPath
    $newContent = $content | ForEach-Object {
        if ($_ -match '^DB_PASSWORD=') {
            'DB_PASSWORD=Sorpresa2024'
        } else {
            $_
        }
    }
    Set-Content -Path $envPath -Value $newContent
    Write-Host "✓ Archivo .env actualizado con contraseña: Sorpresa2024" -ForegroundColor Green
    Write-Host "`nConfiguración actual:" -ForegroundColor Cyan
    Get-Content $envPath | Select-String -Pattern "DB_"
} else {
    Write-Host "✗ No se encontró el archivo .env" -ForegroundColor Red
}
