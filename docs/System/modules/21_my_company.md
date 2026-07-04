# 21 - Mi empresa

## Que hace

Permite configurar datos de la empresa visibles interna y publicamente.

## Archivos

- Ruta: `routes/System/Organizations/Company.php`
- Controlador: `CompanyController`
- Servicios: `CompanyService`, `CompanyConfigService`, `CompanySectionService`, `TenantStoragePath`
- Tablas: `companies`, `company_socials_media`, `companies_sub_sections`

## Campos necesarios

- `slug`
- `internal_code`
- `identity_document_type_id`
- `currency_id`
- `document_number`
- `legal_name`
- `commercial_name`
- `tagline`
- `description`
- `address`
- `telephone`
- `email`
- `token_api_misc`
- imagenes de marca
- `status`

## Reglas

- Slug debe ser unico.
- Datos publicos alimentan Guest.
- Redes sociales deben mantenerse por tipo.
- `CompanySectionService` administra el menú habilitado y `CompanySubSectionObserver` invalida su caché automáticamente.

## Estado de mejoras

- El `slug` se trata como identificador estable del tenant y no forma parte del formulario de actualización ordinaria.
- Al reemplazar una imagen se elimina el archivo anterior después de guardar la nueva referencia.
- Los archivos de marca se guardan bajo `tenants/{tenant_slug}/...`; dos tenants con el mismo código interno no pueden sobrescribirse.
- La configuración operativa vive tipada en `company_settings`; los datos fiscales y públicos permanecen en `companies`.
- `GET|POST|PATCH /master-data/company-settings` administra claves tipadas bajo los permisos de Mi empresa; valida el valor, audita la mutación e invalida `initParams` dependientes.
