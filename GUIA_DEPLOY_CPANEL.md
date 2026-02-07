# Guía de Despliegue en cPanel - Samaria ERP

Esta guía te ayudará a desplegar el sistema Samaria ERP en un servidor con cPanel paso a paso.

## 📋 Requisitos Previos

- Acceso a cPanel
- PHP 8.1 o superior
- MySQL 5.7+ o MariaDB 10.3+
- Composer instalado en el servidor (o acceso SSH)
- Node.js y npm (para compilar assets)
- Extensiones PHP requeridas:
  - `pdo_mysql`
  - `mbstring`
  - `openssl`
  - `tokenizer`
  - `json`
  - `curl`
  - `fileinfo`
  - `zip`

---

## 🚀 Paso 1: Preparar el Proyecto Localmente

### 1.1. Optimizar para Producción

```bash
# En tu máquina local, antes de subir:
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### 1.2. Crear archivo .env para producción

Crea un archivo `.env.production` con las configuraciones del servidor:

```env
APP_NAME="Samaria ERP"
APP_ENV=production
APP_KEY=base64:TU_CLAVE_GENERADA_AQUI
APP_DEBUG=false
APP_URL=https://tudominio.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tu_base_de_datos
DB_USERNAME=tu_usuario_db
DB_PASSWORD=tu_contraseña_db

SESSION_DRIVER=database
SESSION_LIFETIME=120

# Configuración de correo (opcional)
MAIL_MAILER=smtp
MAIL_HOST=tu_servidor_smtp
MAIL_PORT=587
MAIL_USERNAME=tu_email
MAIL_PASSWORD=tu_contraseña_email
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 1.3. Generar clave de aplicación

```bash
php artisan key:generate
```

Copia la clave generada al archivo `.env.production`.

---

## 📤 Paso 2: Subir Archivos al Servidor

### 2.1. Conectar vía FTP/SFTP

Usa FileZilla, WinSCP o el File Manager de cPanel.

### 2.2. Estructura de directorios en cPanel

Normalmente tu dominio está en:
- `public_html/` - Para el dominio principal
- `public_html/subdominio/` - Para subdominios

**Recomendación:** Si tienes múltiples sitios, crea un subdirectorio:
```
public_html/samaria-erp/
```

### 2.3. Archivos a subir

Sube TODOS los archivos del proyecto EXCEPTO:
- `node_modules/` (se reinstala en el servidor)
- `.git/` (opcional, si no quieres el historial)
- `storage/logs/*.log`
- `.env` (lo crearemos en el servidor)

### 2.4. Estructura final en el servidor

```
public_html/samaria-erp/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── vendor/ (si lo subiste, o lo instalas en el servidor)
├── .env
├── artisan
├── composer.json
└── ...
```

---

## 🗄️ Paso 3: Configurar Base de Datos

### 3.1. Crear base de datos en cPanel

1. Accede a cPanel
2. Ve a **"MySQL Databases"** o **"Bases de Datos MySQL"**
3. Crea una nueva base de datos:
   - Nombre: `samariac_samaria` (o el que prefieras)
   - Anota el nombre completo: `usuario_samariac_samaria`
4. Crea un usuario de base de datos:
   - Usuario: `samaria_user` (o el que prefieras)
   - Contraseña: Genera una segura
   - Anota el nombre completo: `usuario_samaria_user`
5. Asigna el usuario a la base de datos con **todos los privilegios**

### 3.2. Importar datos

**Opción A: Usando phpMyAdmin (Recomendado)**

1. Ve a **phpMyAdmin** en cPanel
2. Selecciona tu base de datos
3. Ve a la pestaña **"Importar"**
4. Selecciona el archivo `samariac_samaria.sql`
5. Ajusta el límite de tiempo si es necesario
6. Haz clic en **"Continuar"**

**Opción B: Usando SSH (si tienes acceso)**

```bash
cd ~/public_html/samaria-erp
mysql -u usuario_samaria_user -p usuario_samariac_samaria < samariac_samaria.sql
```

**Opción C: Usando el script PHP**

1. Sube el archivo `importar_sql.php` al servidor
2. Accede vía navegador: `https://tudominio.com/samaria-erp/importar_sql.php`
3. O ejecuta vía SSH: `php importar_sql.php --clean`

---

## ⚙️ Paso 4: Configurar PHP en cPanel

### 4.1. Seleccionar versión de PHP

1. Ve a **"Select PHP Version"** o **"Selector de Versión PHP"**
2. Selecciona **PHP 8.1** o superior
3. Habilita las extensiones necesarias:
   - ✅ `pdo_mysql`
   - ✅ `mbstring`
   - ✅ `openssl`
   - ✅ `tokenizer`
   - ✅ `json`
   - ✅ `curl`
   - ✅ `fileinfo`
   - ✅ `zip`
   - ✅ `gd` (opcional, para imágenes)
   - ✅ `xml` (opcional)

