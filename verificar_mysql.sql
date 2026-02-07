-- Script SQL para verificar y configurar permisos en MySQL
-- Ejecuta estos comandos en MySQL (phpMyAdmin, MySQL Workbench, o línea de comandos)

-- 1. Ver usuarios y hosts actuales
SELECT user, host FROM mysql.user WHERE user = 'root';

-- 2. Otorgar permisos al usuario root desde cualquier host
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'Sorpresa2024' WITH GRANT OPTION;
FLUSH PRIVILEGES;

-- 3. Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS samariac_samaria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 4. Verificar que la base de datos se creó
SHOW DATABASES LIKE 'samariac_samaria';

-- 5. (Opcional) Crear un usuario específico para la aplicación
-- CREATE USER 'samaria_user'@'%' IDENTIFIED BY 'Sorpresa2024';
-- GRANT ALL PRIVILEGES ON samariac_samaria.* TO 'samaria_user'@'%';
-- FLUSH PRIVILEGES;
