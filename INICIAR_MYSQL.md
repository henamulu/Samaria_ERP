# Cómo Iniciar MySQL

## Problema
El servicio MySQL `MYSQL91` está detenido y requiere permisos de administrador para iniciarlo.

## Solución: Iniciar MySQL Manualmente

### Opción 1: Usando PowerShell como Administrador

1. **Abre PowerShell como Administrador:**
   - Presiona `Windows + X`
   - Selecciona "Windows PowerShell (Administrador)" o "Terminal (Administrador)"

2. **Ejecuta estos comandos:**
   ```powershell
   Start-Service MYSQL91
   ```

3. **Verifica que esté corriendo:**
   ```powershell
   Get-Service MYSQL91
   ```

### Opción 2: Usando el Administrador de Servicios de Windows

1. **Abre el Administrador de Servicios:**
   - Presiona `Windows + R`
   - Escribe: `services.msc`
   - Presiona Enter

2. **Busca el servicio MySQL:**
   - Busca "MYSQL91" o "MySQL"
   - Haz clic derecho sobre él
   - Selecciona "Iniciar"

### Opción 3: Usando el Panel de Control de MySQL

Si tienes MySQL instalado con el instalador oficial:

1. Busca "MySQL" en el menú de inicio
2. Abre "MySQL Installer - Community"
3. Ve a "Server Status"
4. Haz clic en "Start" para iniciar el servidor

### Opción 4: Si usas XAMPP/WAMP

1. **XAMPP:**
   - Abre el Panel de Control de XAMPP
   - Haz clic en "Start" junto a MySQL

2. **WAMP:**
   - Haz clic en el icono de WAMP en la bandeja del sistema
   - Ve a "MySQL" → "Start Service"

## Verificar que MySQL Está Corriendo

Después de iniciar MySQL, verifica la conexión:

```powershell
# Verificar servicio
Get-Service MYSQL91

# Probar conexión al puerto
Test-NetConnection -ComputerName 127.0.0.1 -Port 3306

# Probar conexión desde PHP
cd samaria-erp
php test_mysql_connection.php
```

## Si MySQL No Inicia

### Verificar Logs de MySQL

Los logs de MySQL suelen estar en:
- `C:\ProgramData\MySQL\MySQL Server 9.1\Data\*.err`
- O en la carpeta de instalación de MySQL

### Problemas Comunes

1. **Puerto 3306 ya en uso:**
   - Verifica si otro proceso está usando el puerto:
     ```powershell
     netstat -ano | findstr :3306
     ```

2. **MySQL no se instaló correctamente:**
   - Reinstala MySQL o usa XAMPP/WAMP

3. **Permisos insuficientes:**
   - Asegúrate de ejecutar como Administrador

## Después de Iniciar MySQL

Una vez que MySQL esté corriendo:

1. **Verifica la conexión:**
   ```powershell
   cd samaria-erp
   php test_mysql_connection.php
   ```

2. **Inicia Laravel:**
   ```powershell
   php artisan serve
   ```

3. **Accede a la aplicación:**
   - Frontend: http://localhost:8000
   - API: http://localhost:8000/api

## Comando Rápido (Requiere Administrador)

```powershell
# Ejecutar PowerShell como Administrador, luego:
Start-Service MYSQL91
Get-Service MYSQL91
```
