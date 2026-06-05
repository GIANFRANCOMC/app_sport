# 06 - Historial de cliente

## Que hace

Permite consultar ventas, membresias y asistencias de un cliente en un periodo.

## Archivos

- Ruta: `routes/System/Customers/TrackingCustomer.php`
- Controlador: `TrackingCustomerController`
- Servicio: `TrackingCustomerBusinessService`
- Vue: `resources/js/System/Pages/Customers/tracking_customers`
- Componentes: `TrackingCustomers/Timeline`, `Sales`, `Subscriptions`, `Attendances`
- Tablas: `customers`, `sales_header`, `subscriptions`, `attendances`

## Reglas

- Buscar cliente por id o numero de documento.
- Filtrar informacion por rango.
- Devolver solo informacion de la empresa actual.

## Campos relevantes

- `customer_id`
- `customer_document_number`
- `period_type`
- `options.information`

## Mejoras sugeridas

- Validar que ventas consultadas pertenezcan a sucursales de la empresa.
- Permitir rango personalizado.
- Agregar resumen financiero y de asistencia en cabecera.

