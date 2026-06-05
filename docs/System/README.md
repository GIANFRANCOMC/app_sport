# System - Plataforma principal

`System` es la plataforma interna usada por empresas y sus usuarios autenticados. Representa la operacion principal del negocio: configuracion de empresa, usuarios, clientes, ventas, membresias, asistencias, catalogos, inventario, activos, biometricos, dashboard y reportes.

## Separacion de responsabilidad

System no debe asumir comportamiento de visitantes publicos. Si una funcionalidad esta pensada para clientes finales, enlaces publicos, formularios sin login o servicios expuestos por `company_slug`, debe documentarse y desarrollarse en `Guest`.

## Estructura real en codigo

- Controladores: `app/Http/Controllers/System`
- Modelos: `app/Models/System`
- Requests: `app/Http/Requests/System`
- Servicios: `app/Services/System`
- Rutas: `routes/System`
- Vistas Blade: `resources/views/System`
- Vue: `resources/js/System`

## Lectura recomendada

1. [ARCHITECTURE.md](ARCHITECTURE.md)
2. [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md)
3. [TABLES.md](TABLES.md)
4. [modules/00_menu_order.md](modules/00_menu_order.md)
5. Modulos numerados en [modules](modules)
6. Mejoras y pendientes en [new_requirements](new_requirements)

## Modulos por menu

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

