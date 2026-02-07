-- Script para crear la base de datos en MySQL
-- Ejecutar este script en MySQL antes de ejecutar las migraciones

CREATE DATABASE IF NOT EXISTS samariac_samaria 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Verificar que la base de datos se creó correctamente
SHOW DATABASES LIKE 'samariac_samaria';
