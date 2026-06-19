# 04 - Ventas / Nuevo

## Que hace

Permite crear una venta con productos, servicios o membresias. Una venta puede generar efectos secundarios: descuento de stock y creacion de membresias reales.

## Archivos

- Ruta: `routes/System/Sales/Sale.php`
- Controlador: `SaleController`
- Request: `StoreSaleRequest`
- Servicio: `SaleService`
- Vista: `resources/views/System/general/Sales/sales/main.blade.php`
- Vue: `resources/js/System/Pages/Sales/sales/main.vue`
- Tablas: `sales_header`, `sales_body`, `items`, `warehouse_items`, `warehouses`, `subscriptions`

## Reglas

- Debe existir almacen para la sucursal.
- El correlativo se genera por serie.
- El total se calcula desde detalles.
- Si el detalle es `product`, se descuenta stock.
- Si el detalle es `subscription`, se crea membresia real para el cliente.
- Todo se ejecuta dentro de transaccion.
- `SaleConfigService` obtiene sucursales, clientes e ítems mediante `CompanyReferenceDataService`.
- La creación de una venta no elimina la caché de configuración: los registros operativos se consultan por endpoints separados.

## Campos necesarios

- `branch_id`
- `serie_id`
- `holder_id`
- `currency_id`
- `issue_date`
- `details[]`
- Por detalle: `item_id`, `currency_id`, `name`, `quantity`, `price`, `type`, `extras`

## Mejoras sugeridas

- Evitar stock negativo si la empresa no lo permite.
- La anulación aplica `company_settings.inventory.restore_stock_on_sale_cancellation`; por defecto no repone productos automáticamente.
- Validar que `serie_id` pertenezca a la sucursal seleccionada.
- Proteger correlativo contra concurrencia.
- Tipar `extras` de membresia con estructura clara.

## Actualizacion: impuestos y pagos configurables

- La venta ahora aplica automaticamente todos los impuestos activos desde `taxes`, filtrados por alcance `sale` o `both`.
- La venta ahora puede recibir multiples metodos de pago desde `payment_methods`, filtrados por alcance `sale` o `both`, indicando el monto pagado por cada metodo.
- El backend recalcula subtotal, impuestos, total y pagos para mantener la consistencia del documento.
- Los impuestos aplicados se guardan como foto del documento en `sale_taxes`.
- Los pagos aplicados se guardan como foto del documento en `sale_payments`.
- La vista `resources/js/System/Pages/Sales/sales/main.vue` muestra un bloque lateral de liquidacion con impuestos aplicados automaticamente, metodos de pago, subtotal, impuestos, total, pagado y diferencia.
- Si solo hay un metodo de pago, el importe se sincroniza con el total para facilitar el registro.
- Pendiente: crear pantalla administrativa para impuestos y metodos de pago por empresa.

## Actualizacion: IGV incluido, almacen de venta y caja

- `items.price_includes_tax` define si el precio de venta del producto, servicio o membresia ya contiene IGV.
- La venta envia `details.*.price_includes_tax` y guarda la foto del valor en `sales_body.price_includes_tax`.
- Si el precio incluye IGV, los impuestos configurados para venta no incrementan el total de ese detalle.
- Si el precio no incluye IGV, todos los impuestos activos de alcance `sale` o `both` se calculan sobre ese detalle y aumentan el total.
- `sales_header.warehouse_id` guarda el almacen afectado por la venta.
- El frontend muestra el selector de almacen junto a Sucursal y Tipo de comprobante. Si la sucursal solo tiene un almacen, lo selecciona automaticamente.
- `SaleService::resolveWarehouse()` valida que el almacen pertenezca a la sucursal y a la empresa autenticada. Si hay varios almacenes y no se envia uno, el backend rechaza la venta con un mensaje accionable.
- `sales_header.cash_session_id` permite vincular la venta con una caja abierta cuando el modulo de caja este activo.
- Si la venta incluye `cash_session_id`, cada pago genera un registro en `cash_movements`, manteniendo trazabilidad por metodo de pago para apertura, cierre, arqueo y resumen de caja.
- Los metodos de pago iniciales incluyen `Efectivo`, `Tarjeta`, `Transferencia`, `Billetera digital`, `Yape` y `Plin`; todos siguen siendo configurables por empresa y por alcance (`sale`, `purchase`, `both`).
# Venta POS

