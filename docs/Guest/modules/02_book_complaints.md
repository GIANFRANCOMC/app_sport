# 02 - Libro de reclamaciones publico

## Que hace

Permite a visitantes registrar quejas, reclamos o sugerencias.

## Archivos

- Ruta: `routes/Guest/BookComplaint.php`
- Controlador: `Guest/BookComplaintController`
- Vista/Vue: `resources/views/Guest/general/book_complaints`, `resources/js/Guest/Pages/book_complaints`
- Tablas: `book_complaints`, `identity_document_types`, `branches`

## Campos necesarios

- Tipo y numero de documento.
- Nombre.
- Email y telefono opcionales segun validacion.
- Tipo: queja, reclamo o sugerencia.
- Descripcion.
- Solicitud.
- Evidencia si aplica.
- Datos tecnicos: IP, user agent, plataforma, navegador.

## Reglas

- Debe pertenecer a empresa y sucursal validas.
- Estado inicial recomendado: `pending`.
- No debe permitir modificar reclamos desde Guest.

## Mejoras sugeridas

- Agregar captcha/rate limiting.
- Enviar constancia por email.
- Permitir seguimiento publico con codigo seguro.

