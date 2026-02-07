# Guía de Configuración - Sistema ERP Samaria

## Paso 1: Crear Base de Datos MySQL

Ejecuta el siguiente comando en MySQL o usa un cliente como phpMyAdmin, MySQL Workbench, o HeidiSQL:

```sql
CREATE DATABASE IF NOT EXISTS samariac_samaria 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

O ejecuta el archivo SQL:
```bash
mysql -u root -p < CREAR_BD.sql
```

## Paso 2: Configurar .env

El archivo `.env` ya está configurado con:
- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=samariac_samaria`
- `DB_USERNAME=root`
- `DB_PASSWORD=` ⚠️ **DEBES CONFIGURAR TU CONTRASEÑA DE MYSQL**

**IMPORTANTE:** Edita el archivo `.env` y configura tu contraseña de MySQL:
```
DB_USERNAME=root
DB_PASSWORD=tu_contraseña_mysql
```

Si tu MySQL no tiene contraseña, deja `DB_PASSWORD=` vacío, pero asegúrate de que el usuario tenga permisos.

## Paso 3: Ejecutar Migraciones

```bash
php artisan migrate
```

Esto creará todas las tablas necesarias en la base de datos.

## Paso 4: Ejecutar Seeders

```bash
php artisan db:seed --class=RolePermissionSeeder
```

Esto creará los roles y permisos iniciales del sistema.

## Paso 5: Probar la API

### Opción 1: Usando Postman

1. **Login** (POST)
   - URL: `http://localhost:8000/api/login`
   - Method: POST
   - Headers: `Content-Type: application/json`
   - Body (raw JSON):
   ```json
   {
     "user_name": "tu_usuario",
     "password": "tu_contraseña"
   }
   ```
   - Respuesta esperada:
   ```json
   {
     "user": {...},
     "token": "1|xxxxxxxxxxxxx"
   }
   ```

2. **Obtener Usuario Actual** (GET)
   - URL: `http://localhost:8000/api/user`
   - Method: GET
   - Headers: 
     - `Authorization: Bearer {token}`
     - `Accept: application/json`

3. **Obtener Clientes** (GET)
   - URL: `http://localhost:8000/api/customers`
   - Method: GET
   - Headers: 
     - `Authorization: Bearer {token}`
     - `Accept: application/json`

### Opción 2: Usando cURL

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"user_name":"tu_usuario","password":"tu_contraseña"}'

# Guarda el token de la respuesta y úsalo en:
curl -X GET http://localhost:8000/api/customers \
  -H "Authorization: Bearer TU_TOKEN_AQUI" \
  -H "Accept: application/json"
```

### Opción 3: Usando Insomnia

1. Crea una nueva petición POST a `http://localhost:8000/api/login`
2. En el body, selecciona JSON y agrega:
   ```json
   {
     "user_name": "tu_usuario",
     "password": "tu_contraseña"
   }
   ```
3. Guarda el token de la respuesta
4. Crea una nueva petición GET a `http://localhost:8000/api/customers`
5. En Headers, agrega: `Authorization: Bearer {token}`

## Paso 6: Iniciar Servidor de Desarrollo

```bash
php artisan serve
```

El servidor estará disponible en: `http://localhost:8000`

## Paso 7: Instalar Frontend (Vue.js + Inertia.js)

```bash
# Instalar dependencias npm
npm install

# Instalar Vue.js 3 e Inertia.js
npm install vue@latest @inertiajs/vue3

# Compilar assets
npm run dev
```

## Endpoints Disponibles

### Autenticación
- `POST /api/login` - Iniciar sesión
- `POST /api/logout` - Cerrar sesión (requiere auth)
- `GET /api/user` - Obtener usuario actual (requiere auth)

### Clientes
- `GET /api/customers` - Listar clientes
- `POST /api/customers` - Crear cliente
- `GET /api/customers/{id}` - Ver cliente
- `PUT /api/customers/{id}` - Actualizar cliente
- `DELETE /api/customers/{id}` - Eliminar cliente

### Proveedores
- `GET /api/suppliers` - Listar proveedores
- `POST /api/suppliers` - Crear proveedor
- `GET /api/suppliers/{id}` - Ver proveedor
- `PUT /api/suppliers/{id}` - Actualizar proveedor
- `DELETE /api/suppliers/{id}` - Eliminar proveedor

### Transportistas
- `GET /api/transporters` - Listar transportistas
- `POST /api/transporters` - Crear transportista
- `GET /api/transporters/{id}` - Ver transportista
- `PUT /api/transporters/{id}` - Actualizar transportista
- `DELETE /api/transporters/{id}` - Eliminar transportista

### Entregas
- `GET /api/deliveries` - Listar entregas
- `POST /api/deliveries` - Crear entrega
- `GET /api/deliveries/{id}` - Ver entrega
- `PUT /api/deliveries/{id}` - Actualizar entrega
- `POST /api/deliveries/{id}/approve` - Aprobar entrega
- `POST /api/deliveries/{id}/confirm` - Confirmar entrega
- `GET /api/deliveries/pending/list` - Listar entregas pendientes

### Pagos
- `GET /api/payments` - Listar pagos
- `POST /api/payments` - Crear pago
- `GET /api/payments/{id}` - Ver pago
- `PUT /api/payments/{id}` - Actualizar pago
- `POST /api/payments/{id}/approve` - Aprobar pago
- `POST /api/payments/{id}/settle` - Liquidar pago
- `GET /api/payments/unpaid/transporters` - Pagos pendientes a transportistas

### Pagos a Transportistas
- `GET /api/transporter-payments` - Listar pagos
- `POST /api/transporter-payments` - Crear pago
- `GET /api/transporter-payments/{id}` - Ver pago
- `PUT /api/transporter-payments/{id}` - Actualizar pago
- `POST /api/transporter-payments/{id}/approve` - Aprobar pago
- `POST /api/transporter-payments/{id}/settle` - Liquidar pago

### Reportes
- `GET /api/reports/dashboard` - Métricas del dashboard
- `GET /api/reports/deliveries` - Reporte de entregas
- `GET /api/reports/financial` - Reporte financiero
- `GET /api/reports/customer-credits` - Créditos pendientes de clientes

## Solución de Problemas

### Error: "Access denied for user"
- Verifica las credenciales en `.env`
- Asegúrate de que el usuario MySQL tenga permisos para crear bases de datos

### Error: "Base de datos no existe"
- Ejecuta el script `CREAR_BD.sql` o crea la base de datos manualmente

### Error: "Class not found"
- Ejecuta: `composer dump-autoload`

### Error: "Migration table not found"
- Ejecuta: `php artisan migrate:install`

### Error: "Token invalid"
- Asegúrate de incluir el header `Authorization: Bearer {token}` en todas las peticiones
- El token expira después de cierto tiempo, vuelve a hacer login

## Notas Importantes

- Todas las rutas API requieren autenticación excepto `/api/login`
- Los tokens se generan con Laravel Sanctum
- Las contraseñas deben estar hasheadas en la base de datos
- El sistema usa el prefijo `sam_` para mantener compatibilidad con el sistema anterior
