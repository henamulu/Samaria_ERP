# Instrucciones Manuales para Configurar MySQL

Si el script automático no funciona, sigue estos pasos manualmente:

## Opción 1: Usando phpMyAdmin

1. Abre phpMyAdmin en tu navegador: `http://localhost/phpmyadmin`
2. Inicia sesión con:
   - Usuario: `root`
   - Contraseña: `Sorpresa2024`
3. Ve a la pestaña "SQL"
4. Copia y pega estos comandos:

```sql
-- Otorgar permisos desde cualquier host
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'Sorpresa2024' WITH GRANT OPTION;

-- Otorgar permisos desde la IP específica
CREATE USER IF NOT EXISTS 'root'@'172.20.0.1' IDENTIFIED BY 'Sorpresa2024';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'172.20.0.1' WITH GRANT OPTION;

-- Aplicar cambios
FLUSH PRIVILEGES;

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS samariac_samaria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

5. Haz clic en "Ejecutar"

## Opción 2: Usando MySQL Workbench

1. Abre MySQL Workbench
2. Conéctate a tu servidor MySQL
3. Abre una nueva pestaña de consulta
4. Ejecuta los mismos comandos SQL de arriba

## Opción 3: Usando HeidiSQL

1. Abre HeidiSQL
2. Conéctate a tu servidor MySQL
3. Abre una nueva pestaña de consulta
4. Ejecuta los mismos comandos SQL de arriba

## Opción 4: Usando Línea de Comandos (si tienes mysql client)

Abre PowerShell o CMD y ejecuta:

```powershell
# Conectarte a MySQL
mysql -u root -p

# Ingresa la contraseña cuando se solicite: Sorpresa2024

# Luego ejecuta los comandos SQL uno por uno
```

## Después de Configurar

Una vez que hayas ejecutado los comandos SQL, verifica que funcionó:

```bash
cd samaria-erp
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
```

## Verificar que Funcionó

Puedes verificar que los permisos se otorgaron correctamente ejecutando:

```sql
SELECT user, host FROM mysql.user WHERE user = 'root';
```

Deberías ver entradas para:
- `root@localhost`
- `root@%`
- `root@172.20.0.1` (o la IP desde la que te conectas)
