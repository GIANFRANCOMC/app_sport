# 24 - Compras

## Propósito

Registra órdenes y facturas de compra, controla lo pendiente por recibir y alimenta el inventario con el costo real de cada recepción. El documento comercial y la recepción física son eventos distintos, pero `delivery_mode` permite decidir si la entrada al almacén es inmediata o queda pendiente.

## Archivos principales

- Rutas: `routes/System/Purchases/Purchase.php`
- Controlador: `PurchaseController`
- Validaciones: `StorePurchaseRequest`, `ReceivePurchaseRequest`
- Servicio: `PurchaseService`
- Configuración: `PurchaseConfigService`
- Exportación: `PurchaseListExport`
- Vista: `resources/js/System/Pages/Purchases/purchases/main.vue`

## Flujo

1. Se registra proveedor, almacén de recepción, moneda, documento, fechas y productos.
2. Cada detalle guarda cantidad solicitada y costo unitario.
3. La compra guarda `delivery_mode`: `immediate` para recibir todo al crear o `pending` para recepciones posteriores.
4. Si la entrega es inmediata, el backend genera la recepción total y actualiza inventario en la misma transacción.
5. Si la entrega queda pendiente, una recepción posterior puede incluir parte o todo lo pendiente.
6. Cada cantidad recibida genera una entrada `purchase` mediante `InventoryMovementService`.
7. El costo recibido recalcula el promedio ponderado del producto en ese almacén y la compra cambia a `partial` o `received` según el avance.

## Reglas de negocio

- Solo admite proveedores activos de la empresa.
- El almacén debe pertenecer a la empresa.
- Solo admite items activos de tipo `product`.
- Un producto no puede repetirse en el mismo documento.
- Cantidad y costo unitario deben ser válidos; la cantidad debe ser mayor que cero.
- Un documento no anulado no puede repetirse para el mismo proveedor, tipo y número.
- La recepción no puede superar la cantidad pendiente.
- La recepción y todos sus movimientos se procesan en una sola transacción.
- Una compra con mercadería recibida no puede anularse. La salida física se registra como devolución a proveedor desde Inventario.
- No se actualiza `warehouse_items` directamente; los saldos cambian mediante movimientos de inventario.

## Valorización

El método inicial es promedio ponderado por producto y almacén. Los impuestos se registran en la cabecera, pero el costo de inventario usa el costo unitario del detalle; no utiliza el precio de venta.

## Interfaz

- El listado permite buscar por proveedor, documento o producto y filtrar por estado.
- La recepción muestra únicamente cantidades pendientes.
- El progreso diferencia pendiente, parcial, recibido y anulado.
- La exportación usa los mismos filtros del listado y no aplica paginación.
- Las modales usan backdrop estático y los estilos reutilizables `br-entity-modal` y `br-modal-standard`.

## Estado de mejoras

- Cada compra recibe una referencia interna única `COM-*`, separada del número del proveedor.
- `delivery_mode` queda persistido como `immediate` o `pending`.
- Flete, seguro y otros gastos se distribuyen por valor, cantidad o partes iguales.
- Cada detalle conserva costo original, gasto asignado y `inventory_unit_cost`; las recepciones valorizan inventario con este último.
- La cabecera conserva gasto total, pagado, saldo y estado de pago.
- La aprobación pendiente bloquea recepciones; aprobar no simula una recepción física.

## Estado backend implementado

- La cabecera incorpora vencimiento y estado/aprobador de aprobación.
- `purchase_expenses` registra flete, seguro u otros gastos con criterio de distribución.
- `purchase_returns` y `purchase_return_items` vinculan devoluciones con compra, recepción, almacén y movimiento de inventario.
- La pantalla de estos contratos se mantiene en `docs/UI_UX_PENDING.md`.

## Productos disponibles

- Compras solo lista productos con estado `active`.
- Un producto `inactive` no puede seleccionarse para nuevas órdenes o facturas de compra; primero debe reactivarse desde Catálogo comercial.
- Los almacenes disponibles respetan las sucursales permitidas del colaborador.
