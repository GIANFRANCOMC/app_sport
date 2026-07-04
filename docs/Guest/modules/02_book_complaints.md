# 02 - Libro de reclamaciones público

## Backend

- Valida documento, contacto, tipo, detalle y pedido mediante `StoreBookComplaintRequest`.
- Limita solicitudes por minuto, día, empresa e IP; añade honeypot y Turnstile cuando `CAPTCHA_ENABLED=true`.
- Genera `tracking_code` aleatorio y único por empresa.
- Separa `admin_response` de `public_response`.
- Registra estado inicial en `book_complaint_status_histories`.
- Admite adjuntos múltiples mediante `book_complaint_attachments`.
- Guarda los adjuntos en el disco privado dentro del directorio tenant; una transacción fallida elimina los archivos ya escritos.
- `GET /book_complaints/attachments/{attachmentId}` permite descarga autenticada y filtrada por empresa.
- `GET /{company_slug}/book_complaints/status/{trackingCode}` expone únicamente estado, respuesta pública y fechas.
