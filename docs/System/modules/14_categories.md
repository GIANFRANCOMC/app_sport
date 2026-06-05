# 14 - Categorias

## Que hace

Organiza productos, servicios y membresias.

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
- Puede asociarse con multiples items.

## Mejoras sugeridas

- Evitar eliminar categorias con items activos.
- Agregar orden/visibilidad publica si el portal mostrara catalogo agrupado.

