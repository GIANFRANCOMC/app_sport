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
- Tablas: `items`, `brands`, `category_items`, `categories`, `warehouses`, `warehouse_items`

## Datos del producto

### Identificación

- `items.company_id`: empresa propietaria.
- `items.brand_id`: marca opcional de la empresa; una marca puede agrupar muchos productos.
- `items.internal_code`: código interno único entre productos de la empresa.
- `items.barcode`: código de barras validado como único entre todos los items de la empresa desde el backend; técnicamente usa formato EAN-13.
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

- `items.see_my_web`: control principal para exponer el producto en el catálogo comercial.
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
- La marca es opcional, debe pertenecer a la empresa y no puede asignarse si está inactiva.
- Una marca inactiva ya asociada puede conservarse durante una edición para no romper datos históricos.
- Código interno, código de barras, nombre, precio, moneda y estado son obligatorios.
- El código interno es único entre productos de la empresa.
- El código de barras es único entre todos los items de la empresa.
- Precio, stock inicial y stock mínimo no pueden ser negativos.
- El precio debe respetar los límites mínimo y máximo configurados.
- Los almacenes enviados deben estar activos y pertenecer a sucursales activas de la empresa autenticada.
- Las categorías deben estar activas y pertenecer a la empresa autenticada.
- La moneda debe existir y estar activa.
- El precio mínimo no puede superar al precio de venta.
- El precio máximo no puede ser menor que el precio de venta ni que el precio mínimo.
- Código interno, código de barras, precios, arreglos y relaciones se vuelven a validar en backend aunque exista validación frontend.
- No se permite repetir un almacén dentro del formulario.
- Si `see_my_web` es falso, `see_my_web_price` se fuerza a falso.
- Crear o editar producto, inventario y categorías ocurre dentro de una transacción.

## Interfaz

- Tabla compacta con producto, identificación, precio, inventario, publicación, estado y acción.
- Código interno y código de barras se muestran como identificadores distintos; el formato EAN-13 se explica únicamente como ayuda técnica en tooltips y documentación.
- El inventario resume cantidad total y almacenes que alcanzaron su mínimo.
- La marca aparece inmediatamente debajo del nombre en una cápsula compacta de azul suave, con icono y nombre. Se diferencia del código interno sin añadir otra columna; el icono muestra el tooltip `Marca`.
- Cuando existe descripción, se conserva una separación adicional después de la marca para que ambos datos puedan leerse como niveles distintos.
- Los iconos de publicación distinguen disponibilidad del producto y visibilidad del precio.
- El formulario se organiza en tres pestañas: Datos y precio, Información comercial e Inventario.
- La primera pestaña agrupa nombre, código interno y código de barras en una fila; precio de venta, precio mínimo y precio máximo en otra.
- El estado se selecciona mediante el selector reutilizable `vue-select`, con las mismas reglas visuales y de interacción que el resto de selectores del sistema.
- El selector de estado ocupa cuatro columnas en escritorio para evitar opciones innecesariamente anchas.
- En resoluciones `lg`, el selector de estado ocupa seis columnas para conservar una proporción cómoda.
- La primera pestaña contiene también Marca inmediatamente antes de Estado, evitando separar datos básicos de clasificación durante el alta.
- La segunda pestaña presenta primero la descripción comercial adicional, luego las categorías y finalmente la publicación, respetando el orden natural de lectura y clasificación.
- Los controles de publicación asumen la existencia del catálogo comercial: `Publicar producto` indica su visibilidad y `Mostrar precio` expone el importe únicamente cuando la publicación está activa.
- Marca y Categorías incluyen una acción contextual `Agregar` junto al label, acompañada por un icono circular de suma para reconocerla con rapidez sin competir visualmente con el campo. Cada acción abre un modal rápido sin cerrar ni limpiar el formulario de Producto.
- Al crear una Marca, el nuevo registro se incorpora al catálogo local y queda seleccionado automáticamente. Al crear una Categoría, se incorpora y añade a la selección múltiple existente.
- Las altas rápidas no vuelven a solicitar todo `initParams`: actualizan de forma reactiva `options.brands.records` u `options.categories.records`; el backend ya invalida `ProductConfigService` para futuras cargas.
- El selector de Marca permite limpiar la relación y muestra únicamente marcas activas.
- Los selectores reutilizan una `X` tipográfica compacta y centrada ópticamente con la flecha para limpiar valores, evitando deformaciones del SVG y manteniendo un área clicable cómoda.
- Las tres pestañas ocupan todo el ancho del modal, tienen separación visual y resaltan claramente la sección activa.
- El modal utiliza el desplazamiento natural de Bootstrap, sin `modal-dialog-scrollable`; la navegación `nav-pills` permanece en el flujo del formulario para impedir que los campos pasen visualmente por detrás.
- Al existir errores, se marca la pestaña afectada y se abre automáticamente la primera que requiere corrección.
- Cada apertura reemplaza completamente los datos del formulario y genera nuevos códigos al crear, evitando mezclar información entre creación y edición.
- El cierre, Cancelar y el evento `hidden.bs.modal` limpian datos, errores y pestaña activa.
- Los campos de stock reutilizan el componente `InputNumber`.
- Las sucursales y almacenes se muestran juntos para evitar ambigüedad.
- En móvil, cada fila de inventario pasa a disposición vertical.
- Los controles y estados usan los tokens `--br-*` del branding.
- Los botones de generación y edición usan iconos con tooltip.

