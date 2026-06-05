# Guest - Portal publico

`Guest` representa las superficies publicas de Gympe. Esta orientado a visitantes, clientes finales de las empresas y servicios expuestos por System mediante URLs con `company_slug`.

## Separacion de responsabilidad

Guest no es el panel administrativo. No debe incluir reglas de administracion interna, configuracion avanzada ni operaciones que requieran usuario autenticado de empresa, salvo que reutilice servicios de System de forma controlada.

## Estructura real en codigo

- Controladores: `app/Http/Controllers/Guest`
- Modelos: `app/Models/Guest`
- Rutas: `routes/Guest`
- Vistas Blade: `resources/views/Guest`
- Vue: `resources/js/Guest`

## Rutas publicas actuales

- `{company_slug}/home`
- `{company_slug}/book_complaints`
- `{company_slug}/tracking_attendances`
- `{company_slug}/biometric_devices`

Todas dependen del middleware `company.exists`, que resuelve la empresa por slug.

## Lectura recomendada

1. [ARCHITECTURE.md](ARCHITECTURE.md)
2. [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md)
3. [TABLES.md](TABLES.md)
4. Modulos en [modules](modules)
5. Mejoras en [new_requirements](new_requirements)

