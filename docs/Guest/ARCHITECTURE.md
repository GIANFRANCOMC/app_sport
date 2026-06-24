# Guest - Arquitectura

## Proposito

Guest expone funciones publicas de una empresa sin requerir login interno. La empresa se identifica por `company_slug`, y sus datos se cargan por middleware.

## Flujo base

1. La ruta recibe `{company_slug}`.
2. `company.exists` busca la empresa.
3. El controlador Guest usa `$request->get("company")`.
4. La vista Blade monta una pagina Vue de `resources/js/Guest`.
5. Las acciones publicas validan empresa y, si aplica, sucursal.

## Reutilizacion de System

Guest puede reutilizar servicios de negocio de System cuando la regla es la misma. Ejemplo: asistencia publica usa `TrackingAttendanceBusinessService`.

Regla: reutilizar no significa mezclar responsabilidades. Si el servicio de System depende de usuario autenticado, debe adaptarse o envolverlo con cuidado.

Las consultas públicas específicas de Guest deben vivir en servicios Guest. El catálogo visible se obtiene mediante `GuestCatalogService::publicItems($companyId)`, que aplica publicación, estado, moneda y orden sin reutilizar consultas internas de System.

## Seguridad publica

Guest debe tratar todo input como no confiable:

- Validar empresa desde slug, no desde request.
- Validar sucursal contra empresa.
- Usar FormRequest cuando se creen datos publicos.
- Aplicar rate limiting si hay formularios o endpoints de asistencia.
- Evitar exponer precios si `see_my_web_price` no esta activo.
- Evitar exponer informacion interna de clientes, usuarios, ventas o stock.

## Riesgos actuales

- Algunas rutas publicas reciben datos sensibles como asistencia sin FormRequest visible.
- `branch` puede venir codificado en base64; eso no equivale a seguridad.
- Si se habilitan endpoints biometricos publicos, deben tener autenticacion por token/firma.

## Pendientes y mejoras por realizar

- Mantener sincronizada esta arquitectura con `../GENERALIDADES.md` cuando cambien criterios transversales.
- Documentar endpoints publicos nuevos con su dependencia de `company_slug` y sus limites de exposicion.
