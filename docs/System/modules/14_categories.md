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

## Mejoras sugeridas

- Evitar eliminar categorías con items activos.
- Agregar orden y visibilidad pública si el portal mostrará el catálogo agrupado.
