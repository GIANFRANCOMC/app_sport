# 10 - Libro de reclamaciones y sugerencias

## Que hace

Permite administrar reclamos, quejas y sugerencias recibidas desde System o Guest.

## Archivos

- Ruta: `routes/System/Organizations/BookComplaint.php`
- Controlador: `System/Organizations/BookComplaintController`
- Servicios: `BookComplaintService`, `BookComplaintConfigService`
- Requests: `StoreBookComplaintRequest`, `UpdateBookComplaintRequest`
- Tabla: `book_complaints`

## Campos necesarios

- `company_id`
- `branch_id`
- `identity_document_type_id`
- `document_number`
- `name`
- `email`
- `phone_number`
- `type`
- `description`
- `request`
- `evidence`
- `admin_response`
- `status`

## Reglas

- Estados: `pending`, `in_progress`, `resolved`.
- Debe poder responderse desde System.
- Guest puede crear, System administra.

## Mejoras sugeridas

- Separar campos de respuesta interna vs datos publicos.
- Agregar fecha de respuesta.
- Agregar adjuntos multiples si se necesita evidencia.
- Agregar trazabilidad de cambios de estado.

