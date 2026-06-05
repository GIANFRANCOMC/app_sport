# 01 - Home publico

## Que hace

Muestra informacion publica de la empresa y catalogo visible.

## Archivos

- Ruta: `routes/Guest/Home.php`
- Controlador: `Guest/HomeController`
- Vista/Vue: `resources/views/Guest/general/home`, `resources/js/Guest/Pages/home`
- Tablas: `companies`, `company_socials_media`, `items`, `currencies`

## Reglas

- Mostrar datos de empresa resuelta por slug.
- Mostrar solo items con `see_my_web`.
- Ocultar precios si `see_my_web_price` no esta activo.

## Mejoras sugeridas

- Agregar SEO por empresa.
- Agregar categorias publicas.
- Validar imagenes y textos publicos incompletos.

