# Configuración empresarial pendiente

## Estado

Pendiente.

## Objetivo

Crear una interfaz administrativa para `company_settings` dentro de Configuración / Mi empresa. La primera etapa permitirá mantener prefijos de productos, servicios, membresías, marcas, categorías, sucursales y activos.

Un valor nulo o vacío significa que el código interno se guarda sin prefijo.

## Crecimiento previsto

La misma infraestructura admitirá configuraciones tipadas de otros grupos, por ejemplo permitir o impedir ventas con stock negativo.

## Reglas

- No exponer `group` ni `key` al usuario final.
- Validar y normalizar valores en backend.
- Invalidar los `initParams` afectados al cambiar una configuración.
- Mantener auditoría con `created_by` y `updated_by`.
- Incorporar permisos específicos antes de habilitar la edición.
