# 11 - Productos

## Qué hace

Administra los registros de `items` cuyo `type` es `product`. Centraliza identificación comercial, categorías, precios, publicación y configuración inicial de inventario por almacén.

Aunque productos, servicios y membresías comparten la tabla `items`, este módulo aplica reglas exclusivas de productos: código de barras EAN-13, stock físico y alertas por almacén.

## Archivos

- Ruta: `routes/System/Catalogs/Product.php`
- Controlador: `app/Http/Controllers/System/Catalogs/ProductController.php`
- Servicio principal: `app/Services/System/Catalogs/Products/ProductService.php`
- Configuración: `app/Services/System/Catalogs/Products/ProductConfigService.php`
- Servicio de inventario: `app/Services/System/Warehouses/Warehouses/WarehouseItemService.php`
- Request base: `app/Http/Requests/System/Catalogs/Products/ProductRequest.php`
- Requests HTTP: `StoreProductRequest`, `UpdateProductRequest`
- Regla EAN-13: `app/Rules/System/Catalogs/ValidEan13.php`
- Modelo: `app/Models/System/Catalogs/Item.php`
- Vue: `resources/js/System/Pages/Catalogs/products/main.vue`
- Estilos: `public/System/assets/css/custom.css`, bloque `SYSTEM PRODUCTS`
- Tablas: `items`, `category_items`, `categories`, `warehouses`, `warehouse_items`

## Datos del producto

### Identificación

- `items.company_id`: empresa propietaria.
- `items.internal_code`: código interno único entre productos de la empresa.
- `items.barcode`: código de barras único entre todos los items de la empresa; técnicamente se valida con formato EAN-13.
- `items.name`: nombre comercial.
- `items.description`: descripción breve.
- `items.type`: siempre `product`.
- `items.status`: `active` o `inactive`.

### Precio

- `items.currency_id`: moneda.
- `items.price`: precio de venta.
- `items.min_price`: límite inferior opcional.
- `items.max_price`: límite superior opcional.

### Publicación

- `items.see_my_web`: control principal para exponer el producto en un futuro catálogo web, PDF u otro recurso público.
- `items.see_my_web_price`: permite exponer el precio únicamente cuando `see_my_web` está activo.

El nombre histórico de estas columnas se conserva por compatibilidad. La interfaz utiliza los conceptos “Publicar producto” y “Mostrar precio” para no limitar su uso futuro exclusivamente a una página web.

## Inventario por almacén

Cada producto tiene un registro en `warehouse_items` por cada almacén activo de la empresa:

- `warehouse_id`: almacén específico.
- `item_id`: producto.
- `quantity`: stock actual.
- `minimum_stock`: umbral de alerta del producto en ese almacén.
- `status`: estado de la relación.

El mínimo se guarda por almacén y no de forma general. Una sucursal puede tener distinta demanda, capacidad o frecuencia de reposición, y la estructura también admite múltiples almacenes por sucursal.

Existe una restricción única para `warehouse_id + item_id`, evitando duplicar el inventario de un producto dentro del mismo almacén.

## Flujo de creación

1. Vue carga categorías, monedas, estados y todos los almacenes activos de la empresa.
2. Se generan valores iniciales para código interno y código de barras.
3. El usuario registra stock inicial y stock mínimo por almacén.
4. El backend valida el producto, EAN-13, unicidad, almacenes y cantidades.
5. `ProductService` crea `items` dentro de una transacción.
6. `WarehouseItemService::syncProductInventory()` crea un registro por almacén.
7. En creación, `quantity` toma el stock inicial indicado.
8. Se sincronizan las categorías.

Si un almacén activo no tiene valores explícitos, se crea con cantidad y mínimo en cero.

## Flujo de edición

- Se pueden actualizar datos comerciales, código de barras, publicación, estado y stock mínimo.
- El stock actual se muestra como solo lectura.
- La cantidad existente no se modifica desde Productos; debe cambiarse desde Gestión de stock para no mezclar catálogo con operaciones de inventario.
- Si aparece un almacén nuevo, se crea automáticamente la relación faltante con cantidad cero.

## Código de barras

- Formato requerido: EAN-13.
- Debe contener trece dígitos y un dígito de control válido.
- Es único por empresa, sin limitar la validación al tipo `product`.
- Puede escribirse o leerse con escáner.
- El botón con icono de código de barras genera un EAN-13 con prefijo interno `200`, evitando competir con rangos comerciales GS1.
- La generación frontend mejora la experiencia, pero el backend siempre vuelve a validar formato y unicidad.

