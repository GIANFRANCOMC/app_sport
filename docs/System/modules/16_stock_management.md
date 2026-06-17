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
- `replenishment`: reposición operativa de existencias.
- `customer_return`: devolución física recibida de un cliente.

- `purchase`: recepción parcial o total de una compra.

### Salida

Resta una cantidad positiva del saldo. Orígenes implementados:

- `sale`: salida automática al vender un producto.
- `manual`: salida justificada desde Inventario.
- `supplier_return`: devolución física enviada a un proveedor.

Origen preparado: `purchase_cancellation`, para revertir una recepción.

### Corrección

El usuario registra el saldo físico contado. El backend calcula la diferencia contra el saldo actual y guarda esa variación. No debe usarse una corrección para representar una compra, venta o traslado.

La toma física usa el origen `physical_count`, separado de entradas y salidas manuales.

## Reglas de negocio

- Todo cambio de `warehouse_items.quantity` debe pasar por `InventoryMovementService`.
- Producto y almacén deben pertenecer a la misma empresa.
- Solo los items de tipo `product` generan movimientos.
- Entradas y salidas requieren una cantidad mayor que cero.
- Correcciones requieren el saldo físico resultante.
- Los movimientos manuales exigen motivo.
- Los movimientos son inmutables; un error se compensa con otro movimiento.
- Una venta genera una salida por cada detalle de tipo producto.
- La anulación consulta `company_settings.inventory.restore_stock_on_sale_cancellation`.
- La política es `false` por defecto: anular no modifica stock y una devolución recibida debe registrarse como `customer_return`.
- Con la política activa, la anulación repone cantidades mediante entradas `sale_cancellation`; nunca elimina la salida original.
- El stock inicial de Productos se registra como entrada `product_opening`.
- Editar precio, descripción, marca, categorías o stock mínimo no genera kardex.
- Las salidas manuales no permiten saldo negativo.
- Ventas conservan temporalmente el comportamiento histórico que permite saldo negativo. Esta regla debe pasar a `company_settings` cuando exista la configuración correspondiente.

## Interfaz

- El selector de **Almacén de trabajo** se presenta en una barra compacta e independiente; define el contexto de todas las pestañas sin ocupar una cabecera descriptiva adicional.
- Las pestañas muestran título y descripción breve para explicar la tarea antes de ingresar:
  - **Control de stock**: existencias actuales, mínimos y alertas.
  - **Kardex**: historial completo y trazabilidad.
  - **Traslados**: movimientos multiproducto entre almacenes.
  - **Kardex valorizado**: costo unitario, valor del movimiento y valor resultante por almacén.
- Las pestañas reutilizan la estructura visual de Productos: título, descripción breve y estado activo discreto, sin tarjeta exterior.
- No se repite un encabezado dentro de cada pestaña. El título y la descripción viven únicamente en la navegación.
- La barra de búsqueda y acciones reutiliza la estructura `br-filter-bar` usada por Productos: mismas alturas, etiquetas, espaciados y botones compactos.
- Las tablas usan una sola superficie delimitada, sin contenedores exteriores adicionales.
- **Control de stock** muestra saldo actual, stock mínimo y situación por producto.
- **Registrar operación** usa el color secundario de marca y abre un formulario para uno o hasta 100 productos. Incluye entrada, salida, toma física, reposición, devolución de cliente y devolución a proveedor.
- **Kardex** muestra fecha, usuario, producto, tipo, variación, saldo anterior/resultante, motivo y origen.
- **Traslados** mueve uno o varios productos entre almacenes de la misma empresa mediante salidas y entradas atómicas.
- **Kardex valorizado** reutiliza filtros, búsqueda y paginación del Kardex operativo.
- El almacén seleccionado limita tanto existencias como kardex.
- Los selectores muestran sucursal y almacén porque una sucursal puede administrar varios almacenes.

### Búsqueda y lectura de códigos

