# 14 - Categorías

## Qué hace

Organiza productos, servicios y membresías.

## Archivos

- Ruta: `routes/System/Catalogs/Category.php`
- Controlador: `CategoryController`
- Servicios: `CategoryService`, `CategoryConfigService`, `CategoryItemService`
- Tablas: `categories`, `category_items`, `items`

## Campos necesarios

- `company_id`
- `internal_code`
- `name`
- `description`
- `status`

## Reglas

- Pertenece a una empresa.
- Puede asociarse con múltiples items.
- Solo las categorías activas se muestran en los formularios de catálogo.
- Después de crear o editar una categoría, `InitParamsCacheInvalidationService` invalida por empresa las cachés de Categorías, Productos, Servicios y Membresías.
- La invalidación ocurre después de que la operación termina correctamente y no ejecuta una limpieza global de la aplicación.

## Dependencias de `initParams`

- `CategoryConfigService`: estados del propio módulo.
- `ProductConfigService`: selector de categorías de Productos.
- `ServiceConfigService`: selector de categorías de Servicios.
- `SubscriptionConfigService`: selector de categorías de Membresías.

## Alta rápida reutilizable

- `resources/js/System/Components/Catalogs/AddCategory.vue` expone el flujo de creación de Categoría para cualquier formulario que necesite ampliar el catálogo sin abandonar su tarea.
- El componente reutiliza `QuickCreateCatalogEntity.vue`, conserva los límites del backend y crea el registro con estado `active`.
- El disparador se configura mediante `triggerMode`, `triggerText`, `triggerTitle`, `triggerIcon` y `triggerClass`; puede representarse como enlace, botón con texto o control solo con icono.
- El formulario rápido solicita Nombre y Descripción. `internal_code` se oculta y se genera automáticamente con el prefijo configurado en `company_settings`; `CAT-` es el valor predeterminado.
- La creación contextual utiliza una modal Bootstrap reutilizable y un SweetAlert de carga por encima de toda la interfaz.
- Nombre y Descripción aceptan Enter para registrar. Un SweetAlert success confirma que la categoría quedó activa y disponible.
- Durante el registro se muestra el loader global para impedir otras acciones; los errores mantienen el modal abierto y se presentan bajo el campo correspondiente.
- Emite `created` con `{record, response}` y `postAction` con la respuesta completa. Productos utiliza `created` para actualizar sus opciones sin seleccionar automáticamente la nueva categoría.
- El modal se implementa con `dialog.showModal()` y se teletransporta a `body`, evitando conflictos al abrirse desde otra modal.
- Los errores frontend y HTTP `422` permanecen dentro del formulario rápido sin cerrar el contexto principal; SweetAlert se usa únicamente después de una creación exitosa.

## Mejoras sugeridas

- Evitar eliminar categorías con items activos.
- Agregar orden y visibilidad pública si el portal mostrará el catálogo agrupado.
