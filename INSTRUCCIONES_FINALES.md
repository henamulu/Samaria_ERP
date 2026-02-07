# Instrucciones Finales de Configuración

## ✅ Pasos Completados

1. ✅ Archivo `.env` configurado para MySQL
2. ✅ Vue.js 3 e Inertia.js instalados
3. ✅ Middleware de Inertia configurado
4. ✅ Páginas de ejemplo creadas (Login, Dashboard)
5. ✅ Rutas web configuradas

## 🔧 Pasos Pendientes (Debes Ejecutarlos)

### 1. Configurar Contraseña de MySQL en .env

**IMPORTANTE:** Edita el archivo `samaria-erp/.env` y configura tu contraseña:

```env
DB_PASSWORD=tu_contraseña_mysql_aqui
```

Si tu MySQL no tiene contraseña, deja `DB_PASSWORD=` vacío.

### 2. Crear Base de Datos

Ejecuta en MySQL (phpMyAdmin, MySQL Workbench, o línea de comandos):

```sql
CREATE DATABASE IF NOT EXISTS samariac_samaria 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

O ejecuta el archivo SQL:
```bash
mysql -u root -p < samaria-erp/CREAR_BD.sql
```

### 3. Ejecutar Migraciones

```bash
cd samaria-erp
php artisan migrate
```

### 4. Ejecutar Seeders

```bash
php artisan db:seed --class=RolePermissionSeeder
```

### 5. Iniciar Servidor de Desarrollo

En una terminal:
```bash
cd samaria-erp
php artisan serve
```

En otra terminal (para compilar assets):
```bash
cd samaria-erp
npm run dev
```

### 6. Acceder a la Aplicación

Abre tu navegador en:
- Frontend: `http://localhost:8000`
- API: `http://localhost:8000/api`

## 📝 Probar la API con Postman/Insomnia

### Endpoint de Login

**POST** `http://localhost:8000/api/login`

Headers:
```
Content-Type: application/json
```

Body (JSON):
```json
{
  "user_name": "tu_usuario",
  "password": "tu_contraseña"
}
```

Respuesta esperada:
```json
{
  "user": {
    "id": 1,
    "user_name": "usuario",
    ...
  },
  "token": "1|xxxxxxxxxxxxx"
}
```

### Endpoint Protegido (Ejemplo: Obtener Clientes)

**GET** `http://localhost:8000/api/customers`

Headers:
```
Authorization: Bearer {token_del_login}
Accept: application/json
```

## 🎨 Estructura del Frontend

```
resources/js/
├── Pages/
│   ├── Login.vue          # Página de login
│   └── Dashboard.vue      # Dashboard principal
├── app.js                # Configuración de Inertia
└── bootstrap.js          # Configuración de Axios
```

## 🔍 Verificar Instalación

### Verificar que todo está instalado:

```bash
# Verificar PHP
php -v

# Verificar Composer
composer --version

# Verificar Node.js
node -v

# Verificar npm
npm -v

# Verificar que las dependencias están instaladas
cd samaria-erp
composer show
npm list
```

### Verificar Base de Datos:

```bash
php artisan db:show
```

## 🐛 Solución de Problemas

### Error: "Access denied for user"
- Verifica las credenciales en `.env`
- Asegúrate de que el usuario MySQL tenga permisos

### Error: "Base de datos no existe"
- Ejecuta el script `CREAR_BD.sql`

### Error: "Class not found"
```bash
composer dump-autoload
```

### Error: "Vite not found"
```bash
npm install
npm run dev
```

### Error: "Token invalid" en API
- Asegúrate de incluir el header `Authorization: Bearer {token}`
- El token expira, vuelve a hacer login

## 📚 Documentación Adicional

- Ver `SETUP.md` para guía completa de configuración
- Ver `README.md` para información general del proyecto
- Ver `RESUMEN_FINAL.md` para resumen de implementación

## 🚀 Próximos Pasos de Desarrollo

1. **Completar Páginas Frontend:**
   - Lista de clientes
   - Lista de entregas
   - Formularios de creación/edición
   - Reportes

2. **Implementar Autenticación Frontend:**
   - Guardar token en localStorage
   - Interceptor de Axios para agregar token automáticamente
   - Manejo de sesión expirada

3. **Agregar Más Funcionalidades:**
   - Filtros y búsqueda
   - Paginación
   - Exportación de datos
   - Gráficos y visualizaciones

## ✨ Estado Actual

- ✅ Backend completo (Laravel 12)
- ✅ API RESTful funcional
- ✅ Autenticación con Sanctum
- ✅ Sistema de roles y permisos
- ✅ Frontend básico configurado (Vue.js + Inertia.js)
- ⏳ Pendiente: Configurar BD y ejecutar migraciones
- ⏳ Pendiente: Desarrollar páginas frontend completas
