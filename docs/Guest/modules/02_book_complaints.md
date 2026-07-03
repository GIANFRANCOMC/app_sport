# 02 - Libro de reclamaciones público

## Backend

- Valida documento, contacto, tipo, detalle y pedido mediante `StoreBookComplaintRequest`.
- Limita solicitudes por minuto, día, empresa e IP; añade honeypot.
- Genera `tracking_code` aleatorio y único por empresa.
- Separa `admin_response` de `public_response`.
- Registra estado inicial en `book_complaint_status_histories`.
- Admite adjuntos múltiples mediante `book_complaint_attachments`.
- `GET /{company_slug}/book_complaints/status/{trackingCode}` expone únicamente estado, respuesta pública y fechas.

Captcha, carga de adjuntos y consulta visual están centralizados en `docs/UI_UX_PENDING.md`.
