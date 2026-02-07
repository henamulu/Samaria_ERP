# Cómo Restablecer la Contraseña de MySQL

## Si No Recuerdas la Contraseña de MySQL

### Opción 1: Usar el Instalador de MySQL

1. Busca "MySQL Installer - Community" en el menú de inicio
2. Abre el instalador
3. Ve a "Reconfigure" o "Modify"
4. Busca la opción de cambiar la contraseña del usuario root

### Opción 2: Restablecer desde Línea de Comandos (Requiere Detener MySQL)

1. **Detener MySQL:**
   ```powershell
   Stop-Service MYSQL91
   ```

2. **Iniciar MySQL en modo seguro (sin verificación de permisos):**
   ```powershell
   # Necesitas encontrar la ruta de instalación de MySQL
   # Generalmente está en: C:\Program Files\MySQL\MySQL Server 9.1\bin\
   
   cd "C:\Program Files\MySQL\MySQL Server 9.1\bin"
   mysqld --skip-grant-tables --console
   ```

3. **En otra terminal, conectar sin contraseña:**
   ```powershell
   mysql -u root
   ```

4. **Cambiar la contraseña:**
   ```sql
   USE mysql;
   UPDATE user SET authentication_string=PASSWORD('root') WHERE User='root';
   FLUSH PRIVILEGES;
   EXIT;
   ```

5. **Detener MySQL y reiniciarlo normalmente:**
   ```powershell
   # Detener el proceso mysqld que está corriendo en modo seguro
   # Luego iniciar el servicio normalmente
   Start-Service MYSQL91
   ```

### Opción 3: Si Usas XAMPP/WAMP

**XAMPP:**
- La contraseña por defecto suele estar **vacía**
- Prueba con: `DB_PASSWORD=` (vacío)

**WAMP:**
- La contraseña por defecto suele estar **vacía**
- Prueba con: `DB_PASSWORD=` (vacío)

### Opción 4: Crear un Nuevo Usuario

Si no puedes restablecer la contraseña de root, puedes crear un nuevo usuario:

1. **Conéctate a MySQL (necesitarás la contraseña actual o acceso de administrador)**
2. **Ejecuta:**
   ```sql
   CREATE USER 'samaria_user'@'localhost' IDENTIFIED BY 'root';
   GRANT ALL PRIVILEGES ON samariac_samaria.* TO 'samaria_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. **Actualiza el .env:**
   ```env
   DB_USERNAME=samaria_user
   DB_PASSWORD=root
   ```

## Verificar la Contraseña Actual

Si tienes acceso a phpMyAdmin o MySQL Workbench:

1. Intenta conectarte con diferentes contraseñas comunes:
   - (vacía)
   - root
   - password
   - 123456
   - La que configuraste durante la instalación

2. Si puedes conectarte, verás la contraseña correcta o podrás cambiarla desde la interfaz

## Después de Restablecer

Una vez que tengas la contraseña correcta:

1. **Actualiza el .env:**
   ```env
   DB_PASSWORD=tu_contraseña_correcta
   ```

2. **Prueba la conexión:**
   ```powershell
   cd samaria-erp
   php test_mysql_connection.php
   ```

3. **Si funciona, inicia Laravel:**
   ```powershell
   php artisan serve
   ```