- Todas las pestañas comparten un buscador por nombre, código interno o código de barras.
- El buscador reutiliza `InputText`; el icono de código de barras forma parte del grupo del campo y comparte borde y foco.
- El campo admite lectores físicos que escriben el código y envían `Enter`; no exige que el usuario conozca una sintaxis especial.
- En **Control de stock**, una coincidencia exacta filtra y resalta el producto.
- En **Kardex**, una coincidencia exacta consulta directamente su trazabilidad.
- En **Traslados**, una coincidencia exacta agrega el producto a la operación. Si ya está incluido, se informa sin duplicarlo.
- Al abrir **Registrar operación** después de escanear una coincidencia exacta, el producto queda preseleccionado.
- Las coincidencias parciales siguen funcionando como búsqueda convencional.

## Operaciones multiproducto

- Una operación manual admite entre 1 y 100 productos sin duplicados.
- Tipo de operación, origen, almacén y motivo son comunes al lote.
- Cada producto conserva su propia cantidad o saldo contado.
- El backend valida todos los registros y los procesa dentro de una sola transacción.
- Si cualquier producto falla, no se registra ningún movimiento del lote.
- La operación continúa generando un registro independiente en `inventory_movements` por producto; no se pierde granularidad ni trazabilidad.

## Exportación

- Todas las pestañas permiten descargar un Excel con los filtros visibles.
- **Control de stock** exporta código interno, código de barras, producto, stock actual, stock mínimo y situación.
- **Kardex** exporta fecha, almacén, identificación, producto, movimiento, origen, saldos, motivo, referencia y responsable.
- **Traslados** exporta los movimientos `transfer_in` y `transfer_out` del almacén seleccionado.
- La exportación usa las mismas consultas de la pantalla sin paginación, evitando diferencias entre listado y reporte.
- Códigos internos y códigos de barras se escriben como texto para impedir notación científica o pérdida de ceros.
- Las existencias bajo el mínimo se resaltan suavemente en el Excel.
- En escritorio, Descargar se muestra como icono con tooltip y se mantiene al extremo derecho. En móvil muestra icono y texto.

### Kardex valorizado

- Usa el método `weighted_average`, configurado por empresa en `company_settings`.
- `warehouse_items` materializa `average_cost` e `inventory_value`.
- Cada movimiento conserva `unit_cost`, `value_before`, `value_change` y `value_after`.
- Las entradas de compra usan el costo unitario recibido.
- Las salidas usan el costo promedio vigente del almacén.
- Los traslados conservan el costo del almacén de origen para la entrada del destino.
- Las entradas manuales y reposiciones aceptan costo unitario opcional; si se omite, conservan el promedio actual.
- Nunca usa el precio de venta como costo.
- La pestaña y su Excel están activas y usan los mismos filtros del Kardex.

## Traslados entre almacenes

- El almacén seleccionado en la cabecera funciona como origen.
- El destino debe ser distinto y pertenecer a la misma empresa.
- Cada operación admite entre 1 y 100 productos sin duplicados.
- Cada producto debe estar activo y ser de tipo `product`.
- Cada cantidad debe ser mayor que cero y no puede superar el saldo disponible del producto en el origen.
- El motivo es obligatorio.
- El traslado usa una referencia común y genera, por cada producto, dos movimientos:
  - `transfer_out`: salida del almacén de origen.
  - `transfer_in`: entrada al almacén de destino.
- Todos los productos se procesan dentro de una sola transacción. Si falla cualquier movimiento, ningún saldo del traslado se modifica.
- El traslado no altera el stock mínimo configurado en ninguno de los almacenes.
- La interfaz permite agregar y quitar filas, evita seleccionar el mismo producto dos veces y conserva errores asociados a cada fila.

## Modales y alertas

