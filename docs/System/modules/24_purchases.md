# 24 - Compras

## Propósito

Registra órdenes y facturas de compra, controla lo pendiente por recibir y alimenta el inventario con el costo real de cada recepción. El documento comercial y la recepción física son eventos distintos: crear una compra no modifica existencias.

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
3. La compra inicia como `confirmed`, pendiente de recepción.
4. Una recepción puede incluir parte o todo lo pendiente.
5. Cada cantidad recibida genera una entrada `purchase` mediante `InventoryMovementService`.
6. El costo recibido recalcula el promedio ponderado del producto en ese almacén.
7. La compra cambia a `partial` o `received` según el avance.

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
- No se actualiza `warehouse_items` directamente.

## Valorización

El método inicial es promedio ponderado por producto y almacén. Los impuestos se registran en la cabecera, pero el costo de inventario usa el costo unitario del detalle; no utiliza el precio de venta.

## Interfaz

- El listado permite buscar por proveedor, documento o producto y filtrar por estado.
- La recepción muestra únicamente cantidades pendientes.
- El progreso diferencia pendiente, parcial, recibido y anulado.
- La exportación usa los mismos filtros del listado y no aplica paginación.
- Las modales usan backdrop estático y los estilos reutilizables `br-entity-modal` y `br-modal-standard`.

## Mejoras sugeridas

- Condiciones de pago, cuentas por pagar y vencimientos.
- Gastos adicionales distribuibles al costo, como flete o seguro.
- Aprobación de órdenes antes de confirmar.
- Devoluciones ligadas directamente a una recepción de compra.
- Numeración interna configurable para órdenes y recepciones.
## Productos disponibles

- Compras solo lista productos con estado `active`.
- Un producto `inactive` no puede seleccionarse para nuevas órdenes o facturas de compra; primero debe reactivarse desde Catálogo comercial.
- Los almacenes disponibles respetan las sucursales permitidas del colaborador.