## Integraciones impactadas

- `ProductConfigService` obtiene categorías y almacenes mediante `CompanyReferenceDataService`, y monedas mediante `MasterReferenceDataService`.
- `ProductConfigService` obtiene también las marcas activas mediante `CompanyReferenceDataService::brands()`.
- Gestión de stock usa `warehouse_items.minimum_stock` y deja de comparar contra el valor fijo `5`.
- Al crear un almacén predeterminado, `WarehouseService` genera relaciones en cero para todos los productos existentes.
- El listado de Productos carga `warehouseItems.warehouse.branch` para resumir stock y alertas sin consultas posteriores desde Vue.

## Mejoras aplicadas

- La caché de `initParams` ya no se invalida de forma aislada. Productos declara una mutación del recurso compartido `items`, por lo que también refresca la configuración de Ventas.
- Cuando se crea o modifica una categoría, `InitParamsCacheInvalidationService` elimina la caché de Productos para que el selector muestre inmediatamente las categorías activas de la empresa.
- Cuando se crea o modifica una marca, la dependencia `BRANDS` elimina la caché de Marcas y Productos para actualizar el selector sin esperar el TTL.
- `ProductRequest` extiende `CompanyFormRequest`, normaliza cadenas y usa `BelongsToCompany` tanto para relaciones directas como para almacenes cuya empresa se obtiene mediante sucursal.
- `items.barcode` no declara índices únicos ni índices compuestos adicionales en la migración base. La regla de negocio de unicidad se aplica en backend mediante `UniqueInCompany`.
- La tabla `items`, su relación opcional con `brands` y el menú de Marcas se definen directamente en las migraciones iniciales. Mientras el proyecto permita reiniciar el esquema, no se crean migraciones incrementales para modificar estas tablas existentes.
- `UniqueInCompany` cuenta con pruebas para empresa autenticada, duplicados, exclusión durante edición y filtros adicionales como `type`.

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
- Footer de modal compacto y equilibrado mediante `br-entity-modal__footer`, con padding vertical simétrico y separación prudente entre acciones.
- Footer de modal con superficie gris `#f7f8fa`, configurable mediante `--br-entity-modal-footer-bg`.
- Body de modal sin reserva lateral fija del scrollbar y con padding horizontal interno reducido mediante variables reutilizables de `br-entity-modal`.
- Pestañas con fondo blanco y contenido del formulario en gris suave; las secciones no usan altura mínima fija para evitar scroll innecesario en pestañas cortas.
- Pestañas sin sombra superior y con fondo blanco; las inactivas mantienen borde superior gris y la activa solo cambia el borde superior y el círculo secuencial a secondary navy.
- Switches comerciales con fondo y borde verde suave cuando están activados; el color del checkbox marcado se hereda desde la regla global reutilizable de `br-branding.css`.
- Encabezados numéricos de inventario centrados para mejorar la lectura de stock inicial y stock mínimo.
- Tooltips explican que el código interno es privado para la empresa y que el código de barras identifica la etiqueta visible o escaneable del producto.
- Botones de generación compactos mediante `br-input-action`, integrados al input como una sola unidad: sin divisor interno, mismo borde del campo, foco compartido en el grupo y hover solo en el icono.
- `br-input-action` se adapta automáticamente a la altura real del input para mantener bordes superiores e inferiores nivelados.
- Prefijos de moneda y contadores de caracteres siguen el mismo patrón de control compuesto: sin divisor interno, borde unificado y foco compartido con el input.
- Los prefijos y contadores compactan el padding del borde compartido para evitar separaciones excesivas entre el addon y el valor editable.
- Los contadores de caracteres reutilizan `br-character-counter`, con fondo blanco, tamaño reducido y jerarquía visual equivalente a los símbolos e iconos integrados.
- Los select2 basados en `vue-select` conservan únicamente una flecha de despliegue pequeña, completa en ambos sentidos y sin fondo; su color comunica reposo o apertura sin añadir ruido visual.
- Estado y Categorías renderizan sus opciones mediante `append-to-body`; el menú queda sobre la modal y su footer, sin aumentar el scroll interno del formulario.
- El selector reutilizable de filtros conserva una sola línea aunque la opción sea extensa; muestra el contenido completo al mantener el cursor sobre el texto truncado.
- Los selectores de filtro, Estado y Categorías comparten menú flotante, altura, flecha, colores, elipsis y visualización completa por hover; ninguna opción extensa genera saltos de línea ni scroll horizontal.
- Botón Cancelar con borde gris visible y hover suave mediante `br-btn-cancel`.
- Las ayudas de campo reutilizan `br-field-help`; los controles compartidos residen en `br-branding.css` y no dependen del módulo Productos.
- El campo Estado se mantiene como select2 no buscable para conservar consistencia con los formularios existentes.
- Los prefijos de moneda reutilizan `br-currency-prefix`, con fondo blanco integrado al input, símbolo compacto tipo icono, color secondary suavizado y borde compartido.
- El prefijo monetario usa `br-currency-prefix__symbol` para controlar su escala de forma independiente, con color secondary, peso medio y separación compacta respecto del importe.
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
- Código interno y código de barras usan el componente reutilizable `CopyButton`; por defecto muestra `Copiar` y `Copiado`, y puede activar `useLabelInTooltip` para mostrar textos contextuales como `Copiar código interno` y `Código interno copiado`.
- `StatusBadge` añade automáticamente una clase normalizada desde la BD, como `br-status-active` o `br-status-inactive`, además de la variante semántica existente.
- Las etiquetas de estado son compactas y se perciben como información, no como botones.
- La tabla de Productos se adapta al ancho disponible en escritorio; el ancho mínimo y scroll horizontal se reservan para resoluciones de hasta `991.98px`.
- La barra de filtros no usa divisor inferior para mantener una composición minimalista.
- Venta, Mínimo y Máximo se alinean en un bloque de tres filas; únicamente los rótulos Mínimo y Máximo usan acentos discretos danger/success.
- La alerta de stock usa el texto explícito “N almacén/almacenes con stock bajo” en una etiqueta warning compacta.
- El inventario sin alertas muestra un check verde con fondo suave y el texto singular o plural “Stock saludable en N almacén/almacenes”.
- En la pestaña Inventario, cada almacén muestra una etiqueta sutil: “Stock bajo o en el mínimo” o “Inventario saludable”, usando la misma semántica warning/success del listado.
- La nota general de inventario fue retirada para reducir ruido visual. Los encabezados `Stock inicial` o `Stock actual` y `Stock mínimo` incluyen ayudas contextuales reutilizables mediante `br-field-help`.
- El encabezado de inventario reutiliza `br-table-header-surface`, la misma regla mate aplicada al encabezado del listado, sin degradados alternativos ni bordes inferiores adicionales. `br-label-with-help` centra ópticamente texto e icono.
- Los tooltips de Marca e inventario usan la variante global compacta `br-tooltip`, con fondo secondary y aparición inmediata, sin animación.
- En edición, el stock actual conserva su comportamiento de solo lectura y se presenta mediante un `span` neutral formateado con `separatorNumber`; no se renderiza como un control deshabilitado porque la cantidad se modifica desde Gestión de stock, no desde Productos. En creación se conserva `InputNumber` para registrar el stock inicial.
- Las pestañas reutilizables mantienen una separación visual moderada entre opciones para facilitar el escaneo sin incrementar su altura.
- Las pestañas reutilizan `nav-pills`, tienen una separación superior más amplia respecto al encabezado y márgenes laterales alineados con los campos del formulario.
- La navegación ya no usa posición sticky ni desenfoque: forma parte del flujo normal del modal, evitando superposiciones y filtraciones de texto durante el desplazamiento.
- En escritorio, las pestañas reducen su separación y mantienen las tres etapas visibles; en móvil se presenta únicamente la etapa activa con su título y descripción completos, acompañada por controles anterior/siguiente accesibles y alineados al branding.
- Todas las etapas móviles conservan una altura fija independientemente de la longitud del título o la descripción; admiten hasta dos líneas por texto sin alterar la estructura. Las flechas son controles compactos y sutiles, centrados verticalmente, con énfasis azul únicamente en hover o foco.
- Los márgenes horizontales de la navegación y del contenido comparten la variable `--br-entity-modal-content-space-x`, ampliada para mejorar la respiración del formulario y ajustada de forma responsive.
- La separación superior entre el encabezado del modal y la navegación se controla mediante `--br-entity-modal-body-space-y`, incrementada para mejorar la jerarquía y respiración visual.
- La distancia entre navegación y campos se controla de manera independiente con `--br-entity-modal-tabs-content-space-y`; es más compacta que la separación superior y se reduce adicionalmente en móvil.
- Las pestañas mantienen una separación intermedia de `0.28rem`, suficiente para distinguir cada etapa sin fragmentar visualmente el flujo.
- En pantallas de hasta `767.98px`, `br-entity-modal` elimina el límite intermedio de Bootstrap y usa casi todo el ancho disponible, dejando únicamente `0.375rem` por lado.
- `AddCategory` y `AddBrand` reutilizan `QuickCreateCatalogEntity` y `QuickCreateTrigger`. El disparador admite modos `link`, `button` e `icon`, además de texto, icono, título, clases y estado deshabilitado parametrizables.
- El modal rápido usa `dialog.showModal()`: aparece en la capa superior nativa, mantiene intacta la modal de Producto y devuelve el foco al contexto anterior al cerrarse.
- Los errores de validación se muestran dentro del modal rápido y bajo sus campos, sin SweetAlert ni recargas invasivas.
- `custom.css` utiliza versionado por fecha de modificación en el layout System para evitar que el navegador conserve estilos anteriores durante las mejoras visuales.
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