- Las modales System aplican globalmente backdrop estático y teclado deshabilitado. El usuario solo puede cerrarlas mediante la `X`, el botón de cierre del footer o una acción programática posterior a un resultado válido.
- El preparador interno de `Alerts.js` agrega la estructura reutilizable `br-modal-standard`; los bodies sin navegación por pestañas reciben espaciado horizontal uniforme mediante `br-modal-standard__body`.
- La modal Registrar movimiento reutiliza el mismo header, body y footer de `br-entity-modal`, sin crear medidas locales.
- Los SweetAlert compactos conservan el color semántico en borde e icono. Sus acciones usan primary para error/success, danger para warning y secondary para question.
- El kardex puede cargar relaciones parciales de sucursal sin exigir `status`; el accessor de `Branch` tolera atributos no seleccionados.
- La relación de usuario del kardex solicita únicamente `id` y `name`. Los accessors de `User` toleran que `gender` o `status` no hayan sido seleccionados y `formatted_preferences` no ejecuta lazy loading: solo transforma preferencias cuando la relación fue cargada explícitamente.
- `Branch`, `Warehouse`, `Item` y `User`, modelos relacionados con el kardex, soportan selecciones parciales sin acceder directamente a claves ausentes.

## Integración

### Productos

- Cada almacén se inicializa con saldo cero.
- Si se informó stock inicial, se genera una entrada por almacén.
- En edición solo puede cambiarse `minimum_stock`; la existencia se modifica desde Inventario.

### Ventas

- Cada producto vendido genera una salida con referencia al detalle de venta.
- Anular una venta solo genera la entrada inversa cuando la política empresarial está activa.
- Con la política desactivada, la respuesta de Ventas recuerda registrar la devolución física cuando corresponda.
- La reposición automática busca la salida original de cada detalle y devuelve el producto al mismo almacén. Las ventas históricas sin movimiento trazable usan el almacén principal como compatibilidad.
- La ruta frontend `stocks_management.movements` está declarada como ruta especial. Sin este mapeo el movimiento existía en base de datos, pero el Kardex no podía consultarlo.

### Compras

El módulo Compras está conectado. Crear el documento no cambia existencias; cada recepción genera entradas `purchase` con costo unitario y actualiza el promedio ponderado. Una compra con recepción no se anula: la mercadería que sale se registra como `supplier_return`.

## Pendientes

- Configuración por empresa para permitir o bloquear ventas con saldo negativo.
- Alertas y notificaciones automáticas al alcanzar stock mínimo.
- Configuración visual del método de valorización; actualmente se administra por base de datos.

## Evolución del módulo

La navegación funcional evita nombres duplicados:

- **Control de stock / Existencias / Reporte Existencias** comparten el saldo materializado de `warehouse_items`.
- **Kardex / Reporte Kardex / Movimientos** comparten `inventory_movements`, con filtros y distintas estrategias de presentación o exportación.
- **Traslados / Transferencia de almacenes** son una sola operación multiproducto.
- **Toma física** es una corrección con origen `physical_count`.
- **Reposiciones y devoluciones** usan `replenishment`, `customer_return` y `supplier_return`.

Requieren módulos documentales independientes y permanecen pendientes:

- Guías de entrada y salida, con numeración, estado y detalle.
- App de almaceneros, con permisos y experiencia móvil propia.
- Reportes consolidados de inventario y stock mínimo/máximo entre varios almacenes.
## Actualizacion: ventas por almacen y caja

- Las ventas ahora guardan `sales_header.warehouse_id`, de modo que el descuento de stock ocurre en el almacen seleccionado por el usuario y no en el primero disponible.
- Si una sucursal tiene varios almacenes, el backend exige indicar el almacen afectado para evitar salidas ambiguas.
- Los movimientos por venta siguen registrandose en `inventory_movements` con origen `sale`, usando el almacen de la cabecera de venta.
- La caja se modela por separado con `cash_registers`, `cash_sessions`, `cash_session_payments` y `cash_movements`.
- Cuando una venta se asocia a `cash_session_id`, sus pagos generan movimientos de caja por metodo de pago. Esto prepara apertura, cierre, arqueo y resumen por metodo sin mezclar trazabilidad financiera con Kardex fisico.
