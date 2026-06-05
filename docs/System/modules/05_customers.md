# 05 - Clientes

## Que hace

Administra los clientes de una empresa. Un cliente puede comprar, recibir membresias, registrar asistencias y asociarse con huellas biometricas.

## Archivos

- Ruta: `routes/System/Customers/Customer.php`
- Controlador: `CustomerController`
- Requests: `StoreCustomerRequest`, `UpdateCustomerRequest`
- Servicios: `CustomerService`, `CustomerConfigService`
- Modelo: `Customer`
- Vista/Vue: `resources/views/System/general/Customers/customers`, `resources/js/System/Pages/Customers/customers`
- Tablas: `customers`, `identity_document_types`, `subscriptions`, `customer_biometric_fingerprints`

## Campos necesarios

- `company_id`
- `identity_document_type_id`
- `document_number`
- `name`
- `email`
- `phone_number`
- `gender`
- `birthdate`
- `status`

## Reglas

- El cliente pertenece a una empresa.
- Documento y tipo de documento deben ser consistentes con longitudes del maestro.
- El registro de huella se hace contra un dispositivo biometrico existente.
- No debe mezclarse con usuarios internos.

## Mejoras sugeridas

- Agregar validacion de unicidad por empresa + tipo documento + numero.
- Mejorar busqueda por documento/nombre/telefono.
- Agregar contacto de emergencia y observaciones medicas si el negocio lo necesita.
- Agregar tests de creacion/edicion multiempresa.

