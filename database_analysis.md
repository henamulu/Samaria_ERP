# Análisis del Esquema de Base de Datos - Samaria ERP

## Resumen General
- **Base de datos**: `samariac_samaria`
- **Total de tablas**: ~99 tablas
- **Motor**: InnoDB / MyISAM (mixto)
- **Charset**: utf8mb4

## Tablas Principales Identificadas

### 1. Entidades Core

#### `sam_user` (Usuarios)
- Campos clave: `id`, `user_name`, `user_email`, `user_password`, `role`, `user_status`
- Roles: Admin, User, cashier
- Relaciones: Base para autenticación

#### `sam_customer` (Clientes)
- Campos clave: `id`, `customer_type`, `company_name`, `firstname`, `lastname`, `tin_no`, `withholding`, `status`
- Tipos: company, ind (individual)
- Relaciones: Referenciado en entregas, órdenes de venta

#### `sam_supplier` (Proveedores)
- Campos clave: `id`, `company_name`, `status`
- Relaciones: Referenciado en órdenes de compra, entregas

#### `sam_transporter` (Transportistas)
- Campos clave: `id`, `company_name`, `firstname`, `lastname`
- Relaciones: Contratos de transporte, pagos

#### `sam_transporter_truck_owners` (Propietarios de Camiones)
- Campos clave: `id`, `firstname`, `lastname`
- Relaciones: Con `sam_transporter_agg` (owner)

### 2. Gestión de Inventario y Entregas

#### `sam_delivery` (Entregas) - TABLA CRÍTICA
- Campos clave: `id`, `d_no`, `source`, `used_for`, `project`, `item`, `quantity`, `unit_price`, `status`
- Estados: Done, Pending, etc.
- Relaciones:
  - `git_id` -> `sam_git`
  - `t_id` -> `sam_transporter`
  - `tp_id` -> `sam_transporter_payment`
  - `project` -> `sam_customer.id`
  - `p_id` -> `sam_payment`
  - `pr_id` -> Payment request
  - `ex_id` -> External payment

#### `sam_git` (Goods Issue Transfer)
- Campos clave: `id`, `so_id`, `supplier_id`, `unit_price`, `tax_p`
- Relaciones: `so_id` -> `sam_sales_order`

#### `sam_item` (Artículos)
- Campos clave: `id`, `item_name`, `category`, `unit`
- Categorías: aggregate, cement, sand, etc.

### 3. Órdenes y Contratos

#### `sam_sales_order` (Órdenes de Venta)
- Campos clave: `id`, `so_no`, `customer`, `item_id`, `unit_price`, `tax_p`, `status`
- Estados: Approved, Checked, Done
- Relaciones: `customer` -> `sam_customer.id`

#### `sam_purchase_order` (Órdenes de Compra)
- Campos clave: `id`, `po_no`, `supplier`, `status`
- Estados: Done, Pending

#### `sam_transporter_agg` (Contratos de Transporte) - TABLA CRÍTICA
- Campos clave: `id`, `t_id`, `a_no`, `unit_price`, `tax_p`, `size`, `plate_no`, `site`, `owner`, `supplier`, `status`, `agg_status`
- Estados: Approved, InActive, Active, Void
- Relaciones:
  - `t_id` -> `sam_transporter.id`
  - `owner` -> `sam_transporter_truck_owners.id`
  - `site` -> `sam_customer.id`
  - `supplier` -> `sam_supplier.id`

### 4. Finanzas

#### `sam_payment` (Pagos)
- Campos clave: `id`, `p_no`, `po_no`, `p_type`, `net_amount`, `status`
- Tipos: credit, transporter, etc.
- Estados: Approved, Void, Pending

#### `sam_transporter_payment` (Pagos a Transportistas)
- Campos clave: `id`, `tp_no`, `ta_id`, `u_price`, `status`
- Relaciones: `ta_id` -> `sam_transporter_agg.id`

#### `sam_transporter_payment_net` (Detalle de Pagos Netos)
- Campos clave: `id`, `tp_no`, `net_amount`, `remaning`
- Relaciones: `tp_no` -> `sam_transporter_payment.tp_no`

#### `sam_bank_balance` (Balance Bancario)
- Campos clave: `id`, `balance`

#### `sam_income` (Ingresos)
- Campos clave: `id`, `i_amount`, `coll_type`, `status`

#### `sam_ext_payment_c` (Pagos Externos - Crédito)
- Campos clave: `id`, `p_no`, `balance`, `status`

### 5. Reportes y Transacciones

#### `sam_all_transaction` (Todas las Transacciones)
- Campos clave: `id`, `t_type`, `amount`, `t_date`, `ref_no`, `status`
- Tipos: sales, credit, po, petty, vat, purchase_advance, admin_expense

## Relaciones Clave Identificadas

### Flujo de Entrega a Pago
```
sam_delivery
  ├─> sam_git (git_id)
  │   └─> sam_sales_order (so_id)
  │       └─> sam_customer (customer)
  ├─> sam_transporter_agg (t_id + plate_no)
  │   ├─> sam_transporter (t_id)
  │   └─> sam_transporter_truck_owners (owner)
  ├─> sam_transporter_payment (tp_id)
  │   └─> sam_transporter_payment_net (tp_no)
  └─> sam_payment (p_id)
```

### Flujo de Crédito
```
sam_delivery (used_for='customer')
  ├─> sam_sales_order_c (remaning)
  └─> sam_income (pr_id)
      └─> sam_payment (p_id)
```

## Problemas de Diseño Identificados

1. **Falta de Foreign Keys**: Las relaciones son por ID pero sin constraints
2. **Campos de texto para IDs**: Muchos campos de relación son VARCHAR en lugar de INT
3. **Nomenclatura inconsistente**: Mezcla de inglés y español
4. **Campos redundantes**: Información duplicada en múltiples tablas
5. **Sin timestamps**: Fechas como VARCHAR en lugar de DATE/DATETIME
6. **Estados como strings**: Deberían ser ENUMs o tablas de referencia

## Prioridades para Migración

### Fase 1 (Crítico)
- sam_user
- sam_customer
- sam_supplier
- sam_transporter
- sam_delivery

### Fase 2 (Importante)
- sam_sales_order
- sam_purchase_order
- sam_transporter_agg
- sam_payment
- sam_transporter_payment

### Fase 3 (Complementario)
- sam_git
- sam_income
- sam_bank_balance
- sam_all_transaction