- `sales.pos` se muestra bajo la cabecera `Operacion`, junto con `Cajas`, para agrupar el trabajo de mostrador debajo de Dashboard y reducir pasos operativos.
- `sales.pos` usa una vista propia tipo mostrador: categorias superiores, buscador de productos, cards de productos y ticket lateral.
- Las categorias son chips compactos sin iconos, con fondo solido anaranjado suave y contador lateral para mostrar disponibilidad sin ocupar otra linea.
- Las cards de productos, servicios y membresias no agregan al tocar el contenido; solo agregan mediante el boton `+`.
- Cada card diferencia marca, codigo interno y codigo de barras en lineas separadas para mejorar lectura en pantallas reducidas.
- Cada card muestra una accion de detalle para abrir una modal con nombre, tipo, descripcion, marca, categorias, codigos, precio y configuracion de IGV cuando el espacio del card no alcance.
- Si no existen cajas abiertas, POS muestra una alerta roja dentro del panel de detalle y oculta cliente, pagos, limpiar venta y generar venta.
- Si existe una sola caja abierta, POS muestra caja y sucursal como texto de contexto; si existen varias, permite seleccionar la caja.
- El buscador se ubica debajo de categorias para mantener primero el contexto visual de navegacion.
- El panel derecho sigue el orden operativo: caja/sucursal, detalle de subtotal e impuestos, total y detalle de venta. Cliente y pagos se revisan en la modal de confirmacion.
- La caja activa se muestra como contexto superior del ticket con fondo anaranjado para que el cajero identifique rapidamente caja y sucursal.
- La pantalla POS reutiliza `sales.store` para generar la venta, por lo que conserva validaciones y trazabilidad del modulo de ventas.
- El ticket lateral prioriza total, items agregados, impuestos y detalle de productos.
- La caja abierta es el selector principal del POS y muestra a que sucursal pertenece. La sucursal se deriva desde la caja.
- El almacen solo se muestra cuando la sucursal tiene mas de un almacen disponible; si solo existe uno, se selecciona automaticamente.
- Los metodos de pago son multiples. Por defecto se agrega efectivo por el total de la venta; el editor vive en la modal de confirmacion y se muestra con `Cambiar metodo de pago`.
- Cuando el editor de pagos esta abierto se muestran solo inputs editables; cuando se oculta, se muestra solo el resumen por metodo de pago.
- El selector de metodo de pago es buscable para agilizar caja cuando hay varios metodos configurados.
- La venta se confirma en dos pasos: el boton `Revisar venta` abre una modal con subtotal, impuestos, total y metodos de pago editables; luego `Confirmar venta` registra la venta.
- POS permite agregar un cliente desde el campo Cliente reutilizando `AddCustomer`; al crear el cliente se agrega a la lista y queda disponible para seleccion.
- El boton `Generar venta` se oculta hasta que la caja, cliente, pagos y detalle esten completos, evitando una accion visualmente disponible cuando todavia falta informacion.
- Al abrir o cerrar una caja, `CashRegisterService` invalida la cache de `CashRegisterConfigService` y `SaleConfigService` para que POS muestre solo cajas abiertas vigentes al volver a ingresar.
- Los impuestos configurados para ventas se aplican automaticamente sobre productos cuyo precio no incluye IGV (`price_includes_tax = false`).
- Si existe una sesion de caja abierta, POS puede asociar la venta a `cash_session_id` para alimentar movimientos de caja.
## Venta POS - pagos y confirmación

- La vista principal del POS se enfoca en seleccionar catálogo, caja y revisar el detalle de la venta.
- Los métodos de pago se revisan y editan únicamente en la modal **Confirmar venta**. Por defecto se muestra un resumen legible; si el pago será mixto, el usuario puede usar **Cambiar método de pago** y ajustar importes antes de confirmar.
- El cliente también se selecciona dentro de **Confirmar venta**, junto al resumen y los pagos, para que la pantalla principal quede enfocada en armar el detalle.
- El botón **Revisar venta** queda visible cuando hay caja abierta y solo se bloquea si el detalle está vacío.
- El botón **Confirmar venta** dentro de la modal se mantiene visible y solo se bloquea si el detalle está vacío; las validaciones de cliente, caja y pagos se comunican al confirmar.
- El botón principal de la vista se mantiene como **Revisar venta** para reforzar que la venta se confirma en un segundo paso.
- Las cajas disponibles se limitan por las sucursales permitidas del colaborador. Si el usuario no tiene sucursales configuradas, mantiene acceso a todas las sucursales de la empresa.
- Los ítems con estado `inactive` no se cargan para Venta POS ni para el flujo transaccional de ventas.
