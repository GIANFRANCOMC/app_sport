# 12 - Servicios

## Que hace

Administra items de tipo `service`. Los servicios pueden venderse, pero no afectan stock ni crean membresia.

## Archivos

- Ruta: `routes/System/Catalogs/Service.php`
- Controlador: `ServiceController`
- Servicios: `ServiceService`, `ServiceConfigService`
- Requests: `StoreServiceRequest`, `UpdateServiceRequest`
- Tablas: `items`, `category_items`, `categories`

## Campos necesarios

Los mismos de `items`, con `type = service`.

## Reglas

- Categorías y monedas de `initParams` se obtienen mediante los servicios de referencia compartidos.
- No debe descontar stock.
- No debe crear membresía real.
- Puede mostrarse en portal público según `see_my_web`.
- Al crear o editar un servicio se invalida el recurso compartido `items`, actualizando también los artículos disponibles en Ventas.
- Al crear o editar una categoría se limpia la caché de `ServiceConfigService`, por lo que el selector se actualiza en la siguiente carga.

## Mejoras sugeridas

- Permitir duración estimada del servicio si el negocio agenda citas.
- Definir si servicios pueden tener comisiones por vendedor.

## Configuración y validación compartida

- El código interno usa `company_settings.internal_code_prefixes.service`; `SER` es el valor inicial.
- Vue presenta `SER-` como addon integrado y permite editar únicamente la parte variable. `InternalCodeService` aplica el valor definitivo en backend.
- Si la configuración de la empresa es nula o vacía, el servicio se guarda sin prefijo y el addon no se muestra.
- Store y Update extienden `CompanyFormRequest`, normalizan cadenas y aplican `AppliesInternalCodePrefix`.
- Los errores bajo cada campo son breves. El resumen de validación agrega el nombre del campo tanto para errores frontend como para respuestas HTTP `422`.

## Actualizacion: IGV incluido

- Los servicios usan `items.price_includes_tax` igual que productos y membresias.
- En el formulario se muestra `Precio incluye IGV`, activo por defecto.
- Cuando se vende un servicio con IGV incluido, los impuestos configurados de venta no aumentan el total de ese detalle.
- Cuando se vende un servicio sin IGV incluido, todos los impuestos activos de alcance `sale` o `both` se calculan sobre el precio del detalle.
