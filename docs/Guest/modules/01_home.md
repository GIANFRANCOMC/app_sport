# 01 - Inicio público

## Qué hace

Muestra información pública de la empresa, categorías y catálogo visible para clientes.

## Archivos

- Ruta: `routes/Guest/Home.php`
- Controlador: `Guest/HomeController`
- Servicio: `app/Services/Guest/GuestCatalogService.php`
- Modelos públicos: `App\Models\Guest\Company`, `Item` y `Category`
- Vista/Vue: `resources/views/Guest/general/home`, `resources/js/Guest/Pages/home`
- Tablas: `companies`, `company_socials_media`, `items`, `categories`, `categories_items`, `currencies`

## Reglas

- La empresa debe estar activa y se resuelve por slug dentro del tenant actual.
- Solo se consultan ítems activos con `see_my_web = true`.
- El precio se expone únicamente cuando `see_my_web_price = true`.
- Moneda y categorías se precargan para evitar consultas repetidas.
- Categorías públicas deben estar activas, visibles y ordenadas.
- El contrato excluye tokens externos, campos de auditoría y columnas internas que el visitante no necesita.

SEO, metadatos sociales y la presentación responsive por categorías permanecen como trabajo de interfaz en `docs/UI_UX_PENDING.md`.
