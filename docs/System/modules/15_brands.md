# 15 - Marcas

## Qué hace

Administra el catálogo de marcas comerciales de cada empresa. Una marca puede asociarse a muchos productos y cada producto puede tener como máximo una marca.

La relación elegida es uno a muchos:

- `brands` tiene muchas filas de `items` de tipo `product`.
- `items.brand_id` pertenece a una marca.
- La marca es opcional en Producto para conservar compatibilidad con registros existentes y permitir artículos genéricos.
- No se usa una relación múltiple porque una marca identifica normalmente al fabricante o identidad comercial principal del producto. Si en el futuro se necesitan fabricantes, líneas o marcas secundarias, deben modelarse como conceptos separados.

## Archivos

- Ruta: `routes/System/Catalogs/Brand.php`
- Controlador: `app/Http/Controllers/System/Catalogs/BrandController.php`
- Servicio: `app/Services/System/Catalogs/Brands/BrandService.php`
- Configuración: `app/Services/System/Catalogs/Brands/BrandConfigService.php`
- Request base: `app/Http/Requests/System/Catalogs/Brands/BrandRequest.php`
- Requests HTTP: `StoreBrandRequest`, `UpdateBrandRequest`
- Modelo: `app/Models/System/Catalogs/Brand.php`
- Vue: `resources/js/System/Pages/Catalogs/brands/main.vue`
- Entrada Vite: `resources/js/System/Pages/Catalogs/brands/main.js`
- Blade: `resources/views/System/general/Catalogs/brands/main.blade.php`
- Traducciones: `resources/lang/es/System/Catalogs/brand.php`
- Migración base: `database/migrations/2024_02_11_223124_create_init_companies_table.php`
- Registro de menú: `database/migrations/2024_01_11_223124_create_init_masters_table.php`
- Tablas: `brands`, `items`

## Tabla `brands`

Campos:

- `id`: identificador.
- `company_id`: empresa propietaria.
- `internal_code`: código interno; su unicidad dentro de la empresa se valida en backend.
- `name`: nombre comercial; su unicidad dentro de la empresa se valida en backend.
- `description`: descripción opcional de hasta 250 caracteres en la API.
- `status`: `active` o `inactive`.
- Auditoría: `created_at`, `created_by`, `updated_at`, `updated_by`.

Restricciones estructurales:

- Clave foránea `company_id -> companies.id` con eliminación en cascada.

La tabla `brands`, la relación `items.brand_id`, la subsección de menú y su habilitación inicial se consolidan en las migraciones base. En la etapa actual del proyecto, los cambios sobre tablas existentes se realizan directamente en esos archivos y se aplican mediante reinicio de migraciones.

## Relación con Productos

`items.brand_id` es nullable y referencia `brands.id`.

- Al eliminar una marca directamente en base de datos, `brand_id` pasa a `null` mediante `ON DELETE SET NULL`.
- El listado de Productos precarga `brand` para evitar consultas por fila.
- La búsqueda general de Productos también considera el nombre de la marca.
- El formulario de Producto muestra el selector de Marca en Datos y precio, inmediatamente antes de Estado.
- Solo se ofrecen marcas activas para nuevas asociaciones.
- Una marca inactiva ya asociada se conserva al editar el producto y se identifica como inactiva; no puede asignarse a otro producto.

## Validaciones

- La petición requiere un usuario autenticado con `company_id`.
- Código interno y nombre se recortan antes de validar.
- Las cadenas vacías se convierten a `null`.
- El código interno solo admite letras, números, punto, guion y guion bajo.
- Código interno y nombre son únicos por empresa mediante `UniqueInCompany` en el FormRequest.
- La tabla no declara restricciones únicas ni índices compuestos para estos campos; la regla permanece en backend para poder evolucionar sin acoplarla a MySQL.
- El estado solo admite `active` o `inactive`.
- El servicio vuelve a comprobar el `company_id` al editar, evitando actualizaciones cruzadas aunque se invoque fuera del controlador.
- El frontend valida campos obligatorios, pero el backend mantiene la autoridad final.

## Caché

Una mutación de Marca ejecuta:

```php
InitParamsCacheInvalidationService::invalidate(
    InitParamsCacheInvalidationService::BRANDS,
    $companyId
);
```

Esto limpia:

- `BrandConfigService`, por estados y configuración del módulo.
- `ProductConfigService`, porque el selector de marcas depende de este catálogo.

## Interfaz

- Barra de filtros reutilizable con búsqueda por todos los campos, código, nombre o descripción.
- Tabla con nombre, descripción, código interno copiable, cantidad de productos activos, estado y edición.
- Modal alineado con `br-entity-modal`, cierre reutilizable, botón Cancelar discreto y acciones diferenciadas para agregar o editar.
- Generación opcional de código interno con tooltip.
- Select de estado con el mismo comportamiento y menú flotante que el resto de System.

## Alta rápida reutilizable

- `resources/js/System/Components/Catalogs/AddBrand.vue` permite crear una Marca desde cualquier formulario sin abandonar el flujo actual.
- El prefijo se obtiene desde `company_settings`; `MAR-` es el valor predeterminado y el backend lo normaliza mediante `InternalCodeService`.
- La creación contextual utiliza una modal Bootstrap reutilizable y un SweetAlert de carga por encima de toda la interfaz.
- Reutiliza `QuickCreateCatalogEntity.vue`, conserva las reglas de longitud del backend y genera internamente un código válido con prefijo `MAR-`.
- El disparador admite los modos `link`, `button` e `icon`, con texto, icono, título, clases y estado deshabilitado configurables mediante propiedades.
- El formulario rápido solicita únicamente Nombre y Descripción; ambos campos aceptan Enter para registrar.
- Emite `created` con `{record, response}` y `postAction` con la respuesta completa. Productos usa el registro emitido para actualizar el selector sin sustituir la marca ya elegida.
- La ventana rápida usa un `dialog` nativo teletransportado a `body`; puede abrirse sobre Producto sin cerrar su modal ni perder datos.
- La Marca rápida se crea activa, mantiene sus errores dentro del formulario y muestra un SweetAlert success únicamente al completarse.
- Durante el registro se muestra el loader global para impedir otras acciones; los mensajes de campo son breves y no repiten el label.

## Criterios para crecer

- No agregar columnas específicas de proveedor a `brands`; proveedor y marca son conceptos distintos.
- Si una marca requiere logotipo, sitio web o país, añadir campos opcionales y actualizar migración, Request, servicio, Vue y documentación en el mismo cambio.
- Si se necesita una marca global compartida por empresas, crear un maestro separado y una relación explícita; no retirar `company_id` de esta tabla.
- Antes de implementar eliminación desde UI, definir si debe bloquearse cuando existen productos o si debe conservarse el comportamiento `SET NULL`.

## Mejoras pendientes

- Logotipo opcional con almacenamiento validado.
- País de origen y sitio web oficial.
- Historial de cambios o auditoría especializada.
- Pruebas de integración con base de datos para unicidad y aislamiento entre empresas.
