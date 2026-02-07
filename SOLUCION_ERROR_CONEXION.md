# Solución: Error de Conexión a MySQL

## Error Actual
```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'172.20.0.1' (using password: YES)
```

## Causas Posibles

1. **MySQL está rechazando conexiones desde la IP 172.20.0.1**
   - Esta IP sugiere que estás usando Docker, WSL2, o una red virtual
   - MySQL puede estar configurado para solo aceptar conexiones desde `localhost` o `127.0.0.1`

2. **El usuario root no tiene permisos desde esa IP**
   - MySQL puede tener restricciones de host para el usuario root

3. **La contraseña es incorrecta**
   - Aunque el error dice "using password: YES", la contraseña podría ser incorrecta

## Soluciones

### Solución 1: Verificar y Otorgar Permisos en MySQL

Conéctate a MySQL usando un cliente (phpMyAdmin, MySQL Workbench, o línea de comandos) y ejecuta:

```sql
-- Verificar usuarios y hosts permitidos
SELECT user, host FROM mysql.user WHERE user = 'root';

-- Otorgar permisos al usuario root desde cualquier host
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'Sorpresa2024' WITH GRANT OPTION;
FLUSH PRIVILEGES;

-- O específicamente para la IP 172.20.0.1
GRANT ALL PRIVILEGES ON *.* TO 'root'@'172.20.0.1' IDENTIFIED BY 'Sorpresa2024' WITH GRANT OPTION;
FLUSH PRIVILEGES;
```

### Solución 2: Crear un Usuario Específico para la Aplicación

```sql
-- Crear usuario específico
CREATE USER 'samaria_user'@'%' IDENTIFIED BY 'Sorpresa2024';
GRANT ALL PRIVILEGES ON samariac_samaria.* TO 'samaria_user'@'%';
FLUSH PRIVILEGES;
```

Luego actualiza el `.env`:
```
DB_USERNAME=samaria_user
DB_PASSWORD=Sorpresa2024
```

### Solución 3: Verificar Configuración de MySQL

Si estás usando Docker o WSL2, verifica que MySQL esté configurado para aceptar conexiones externas:

1. **En Docker:**
   - Verifica que el puerto 3306 esté expuesto
   - Verifica la configuración de `bind-address` en `my.cnf`

2. **En WSL2:**
   - Asegúrate de que MySQL esté corriendo: `sudo service mysql status`
   - Verifica que `bind-address` en `/etc/mysql/mysql.conf.d/mysqld.cnf` sea `0.0.0.0` o `127.0.0.1`

### Solución 4: Verificar que MySQL Esté Corriendo

```bash
# Windows (PowerShell)
Get-Service | Where-Object {$_.Name -like "*mysql*"}

# Linux/WSL
sudo service mysql status
# o
sudo systemctl status mysql
```

### Solución 5: Probar Conexión Manualmente

Usa un cliente MySQL para verificar que puedes conectarte:

```bash
# Desde línea de comandos (si tienes mysql client instalado)
mysql -u root -p -h 127.0.0.1
# Ingresa la contraseña: Sorpresa2024
```

### Solución 6: Cambiar Host en .env

Si estás usando Docker, intenta cambiar el host:

```env
DB_HOST=localhost
# o
DB_HOST=mysql  # si el contenedor se llama 'mysql'
```

## Verificación Rápida

Ejecuta este script para verificar la conexión:

```bash
cd samaria-erp
php create_database.php
```

Si el script funciona, entonces el problema está en Laravel. Si no funciona, el problema está en la configuración de MySQL.

## Pasos Recomendados

1. **Conéctate a MySQL** usando phpMyAdmin, MySQL Workbench, o línea de comandos
2. **Ejecuta los comandos SQL** de la Solución 1 o 2
3. **Verifica la conexión** ejecutando `php create_database.php`
4. **Ejecuta las migraciones** con `php artisan migrate`

## Nota Importante

Si estás usando Docker, asegúrate de que:
- El contenedor de MySQL esté corriendo
- El puerto 3306 esté mapeado correctamente
- Las variables de entorno estén configuradas correctamente

Si estás usando WSL2, la IP `172.20.0.1` es normal y necesitas otorgar permisos específicos a esa IP o usar `%` para permitir desde cualquier host.