### 4.2. Ajustar configuración PHP

En **"Options"** o **"Opciones"** de PHP:
- `memory_limit`: 512M o más
- `max_execution_time`: 300 (para importaciones SQL)
- `upload_max_filesize`: 50M
- `post_max_size`: 50M

---

## 📝 Paso 5: Configurar Variables de Entorno

### 5.1. Crear archivo .env

1. En el **File Manager** de cPanel, navega a `public_html/samaria-erp/`
2. Crea un nuevo archivo llamado `.env`
3. Copia el contenido de `.env.production` que preparaste
4. Ajusta los valores:
   - `DB_DATABASE`: El nombre completo de tu base de datos
   - `DB_USERNAME`: El nombre completo de tu usuario
   - `DB_PASSWORD`: La contraseña del usuario
   - `APP_URL`: Tu dominio completo

### 5.2. Verificar permisos del .env

El archivo `.env` debe tener permisos `644` (lectura para todos, escritura solo para el propietario).

---

## 🔧 Paso 6: Instalar Dependencias

### 6.1. Instalar dependencias PHP (Composer)

**Opción A: Si tienes acceso SSH**

```bash
cd ~/public_html/samaria-erp
composer install --optimize-autoloader --no-dev
```

**Opción B: Si NO tienes acceso SSH**

1. Instala Composer localmente en tu máquina
2. Ejecuta: `composer install --optimize-autoloader --no-dev`
3. Sube la carpeta `vendor/` completa al servidor

### 6.2. Instalar dependencias Node.js (opcional, solo si necesitas recompilar)

Si tienes acceso SSH y Node.js instalado:

```bash
cd ~/public_html/samaria-erp
npm install
npm run build
```

Si no tienes acceso SSH, los archivos compilados ya deberían estar en `public/build/` desde tu máquina local.

---

## 📁 Paso 7: Configurar Permisos de Archivos

### 7.1. Permisos de directorios

En el **File Manager** de cPanel, establece estos permisos:

```
storage/                    → 755
storage/framework/          → 755
storage/framework/cache/    → 755
storage/framework/sessions/→ 755
storage/framework/views/    → 755
storage/logs/               → 755
bootstrap/cache/            → 755
```

### 7.2. Permisos de archivos

```
.env                        → 644
artisan                     → 755
```

### 7.3. Usando SSH (si tienes acceso)

```bash
cd ~/public_html/samaria-erp
chmod -R 755 storage bootstrap/cache
chmod 644 .env
chmod 755 artisan
```

---

## 🌐 Paso 8: Configurar el Document Root

### 8.1. Opción A: Subdirectorio (Recomendado si tienes otros sitios)

Si instalaste en `public_html/samaria-erp/`:

1. El **Document Root** debe apuntar a: `public_html/samaria-erp/public`
2. Tu URL será: `https://tudominio.com/samaria-erp`

### 8.2. Opción B: Subdominio (Recomendado)

1. Crea un subdominio en cPanel: `erp.tudominio.com`
2. El **Document Root** será: `public_html/erp/public` (o similar)
3. Sube los archivos a ese directorio
4. Tu URL será: `https://erp.tudominio.com`

### 8.3. Opción C: Dominio Principal

Si es tu único sitio:

1. El **Document Root** debe ser: `public_html/public`
2. Mueve el contenido de `public/` a `public_html/`
3. Mueve el resto de archivos un nivel arriba
4. Ajusta las rutas si es necesario

---

## 🔄 Paso 9: Ejecutar Migraciones y Seeders

### 9.1. Vía SSH (Recomendado)

```bash
cd ~/public_html/samaria-erp
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
```

### 9.2. Vía Navegador (Alternativa)

Crea un archivo temporal `run_migrations.php` en la raíz:

```php
<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

Artisan::call('migrate', ['--force' => true]);
Artisan::call('db:seed', ['--class' => 'RolePermissionSeeder', '--force' => true]);

echo "Migraciones completadas";
```

Accede vía navegador: `https://tudominio.com/samaria-erp/run_migrations.php`

**⚠️ IMPORTANTE:** Elimina este archivo después de ejecutarlo por seguridad.

---

## 👤 Paso 10: Crear Usuario Admin

### 10.1. Vía SSH

```bash
cd ~/public_html/samaria-erp
php verificar_admin.php
```

### 10.2. Vía Navegador

1. Sube el archivo `verificar_admin.php` al servidor
2. Accede: `https://tudominio.com/samaria-erp/verificar_admin.php`
3. **Elimina el archivo después de usarlo**

### 10.3. Credenciales por defecto

- Usuario: `admin`
- Contraseña: `admin123`

**⚠️ Cambia la contraseña inmediatamente después del primer login.**

---

## 🔒 Paso 11: Configuración de Seguridad

### 11.1. Ocultar archivos sensibles

