# 11 - Productos

## Que hace

Administra items de tipo `product`. Los productos pueden venderse y afectar stock.

## Archivos

- Ruta: `routes/System/Catalogs/Product.php`
- Controlador: `ProductController`
- Servicios: `ProductService`, `ProductConfigService`
- Requests: `StoreProductRequest`, `UpdateProductRequest`
- Tablas: `items`, `category_items`, `categories`, `warehouse_items`

## Campos necesarios

- `company_id`
- `currency_id`
- `internal_code`
- `name`
- `description`
- `price`
- `min_price`
- `max_price`
- `type = product`
- `see_my_web`
- `see_my_web_price`
- `status`

## Reglas

- Debe guardar `type = product`.
- Puede estar asociado a categorias.
- Al crearse puede requerir registros de stock por almacen.

## Mejoras sugeridas

- Validar stock minimo si se agrega.
- Evitar eliminar productos con ventas.
- Definir SKU/codigo de barras si se vendera por scanner.