## Reglas de negocio

- El producto siempre se guarda con `type = product`.
- Código interno, código de barras, nombre, precio, moneda y estado son obligatorios.
- El código interno es único entre productos de la empresa.
- El código de barras es único entre todos los items de la empresa.
- Precio, stock inicial y stock mínimo no pueden ser negativos.
- El precio debe respetar los límites mínimo y máximo configurados.
- Los almacenes enviados deben estar activos y pertenecer a sucursales activas de la empresa autenticada.
- No se permite repetir un almacén dentro del formulario.
- Si `see_my_web` es falso, `see_my_web_price` se fuerza a falso.
- Crear o editar producto, inventario y categorías ocurre dentro de una transacción.

## Interfaz

- Tabla compacta con producto, identificación, precio, inventario, publicación, estado y acción.
- Código interno y código de barras se muestran como identificadores distintos; el formato EAN-13 se explica únicamente como ayuda técnica en tooltips y documentación.
- El inventario resume cantidad total y almacenes que alcanzaron su mínimo.
- Los iconos de publicación distinguen disponibilidad del producto y visibilidad del precio.
- El formulario se organiza en tres pestañas: Datos y precio, Configuración comercial e Inventario.
- La primera pestaña agrupa nombre, código interno y código de barras en una fila; precio de venta, precio mínimo y precio máximo en otra.
- El estado se selecciona mediante un grupo segmentado de radios que muestra todas las alternativas sin abrir un desplegable.
- El selector de estado ocupa cuatro columnas en escritorio para evitar opciones innecesariamente anchas.
- En resoluciones `lg`, el selector de estado ocupa seis columnas para conservar una proporción cómoda.
- La segunda pestaña contiene información comercial complementaria: categorías, descripción y publicación.
- Las tres pestañas ocupan todo el ancho del modal, tienen separación visual y resaltan claramente la sección activa.
- La barra de pestañas permanece fija mientras se desplaza únicamente el contenido del formulario.
- Al existir errores, se marca la pestaña afectada y se abre automáticamente la primera que requiere corrección.
- Cada apertura reemplaza completamente los datos del formulario y genera nuevos códigos al crear, evitando mezclar información entre creación y edición.
- El cierre, Cancelar y el evento `hidden.bs.modal` limpian datos, errores y pestaña activa.
- Los campos de stock reutilizan el componente `InputNumber`.
- Las sucursales y almacenes se muestran juntos para evitar ambigüedad.
- En móvil, cada fila de inventario pasa a disposición vertical.
- Los controles y estados usan los tokens `--br-*` del branding.
- Los botones de generación y edición usan iconos con tooltip.

## Integraciones impactadas

- Gestión de stock usa `warehouse_items.minimum_stock` y deja de comparar contra el valor fijo `5`.
- Al crear un almacén predeterminado, `WarehouseService` genera relaciones en cero para todos los productos existentes.
- El listado de Productos carga `warehouseItems.warehouse.branch` para resumir stock y alertas sin consultas posteriores desde Vue.

## Mejoras aplicadas

