# Solución: Error de Conexión a MySQL (2002)

## Error
```
SQLSTATE[HY000] [2002] No se puede establecer una conexión ya que el equipo de destino denegó expresamente dicha conexión
```

## Causas Posibles

1. **MySQL no está corriendo**
2. **MySQL está escuchando en otro puerto**
3. **Firewall bloqueando el puerto 3306**
4. **MySQL configurado para escuchar solo en localhost/socket**

## Soluciones

### Solución 1: Verificar que MySQL esté Corriendo

#### Windows (PowerShell):
```powershell
Get-Service | Where-Object {$_.Name -like "*mysql*"}
```

Si no está corriendo:
```powershell
Start-Service MySQL
# o
Start-Service MySQL80
# o el nombre de tu servicio MySQL
```

#### Verificar procesos:
```powershell
Get-Process | Where-Object {$_.ProcessName -like "*mysql*"}
```

### Solución 2: Verificar el Puerto

```powershell
Test-NetConnection -ComputerName 127.0.0.1 -Port 3306
```

Si el puerto no está abierto:
- Verifica que MySQL esté corriendo
- Verifica la configuración de MySQL (my.ini o my.cnf)

### Solución 3: Verificar Configuración de MySQL

Busca el archivo de configuración de MySQL:
- Windows: `C:\ProgramData\MySQL\MySQL Server X.X\my.ini`
- O en la carpeta de instalación de MySQL

Verifica que tenga:
```ini
[mysqld]
bind-address = 127.0.0.1
# o
bind-address = 0.0.0.0
port = 3306
```

### Solución 4: Probar Conexión Manualmente

Ejecuta el script de prueba:
```powershell
cd samaria-erp
php test_mysql_connection.php
```

### Solución 5: Verificar Credenciales en .env

Asegúrate de que el archivo `.env` tenga:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=samariac_samaria
DB_USERNAME=root
DB_PASSWORD=root
```

### Solución 6: Usar localhost en lugar de 127.0.0.1

Si `127.0.0.1` no funciona, intenta cambiar en `.env`:
```env
DB_HOST=localhost
```

### Solución 7: Verificar Firewall

Asegúrate de que el firewall de Windows no esté bloqueando el puerto 3306:

```powershell
# Ver reglas de firewall
Get-NetFirewallRule | Where-Object {$_.DisplayName -like "*mysql*"}
```

### Solución 8: Reiniciar MySQL

```powershell
# Detener
Stop-Service MySQL
# o
Stop-Service MySQL80

# Iniciar
Start-Service MySQL
# o
Start-Service MySQL80
```

## Verificación Rápida

1. **Verificar que MySQL esté corriendo:**
   ```powershell
   Get-Service | Where-Object {$_.Name -like "*mysql*"}
   ```

2. **Probar conexión:**
   ```powershell
   cd samaria-erp
   php test_mysql_connection.php
   ```

3. **Si la conexión funciona pero Laravel no:**
   - Limpia la caché de configuración:
     ```powershell
     php artisan config:clear
     php artisan cache:clear
     ```

## Comandos Útiles

```powershell
# Ver servicios MySQL
Get-Service | Where-Object {$_.Name -like "*mysql*"}

# Iniciar MySQL
Start-Service MySQL80

# Verificar puerto
Test-NetConnection -ComputerName 127.0.0.1 -Port 3306

# Probar conexión
cd samaria-erp
php test_mysql_connection.php

# Limpiar caché de Laravel
php artisan config:clear
php artisan cache:clear
```

## Si Nada Funciona

1. Verifica que MySQL esté instalado correctamente
2. Intenta conectarte usando un cliente MySQL (phpMyAdmin, MySQL Workbench, HeidiSQL)
3. Si puedes conectarte con un cliente pero no con Laravel, el problema está en la configuración de Laravel
4. Verifica los logs de MySQL para ver qué está pasando