Crea o edita `.htaccess` en la raíz del proyecto:

```apache
# Proteger archivos sensibles
<FilesMatch "^(\.env|\.git|composer\.(json|lock)|package(-lock)?\.json)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Proteger directorios
RedirectMatch 403 ^/storage/.*$
RedirectMatch 403 ^/vendor/.*$
RedirectMatch 403 ^/bootstrap/.*$
```

### 11.2. Proteger archivos PHP de utilidad

Agrega al `.htaccess`:

```apache
# Bloquear acceso a scripts de utilidad
<FilesMatch "^(importar_sql|verificar_admin|actualizar_desde_nuevo_sql|ejecutar_sql)\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 11.3. Configurar APP_DEBUG

Asegúrate de que en `.env`:
```
APP_DEBUG=false
```

---

## 🎨 Paso 12: Verificar Assets Frontend

### 12.1. Verificar que los assets se cargan

1. Accede a tu sitio: `https://tudominio.com/samaria-erp`
2. Abre las herramientas de desarrollador (F12)
3. Ve a la pestaña **Network** o **Red**
4. Recarga la página
5. Verifica que los archivos CSS/JS se cargan desde `/build/`

### 12.2. Si los assets no cargan

**Opción A: Recompilar en el servidor (si tienes SSH)**

```bash
cd ~/public_html/samaria-erp
npm install
npm run build
```

**Opción B: Recompilar localmente y subir**

```bash
# En tu máquina local
npm run build
# Sube la carpeta public/build/ al servidor
```

---

## ✅ Paso 13: Verificación Final

### 13.1. Checklist de verificación

- [ ] El sitio carga sin errores
- [ ] Puedes hacer login con el usuario admin
- [ ] La base de datos está conectada
- [ ] Los assets (CSS/JS) se cargan correctamente
- [ ] Las rutas funcionan (dashboard, sales orders, etc.)
- [ ] No hay errores en los logs (`storage/logs/laravel.log`)

### 13.2. Probar funcionalidades clave

1. **Login**: Accede con `admin` / `admin123`
2. **Dashboard**: Verifica que se muestran los datos
3. **Crear Sales Order**: Prueba crear un nuevo SO
4. **Ver Items**: Verifica que los items se cargan en el dropdown

---

## 🐛 Solución de Problemas Comunes

### Error: "500 Internal Server Error"

1. Verifica los permisos de `storage/` y `bootstrap/cache/`
2. Revisa `storage/logs/laravel.log` para ver el error específico
3. Verifica que `.env` existe y tiene los valores correctos
4. Verifica que `APP_KEY` está configurado

### Error: "Class not found" o "Autoload error"

```bash
# Vía SSH
composer dump-autoload -o
```

### Error: "Permission denied" en storage

```bash
# Vía SSH
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### Error: "Database connection failed"

1. Verifica las credenciales en `.env`
2. Verifica que el usuario tiene permisos en la base de datos
3. Verifica que la base de datos existe
4. Prueba la conexión desde phpMyAdmin

### Los assets no cargan (CSS/JS en blanco)

1. Verifica que `public/build/` existe y tiene archivos
2. Verifica los permisos: `chmod -R 755 public/build`
3. Recompila los assets: `npm run build`

### Error: "The stream or file could not be opened"

```bash
# Vía SSH
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

---

## 📚 Recursos Adicionales

### Archivos importantes a revisar

- `.env` - Configuración del entorno
- `storage/logs/laravel.log` - Logs de errores
- `config/app.php` - Configuración de la aplicación
- `config/database.php` - Configuración de base de datos

### Comandos útiles vía SSH

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔄 Actualizaciones Futuras

### Proceso de actualización

1. Haz backup de la base de datos y archivos
2. Sube los nuevos archivos (excepto `.env`)
3. Vía SSH:
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   ```
4. Verifica que todo funciona

---

## 📞 Soporte

Si encuentras problemas durante el despliegue:

1. Revisa los logs: `storage/logs/laravel.log`
2. Verifica los permisos de archivos
3. Verifica la configuración de `.env`
4. Consulta la documentación de Laravel: https://laravel.com/docs

---

## ✅ Resumen Rápido

1. ✅ Subir archivos al servidor
2. ✅ Crear base de datos y usuario en cPanel
3. ✅ Importar `samariac_samaria.sql`
4. ✅ Configurar PHP 8.1+ con extensiones necesarias
5. ✅ Crear `.env` con credenciales correctas
6. ✅ Instalar dependencias (`composer install`)
7. ✅ Configurar permisos (`storage/`, `bootstrap/cache/`)
8. ✅ Configurar Document Root a `public/`
9. ✅ Ejecutar migraciones
10. ✅ Crear usuario admin
11. ✅ Verificar que todo funciona
12. ✅ Cambiar contraseña de admin

¡Listo! Tu sistema Samaria ERP debería estar funcionando en producción. 🎉
