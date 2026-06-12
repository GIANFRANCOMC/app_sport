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

## Base técnica ya implementada

- `company_settings` admite valores tipados y configuraciones por empresa.
- `CompanySettingService` resuelve grupos y valores.
- `InternalCodeService` aplica los prefijos en backend.
- Productos, servicios, membresías, marcas, categorías, sucursales y activos consumen el mismo contrato.
- Un valor nulo o vacío desactiva el prefijo sin modificar código.

## Pendiente de interfaz

- Diseñar la pantalla dentro de Mi empresa.
- Definir permisos separados de lectura y edición.
- Invalidar únicamente los `ConfigService` que consuman el grupo modificado.
- Incorporar posteriormente otros grupos, como reglas de venta e inventario negativo, sin convertir `company_settings` en un contenedor de lógica no relacionada.
