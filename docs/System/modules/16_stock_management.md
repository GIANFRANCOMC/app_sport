# 16 - Inventario

## Propósito

Controla las existencias de productos por almacén y conserva la trazabilidad de cada variación. El nombre visible cambia de **Gestión de stock** a **Inventario**; el identificador técnico `stocks_management` se mantiene para no romper rutas, permisos ni favoritos existentes.

El módulo separa dos conceptos:

- `warehouse_items` conserva el saldo actual y el stock mínimo.
- `inventory_movements` conserva el kardex inmutable: saldo anterior, variación, saldo resultante, motivo, origen y usuario.

## Archivos principales

- Ruta: `routes/System/Warehouses/StockManagement.php`
- Controlador: `StockManagementController`
- Configuración: `StockManagementConfigService`
- Consulta y compatibilidad: `StockManagementService`
- Núcleo transaccional: `InventoryMovementService`
- Modelo de kardex: `InventoryMovement`
- Vista: `resources/js/System/Pages/Warehouses/stocks_management/main.vue`
- Estilos reutilizables: bloque `br-inventory` de `public/System/assets/css/custom.css`

## Tipos de movimiento

### Entrada

Suma una cantidad positiva al saldo. Orígenes implementados:

- `product_opening`: stock inicial al crear un producto.
- `manual`: entrada registrada desde Inventario.
- `sale_cancellation`: reposición automática al anular una venta.

Origen preparado: `purchase`, para recepciones del futuro módulo Compras.

### Salida

Resta una cantidad positiva del saldo. Orígenes implementados:

- `sale`: salida automática al vender un producto.
- `manual`: salida justificada desde Inventario.

Origen preparado: `purchase_cancellation`, para revertir una recepción.

### Corrección

El usuario registra el saldo físico contado. El backend calcula la diferencia contra el saldo actual y guarda esa variación. No debe usarse una corrección para representar una compra, venta o traslado.

## Reglas de negocio

- Todo cambio de `warehouse_items.quantity` debe pasar por `InventoryMovementService`.
- Producto y almacén deben pertenecer a la misma empresa.
- Solo los items de tipo `product` generan movimientos.
- Entradas y salidas requieren una cantidad mayor que cero.
- Correcciones requieren el saldo físico resultante.
- Los movimientos manuales exigen motivo.
- Los movimientos son inmutables; un error se compensa con otro movimiento.
- Una venta genera una salida por cada detalle de tipo producto.
- La anulación de una venta repone las cantidades mediante entradas nuevas; no elimina la salida original.
- El stock inicial de Productos se registra como entrada `product_opening`.
- Editar precio, descripción, marca, categorías o stock mínimo no genera kardex.
- Las salidas manuales no permiten saldo negativo.
- Ventas conservan temporalmente el comportamiento histórico que permite saldo negativo. Esta regla debe pasar a `company_settings` cuando exista la configuración correspondiente.

## Interfaz

- **Existencias** muestra saldo actual, stock mínimo y situación por producto.
- **Registrar movimiento** abre un formulario contextual del producto seleccionado.
- **Kardex** muestra fecha, usuario, producto, tipo, variación, saldo anterior/resultante, motivo y origen.
- El almacén seleccionado limita tanto existencias como kardex.

## Integración

### Productos

- Cada almacén se inicializa con saldo cero.
- Si se informó stock inicial, se genera una entrada por almacén.
- En edición solo puede cambiarse `minimum_stock`; la existencia se modifica desde Inventario.

### Ventas

- Cada producto vendido genera una salida con referencia al detalle de venta.
- Anular una venta genera la entrada inversa y conserva ambos eventos.

### Compras

La arquitectura reconoce `purchase` y `purchase_cancellation`, pero no se conectan hasta implementar Compras. Ese módulo deberá invocar `InventoryMovementService`; no debe actualizar `warehouse_items` directamente.

## Pendientes

- Traslados entre almacenes mediante dos movimientos enlazados.
- Configuración por empresa para permitir o bloquear ventas con saldo negativo.
- Exportación del kardex a Excel.
- Alertas y notificaciones automáticas al alcanzar stock mínimo.
- Costo unitario y valorización cuando se defina el método contable.
