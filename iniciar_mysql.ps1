# Script PowerShell para iniciar MySQL
# Ejecuta: .\iniciar_mysql.ps1

Write-Host "=== Buscando Servicios MySQL ===" -ForegroundColor Cyan

$mysqlServices = Get-Service | Where-Object {
    $_.Name -like "*mysql*" -or 
    $_.DisplayName -like "*mysql*" -or
    $_.Name -like "*mariadb*" -or
    $_.DisplayName -like "*mariadb*"
}

if ($mysqlServices) {
    Write-Host "`nServicios MySQL encontrados:" -ForegroundColor Green
    $mysqlServices | Format-Table Name, Status, DisplayName -AutoSize
    
    foreach ($service in $mysqlServices) {
        if ($service.Status -eq 'Stopped') {
            Write-Host "`nIniciando servicio: $($service.Name)..." -ForegroundColor Yellow
            try {
                Start-Service -Name $service.Name
                Write-Host "✓ Servicio $($service.Name) iniciado exitosamente" -ForegroundColor Green
            } catch {
                Write-Host "✗ Error al iniciar $($service.Name): $_" -ForegroundColor Red
            }
        } else {
            Write-Host "✓ Servicio $($service.Name) ya está corriendo" -ForegroundColor Green
        }
    }
} else {
    Write-Host "`n✗ No se encontraron servicios MySQL instalados" -ForegroundColor Red
    Write-Host "`nPosibles soluciones:" -ForegroundColor Yellow
    Write-Host "1. Verifica que MySQL esté instalado"
    Write-Host "2. Busca el ejecutable de MySQL y inícialo manualmente"
    Write-Host "3. Si usas XAMPP/WAMP, inicia MySQL desde el panel de control"
    Write-Host "4. Si usas Docker, verifica que el contenedor esté corriendo"
}

Write-Host "`n=== Verificando Conexión ===" -ForegroundColor Cyan
Start-Sleep -Seconds 2

$testConnection = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -WarningAction SilentlyContinue
if ($testConnection.TcpTestSucceeded) {
    Write-Host "✓ MySQL está escuchando en el puerto 3306" -ForegroundColor Green
    Write-Host "`nAhora puedes ejecutar:" -ForegroundColor Cyan
    Write-Host "  php artisan serve" -ForegroundColor White
} else {
    Write-Host "✗ MySQL aún no está escuchando en el puerto 3306" -ForegroundColor Red
    Write-Host "`nVerifica:" -ForegroundColor Yellow
    Write-Host "1. Que MySQL esté completamente iniciado (espera unos segundos)"
    Write-Host "2. Que el puerto 3306 no esté bloqueado por firewall"
    Write-Host "3. Que MySQL esté configurado para escuchar en 127.0.0.1:3306"
}