- Código de barras generado, editable y validado con formato EAN-13.
- Unicidad de código de barras por empresa.
- Stock inicial por almacén durante la creación.
- Stock mínimo por almacén.
- Compatibilidad con múltiples almacenes por sucursal.
- Publicación y visibilidad de precio habilitadas en la interfaz.
- Request base reutilizable para creación y actualización.
- Validación de pertenencia de almacenes a la empresa.
- Sincronización transaccional de inventario.
- Alertas de stock basadas en configuración real.
- UI responsive y alineada al branding.
- Cierre con una `X` simple y sin borde mediante la clase reutilizable `br-modal-close`.
- Tooltips explican que el código interno es privado para la empresa y que el código de barras identifica la etiqueta visible o escaneable del producto.
- Botones de generación compactos mediante `br-input-action`, con iconografía y contraste azul suave del branding.
- Botón Cancelar con borde gris visible y hover suave mediante `br-btn-cancel`.
- Las ayudas de campo reutilizan `br-field-help`; los controles compartidos residen en `br-branding.css` y no dependen del módulo Productos.
- Los selectores binarios o de pocas alternativas pueden reutilizar `br-choice-group` y `br-choice-option`.
- Los prefijos de moneda reutilizan `br-currency-prefix`, con contraste suave basado en los tokens primarios de marca.
- El prefijo monetario usa una superficie neutral y tipografía compacta para diferenciar información contextual de acciones azules como generar códigos.
- El CTA utiliza “Agregar producto” o “Editar producto”; durante el proceso muestra “Agregando” o “Editando” sin puntos suspensivos.
- El CTA principal no usa un icono fijo, porque su acción cambia entre agregar y editar.
- Las acciones reutilizan variantes semánticas: `br-btn-action-search`, `br-btn-action-open-create`, `br-btn-action-create`, `br-btn-action-update` y `br-icon-action-edit`.
- Buscar usa celeste informativo, agregar usa el azul primario de marca y editar usa el secundario navy; apertura y confirmación conservan el color correspondiente a su intención.
- Abrir el alta usa `br-btn-action-open-create` y confirmar el alta usa `br-btn-action-create`, ambos con azul primario.
- Abrir y confirmar una edición usan `br-icon-action-edit` y `br-btn-action-update` respectivamente, ambos con el secundario navy.
- Agregar y editar se distinguen entre sí por color; apertura y confirmación conservan la semántica cromática de su acción.
- `FiltersSection` usa la barra reutilizable `br-filter-bar`: controles compactos, etiquetas discretas y acciones alineadas al final.
- Todos los `vue-select` de System usan un indicador compacto con superficie neutral; al desplegar rota y adopta el azul de marca.
- Las acciones de la barra se alinean al inicio para evitar espacios muertos después del campo de búsqueda.
- La tabla usa `table-layout: fixed` y un `colgroup` con proporciones estables; Precio gana espacio y Producto se compacta para separar mejor los importes de Identificación.
- Identificación diferencia visualmente “Código interno” y “Código de barras” en filas etiquetadas.
- `StatusBadge` añade automáticamente una clase normalizada desde la BD, como `br-status-active` o `br-status-inactive`, además de la variante semántica existente.
- Las etiquetas de estado son compactas y se perciben como información, no como botones.
- La tabla de Productos se adapta al ancho disponible en escritorio; el ancho mínimo y scroll horizontal se reservan para resoluciones de hasta `991.98px`.
- La barra de filtros no usa divisor inferior para mantener una composición minimalista.
- Venta, Mínimo y Máximo se alinean en un bloque de tres filas; únicamente los rótulos Mínimo y Máximo usan acentos discretos danger/success.
- La alerta de stock usa el texto explícito “N almacén/almacenes con stock bajo” en una etiqueta warning compacta.
- El inventario sin alertas muestra un check verde con fondo suave y el texto singular o plural “Stock saludable en N almacén/almacenes”.
- En la pestaña Inventario, cada almacén muestra una etiqueta sutil: “Stock bajo o en el mínimo” o “Inventario saludable”, usando la misma semántica warning/success del listado.
- En edición, el stock actual se muestra como lectura con `separatorNumber`; la cantidad se modifica desde Gestión de stock, no desde Productos.
- Las pestañas sticky cubren completamente la unión con el encabezado para impedir que el contenido desplazado se vea por detrás, y se compactaron en altura para reducir espacio ocupado por navegación.
- El breadcrumb global es compacto, se alinea a la derecha y resalta únicamente la ubicación actual con el azul de marca.
- El listado omite la columna Publicación; esa configuración se consulta y modifica dentro del formulario.
- Los generadores ocultan únicamente su propio tooltip mediante `Alerts.dismissTooltip()`; no destruyen las instancias de otros controles.
- “Descripción comercial adicional” se conserva en etiqueta, filtros, validaciones frontend y atributos de error del backend.
- Las respuestas del backend mantienen el mismo lenguaje: “Producto agregado exitosamente” y “Producto editado exitosamente”.
- Los estilos comunes del módulo se exponen mediante el namespace reutilizable `br-entity-*`; no se mantienen selectores `br-products-*`.

## Mejoras pendientes

- Crear una tabla de movimientos de inventario con motivo, usuario, origen y saldo resultante.
- Definir si se bloquearán ventas con stock insuficiente o se permitirá stock negativo.
- Incorporar impresión de etiquetas con código de barras.
- Añadir lector de código de barras al flujo de ventas.
- Agregar pruebas de integración para creación multi-almacén, EAN duplicado y aislamiento entre empresas.
- Diseñar el catálogo web/PDF que consumirá `see_my_web` y `see_my_web_price`.
