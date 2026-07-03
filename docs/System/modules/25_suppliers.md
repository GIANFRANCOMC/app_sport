# 25 - Proveedores

## Propósito

Mantiene los terceros comerciales usados en Compras. Cada proveedor pertenece a una empresa y puede conservar documento, contacto, teléfono, correo y dirección.

## Archivos principales

- Rutas: `routes/System/Purchases/Supplier.php`
- Controlador: `SupplierController`
- Validación: `StoreSupplierRequest`
- Servicio: `SupplierService`
- Modelo: `Supplier`
- Vista: `resources/js/System/Pages/Purchases/suppliers/main.vue`

## Reglas

- El nombre es obligatorio.
- El número de documento es opcional, pero no puede repetirse dentro de la empresa.
- El correo debe tener formato válido.
- Un proveedor inactivo permanece en el historial, pero no puede seleccionarse en una compra nueva.
- Crear o modificar proveedores invalida la configuración dependiente de Compras.
- El listado permite editar datos e inactivar proveedores sin eliminar su historial.

## Mejoras sugeridas

## Estado backend implementado

- `supplier_contacts` admite múltiples contactos y uno principal.
- `supplier_bank_accounts` admite varias cuentas, moneda y cuenta principal.
- El proveedor incorpora plazo de pago predeterminado y límite de crédito opcional.
- La interfaz y el resumen de desempeño están consolidados en `docs/UI_UX_PENDING.md`.
