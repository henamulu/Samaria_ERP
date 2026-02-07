# Flujos de Negocio Críticos - Sistema ERP Samaria

## 1. Flujo de Entrega a Cliente

### Proceso Completo
```
1. Crear Orden de Venta (Sales Order)
   ├─ Seleccionar Cliente
   ├─ Seleccionar Item/Producto
   ├─ Definir Cantidad y Precio
   └─ Estado: Pending

2. Aprobar Orden de Venta
   ├─ Checked by: Supervisor
   ├─ Approved by: Manager
   └─ Estado: Approved

3. Generar GIT (Goods Issue Transfer)
   ├─ Relacionado con Sales Order
   ├─ Asignar Transportista
   └─ Estado: Pending

4. Crear Entrega (Delivery)
   ├─ Asociar GIT
   ├─ Asignar Camión (truck_plate_no)
   ├─ Asignar Conductor (driver_name)
   ├─ Definir Destino (delivered_to)
   └─ Estado: Pending

5. Confirmar Entrega
   ├─ accepted_by: Cliente
   ├─ signed_by_customer: Sí/No
   ├─ confirm_signed: Sí/No
   └─ Estado: Done

6. Generar Factura/Pago
   ├─ Crear Payment Request
   ├─ Aprobar Pago
   └─ Estado: Settled
```

### Estados Clave
- **Sales Order**: Pending → Checked → Approved → Done
- **Delivery**: Pending → Done
- **Payment**: Pending → Approved → Settled

## 2. Flujo de Pago a Transportista

### Proceso Completo
```
1. Registrar Contrato de Transporte (Transporter Agg)
   ├─ Seleccionar Transportista
   ├─ Definir Precio Unitario
   ├─ Asignar Propietario de Camión
   ├─ Asignar Placa
   └─ Estado: InActive

2. Aprobar Contrato
   ├─ d_r (Delivery Request): Yes
   ├─ d_r_by: Supervisor
   ├─ approved_by: Manager
   └─ Estado: Approved

3. Registrar Viajes/Entregas
   ├─ Cada entrega genera registro en Transporter Agg
   ├─ Se calcula total por viaje
   └─ Estado: Active

4. Generar Solicitud de Pago
   ├─ Agrupar viajes por transportista/propietario
   ├─ Calcular total a pagar
   └─ Estado: Pending

5. Aprobar Pago
   ├─ Verificar entregas completadas
   ├─ Aprobar monto
   └─ Estado: Approved

6. Procesar Pago
   ├─ Registrar en Transporter Payment
   ├─ Actualizar balance
   └─ Estado: Settled
```

### Estados Clave
- **Transporter Agg**: InActive → Approved → Active
- **Transporter Payment**: Pending → Approved → Settled

## 3. Flujo de Orden de Compra

### Proceso Completo
```
1. Crear Requisición de Compra
   ├─ Seleccionar Proveedor
   ├─ Definir Items y Cantidades
   └─ Estado: Pending

2. Aprobar Requisición
   ├─ Checked by: Supervisor
   ├─ Approved by: Manager
   └─ Estado: Approved

3. Crear Orden de Compra (PO)
   ├─ Basada en Requisición
   ├─ Confirmar Precios
   └─ Estado: Pending

4. Confirmar Orden de Compra
   ├─ Confirmar con Proveedor
   └─ Estado: Confirmed

5. Recibir Mercancía (GRV - Goods Receive Voucher)
   ├─ Verificar Cantidades
   ├─ Registrar en Inventario
   └─ Estado: Received

6. Procesar Pago a Proveedor
   ├─ Crear Payment Request
   ├─ Aprobar Pago
   └─ Estado: Settled
```

## 4. Flujo de Crédito a Cliente

### Proceso Completo
```
1. Entrega a Cliente (sin pago inmediato)
   ├─ Delivery con used_for='customer'
   ├─ Estado: Done
   └─ Se genera deuda

2. Registrar Crédito
   ├─ Se registra en sam_sales_order_c (remaning)
   ├─ Se registra en sam_income
   └─ Estado: Pending

3. Solicitar Pago
   ├─ Cliente solicita factura
   ├─ Se genera Payment Request
   └─ Estado: Pending

4. Procesar Pago
   ├─ Registrar pago parcial o total
   ├─ Actualizar remaning
   └─ Estado: Settled
```

## 5. Procesos de Aprobación

### Niveles de Aprobación

#### Nivel 1: Supervisor/Usuario
- Verificar datos
- Marcar como "Checked"
- Campos: `checked_by`

#### Nivel 2: Manager/Admin
- Aprobar transacciones
- Autorizar pagos
- Campos: `approved_by`

#### Nivel 3: Finanzas
- Aprobar pagos grandes
- Reconciliación bancaria
- Campos: `approved_by` en tablas financieras

### Reglas de Aprobación
- **Órdenes de Venta**: Requieren aprobación si monto > umbral
- **Pagos**: Requieren aprobación según monto
- **Contratos de Transporte**: Requieren aprobación siempre
- **Órdenes de Compra**: Requieren aprobación si monto > umbral

## 6. Reportes Críticos

### Reportes Financieros
1. **Reporte de Pagos Pendientes a Transportistas**
   - Agrupar por propietario de camión
   - Calcular total entregado vs pagado
   - Mostrar remanente

2. **Reporte de Créditos a Clientes**
   - Listar clientes con saldo pendiente
   - Mostrar facturas vencidas
   - Calcular total adeudado

3. **Reporte de Transacciones**
   - Todas las transacciones por tipo
   - Agrupar por mes
   - Totales por categoría

### Reportes Operacionales
1. **Dashboard de Entregas**
   - Entregas del día
   - Entregas pendientes
   - Entregas por cliente

2. **Reporte de Inventario**
   - Stock disponible
   - Movimientos recientes
   - Items con bajo stock

## 7. Validaciones Críticas

### Validaciones de Negocio
- No se puede aprobar pago sin entregas confirmadas
- No se puede crear entrega sin orden de venta aprobada
- No se puede procesar pago sin aprobación
- Cantidad entregada no puede exceder cantidad ordenada
- Precio no puede cambiar después de aprobación (sin proceso especial)

### Validaciones de Integridad
- Todos los campos requeridos deben estar presentes
- IDs de relaciones deben existir
- Estados deben seguir el flujo correcto
- Fechas deben ser consistentes

## 8. Notificaciones y Alertas

### Alertas Automáticas
- Pagos pendientes de aprobación
- Entregas pendientes de confirmación
- Créditos vencidos
- Stock bajo
- Contratos próximos a vencer
