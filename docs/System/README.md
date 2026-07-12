# System - Plataforma Principal

`System` es la plataforma interna usada por empresas y sus usuarios autenticados. Representa la operacion principal del negocio: configuracion de empresa, usuarios, clientes, ventas, membresias, asistencias, catalogos, inventario, activos, biometricos, dashboard y reportes.

## Separacion De Responsabilidad

System no debe asumir comportamiento de visitantes publicos. Si una funcionalidad esta pensada para clientes finales, enlaces publicos, formularios sin login o servicios expuestos por `company_slug`, debe documentarse y desarrollarse en `Guest`.

## Estructura Real En Codigo

- Controladores: `app/Http/Controllers/System`
- Modelos: `app/Models/System`
- Requests: `app/Http/Requests/System`
- Servicios: `app/Services/System`
- Rutas: `routes/System`
- Vistas Blade: `resources/views/System`
- Vue: `resources/js/System`

## Lectura Recomendada

1. [ARCHITECTURE.md](ARCHITECTURE.md)
2. [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md)
3. [BACKEND_CONVENTIONS.md](BACKEND_CONVENTIONS.md)
4. [TABLES.md](TABLES.md)
5. [modules/00_menu_order.md](modules/00_menu_order.md)
6. Modulos numerados en [modules](modules)
7. Decisiones de evolucion en [new_requirements](new_requirements)

## Modulos Por Menu

El orden base sale del seed/menu adjunto:

1. Inicio
2. Dashboard
3. Ventas
4. Gestion de clientes
5. Catalogo comercial
6. Infraestructura
7. Configuracion
8. Reportes

Dentro de cada seccion, los archivos de `modules` siguen la numeracion del menu y luego agregan modulos tecnicos de soporte.

## Generalidades Transversales

Antes de tocar cualquier modulo System, revisar [../GENERALIDADES.md](../GENERALIDADES.md). Ese archivo concentra criterios compartidos de branding, formularios, modales, cache, migraciones, multiempresa y documentacion.

## Criterios De Mantenimiento

- Mantener la lista de modulos sincronizada con seeds de `sections`, `sub_sections`, perfiles y menu lateral.
- Cada modulo documenta tablas, rutas, reglas y comportamiento backend vigente.
- Evitar duplicar criterios visuales aqui; si aplica a varios modulos, moverlo a `GENERALIDADES.md`.
- Las decisiones visuales transversales se mantienen en `GENERALIDADES.md`; cada pantalla documenta sus mejoras en su módulo.
