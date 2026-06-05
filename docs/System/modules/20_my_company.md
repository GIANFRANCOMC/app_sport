# 20 - Mi empresa

## Que hace

Permite configurar datos de la empresa visibles interna y publicamente.

## Archivos

- Ruta: `routes/System/Organizations/Company.php`
- Controlador: `CompanyController`
- Servicios: `CompanyService`, `CompanyConfigService`, `CompanySectionService`
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

## Mejoras sugeridas

- Validar slug con formato estable.
- Limpiar imagenes antiguas al reemplazar.
- Separar configuracion publica, fiscal y operativa.

