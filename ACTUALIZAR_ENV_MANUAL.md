# Actualizar Contraseña en .env - Instrucciones Manuales

## Problema
La contraseña en el archivo `.env` es incorrecta. La contraseña correcta de MySQL es: **Sorpresa2024**

## Solución Rápida

### Paso 1: Abrir el archivo .env

1. Ve a la carpeta `samaria-erp`
2. Abre el archivo `.env` con un editor de texto (Notepad, VS Code, etc.)

### Paso 2: Cambiar la Contraseña

Busca esta línea:
```env
DB_PASSWORD=root
```

Y cámbiala por:
```env
DB_PASSWORD=Sorpresa2024
```

### Paso 3: Guardar el Archivo

Guarda el archivo `.env`

### Paso 4: Limpiar Caché y Ejecutar Migraciones

En PowerShell, ejecuta:

```powershell
cd samaria-erp
php artisan config:clear
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
```

## Verificar que Funcionó

Ejecuta:

```powershell
php test_mysql_connection.php
```

Deberías ver:
```
✓ Conexión exitosa a MySQL
✓ Conexión a la base de datos 'samariac_samaria' exitosa
```

## Resumen de Credenciales Correctas

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=samariac_samaria
DB_USERNAME=root
DB_PASSWORD=Sorpresa2024
```

## Después de Actualizar

Una vez actualizado el `.env`:

1. **Limpiar caché:**
   ```powershell
   php artisan config:clear
   ```

2. **Ejecutar migraciones:**
   ```powershell
   php artisan migrate
   ```

3. **Ejecutar seeders:**
   ```powershell
   php artisan db:seed --class=RolePermissionSeeder
   ```

4. **Iniciar servidor:**
   ```powershell
   php artisan serve
   ```

5. **Acceder a la aplicación:**
   - Frontend: http://localhost:8000
   - API: http://localhost:8000/api
