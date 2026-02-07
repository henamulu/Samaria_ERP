# Sistema ERP Samaria Construction - Laravel 12

## Estado del Proyecto

### ✅ Completado

1. **Análisis de Base de Datos**
   - Documento `database_analysis.md` con análisis completo del esquema
   - Identificación de 99 tablas principales
   - Mapeo de relaciones clave
   - Documento `business_flows.md` con flujos de negocio críticos

2. **Configuración Laravel**
   - Laravel 12 instalado
   - Spatie Laravel Permission instalado y configurado
   - Inertia.js instalado
   - Migraciones de permisos publicadas
   - Seeder de roles y permisos creado

3. **Modelos Eloquent Completos**
   - Customer (con relaciones a deliveries y salesOrders)
   - Supplier (con relaciones a purchaseOrders)
   - Transporter (con relaciones a deliveries y transporterPayments)
   - Delivery (con relaciones a customer, transporter, payment)
   - SalesOrder (con relaciones a customer, deliveries, payment)
   - PurchaseOrder (creado)
   - Payment (con relaciones a supplier, deliveries, salesOrders)
   - TransporterPayment (creado)
   - User (configurado con Spatie Permission, adaptado al esquema sam_user)

4. **Migraciones Configuradas**
   - sam_user - Completa (adaptada al esquema original)
   - sam_customer - Completa con índices
   - sam_supplier - Completa
   - sam_transporter - Completa
   - sam_delivery - Completa con índices
   - sam_sales_order - Completa
   - sam_payment - Completa

5. **Servicios de Negocio**
   - DeliveryService (crear, actualizar, confirmar entregas)
   - PaymentService (crear, aprobar, procesar pagos)
   - ReportService (reportes de entregas, financieros, dashboard)

6. **Controladores**
   - CustomerController (CRUD completo con autorización)
   - DeliveryController (CRUD con servicios)
   - SupplierController (creado)
   - TransporterController (creado)

6. **Controladores Completos**
   - CustomerController (CRUD completo con autorización)
   - SupplierController (creado)
   - TransporterController (creado)
   - DeliveryController (CRUD con servicios)
   - PaymentController (CRUD con aprobaciones)
   - TransporterPaymentController (CRUD completo)
   - ReportController (reportes y dashboard)
   - AuthController (login/logout con Sanctum)

7. **Rutas API**
   - Rutas RESTful para todos los recursos
   - Rutas de autenticación
   - Rutas de aprobaciones
   - Rutas de reportes

8. **Autenticación y Autorización**
   - Laravel Sanctum configurado
   - Spatie Permission con roles y permisos
   - Seeder de roles y permisos
   - Middleware de autenticación

9. **Scripts de Migración**
   - Comando `migrate:data` para migrar datos
   - Soporte para usuarios, clientes, proveedores, transportistas

### 🔄 Pendiente

- Frontend con Vue.js + Inertia.js
- Completar modelos restantes (TransporterAgg, Git, Item, Bank, etc.)
- Tests unitarios e integración

### 📋 Próximos Pasos

1. **Configurar Base de Datos**
   ```bash
   # Editar .env con credenciales de BD
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=samariac_samaria
   DB_USERNAME=tu_usuario
   DB_PASSWORD=tu_password
   ```

2. **Ejecutar Migraciones y Seeders**
   ```bash
   php artisan migrate
   php artisan db:seed --class=RolePermissionSeeder
   ```

3. **Migrar Datos (Opcional)**
   ```bash
   # Modo prueba (dry-run)
   php artisan migrate:data --source-user=usuario --source-password=password --dry-run
   
   # Migración real
   php artisan migrate:data --source-user=usuario --source-password=password
   ```

4. **Probar API**
   ```bash
   # Login
   POST /api/login
   {
     "user_name": "usuario",
     "password": "password"
   }
   
   # Obtener clientes
   GET /api/customers
   Authorization: Bearer {token}
   ```

5. **Instalar Frontend**
   ```bash
   npm install
   npm install @inertiajs/vue3 vue@latest
   ```

## Estructura del Proyecto

```
samaria-erp/
├── app/
│   ├── Models/          # Modelos Eloquent
│   ├── Services/        # Lógica de negocio
│   ├── Repositories/    # Acceso a datos
│   └── Http/
│       ├── Controllers/ # Controladores
│       └── Middleware/   # Middleware
├── database/
│   └── migrations/      # Migraciones
└── resources/
    └── js/             # Frontend Vue.js
```

## Notas Importantes

- Las tablas mantienen el prefijo `sam_` del sistema original
- Los modelos usan `$table` para especificar el nombre de tabla
- Las relaciones se basan en el análisis del esquema SQL
- Se requiere migración de datos desde el sistema actual
