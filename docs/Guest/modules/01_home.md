# 01 - Home publico

## Que hace

Muestra informacion publica de la empresa y catalogo visible.

## Archivos

- Ruta: `routes/Guest/Home.php`
- Controlador: `Guest/HomeController`
- Servicio de catálogo: `app/Services/Guest/GuestCatalogService.php`
- Vista/Vue: `resources/views/Guest/general/home`, `resources/js/Guest/Pages/home`
- Tablas: `companies`, `company_socials_media`, `items`, `currencies`

## Reglas

- Mostrar datos de empresa resuelta por slug.
- Mostrar solo items activos con `see_my_web`.
- Precargar moneda y ordenar el catálogo por tipo y nombre desde `GuestCatalogService`.
- Ocultar precios si `see_my_web_price` no esta activo.

## Mejoras sugeridas

- Agregar SEO por empresa.
- Agregar categorias publicas.
- Validar imagenes y textos publicos incompletos.
