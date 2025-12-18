# Sistema Frontend - Documentación

## Estructura Optimizada

Este sistema ha sido refactorizado para facilitar la creación de nuevos módulos y reducir la repetición de código.

## Archivos Principales

### Helpers

#### `ModuleConstants.js`
Constantes centralizadas para evitar repetición:
- Estados comunes (`STATUS`)
- Variantes de badges (`STATUS_BADGE_VARIANTS`)
- Opciones de filtrado (`FILTER_BY_OPTIONS`)
- Clases CSS (`CSS_CLASSES`)
- Textos comunes (`TEXT`)
- Y más...

#### `BaseCrudModule.js`
Clase base para módulos CRUD que proporciona:
- Inicialización de datos
- Métodos comunes (list, create, update)
- Manejo de formularios
- Validación básica

#### `CrudMixin.js`
Mixin Vue para funcionalidades CRUD comunes que puede ser usado en cualquier componente.

#### `ModuleFactory.js`
Factory para crear módulos fácilmente:
- `initCrudModule()` - Crea estructura inicial de módulo CRUD (legacy)
- `createModuleConfig()` - Crea configuración base
- `createFilterByOptions()` - Crea opciones de filtrado
- `initVueModule()` - Inicializa módulo Vue completo

#### `Forms.js`
Helpers para formularios (consolidado):
- `initFormData()` - Inicializa estructura de formulario
- `clearFormData()` - Limpia formularios
- `prepareFormData()` - Prepara datos antes de enviar
- `handleFormErrors()` - Maneja errores
- `validateFormData()` - Valida formulario completo
- `handleCreateUpdateResponse()` - Maneja respuestas

#### `ValidationHelpers.js`
Helpers de validación:
- `validateField()` - Valida un campo
- `validateForm()` - Valida formulario completo
- `CommonValidationRules` - Reglas comunes

#### `ComponentHelpers.js`
Helpers para componentes:
- `createTableActions()` - Crea acciones de tabla
- `confirmAction()` - Maneja confirmaciones

### Componentes Genéricos

#### `DataTable.vue`
Componente de tabla genérico con:
- Columnas configurables
- Acciones personalizables
- Paginación
- Loading states
- Slots para personalización

#### `StatusBadge.vue`
Componente de badge de estado con variantes automáticas.

#### `FormModal.vue`
Componente de modal para formularios con slots personalizables.

## Cómo Crear un Nuevo Módulo

### Opción 1: Usando ModuleFactory (Recomendado)

```javascript
import { initVueModule } from "@System/Helpers/ModuleFactory.js";
import * as Requests from "@System/Helpers/Requests.js";

export default initVueModule({
    entity: "mi_entidad",
    menuId: "menu-mi-entidad",
    pageTitle: "Mi Entidad",
    parentMenuId: "menu-parent",
    customRoutes: {
        customAction: Requests.config({entity: "mi_entidad", type: "customAction"})
    },
    defaultFormData: {
        name: "",
        description: ""
    },
    autoLoadList: true
});
```

### Opción 2: Usando BaseCrudModule

```javascript
import { BaseCrudModule } from "@System/Helpers/BaseCrudModule.js";

const module = new BaseCrudModule({
    entity: "mi_entidad",
    menuId: "menu-mi-entidad",
    pageTitle: "Mi Entidad"
});

export default {
    data() {
        return module.getVueConfig();
    },
    methods: {
        ...module.methods,
        // Métodos personalizados
    }
};
```

### Opción 3: Usando CrudMixin

```javascript
import { CrudMixin } from "@System/Helpers/CrudMixin.js";
import { createModuleConfig } from "@System/Helpers/ModuleFactory.js";

export default {
    mixins: [CrudMixin],
    data() {
        return createModuleConfig({
            entity: "mi_entidad",
            pageTitle: "Mi Entidad"
        });
    },
    methods: {
        // Métodos personalizados que extienden el mixin
    }
};
```

## Ejemplos de Uso

### Usar DataTable

```vue
<template>
    <DataTable
        :columns="tableColumns"
        :records="lists.entity.records.data"
        :loading="lists.entity.extras.loading"
        :actions="tableActions"
        :pagination-links="lists.entity.records.links"
        @action="handleTableAction"
        @pageChange="listEntity"/>
</template>

<script>
import DataTable from "@System/Components/Generics/DataTable.vue";

export default {
    components: { DataTable },
    data() {
        return {
            tableColumns: [
                {key: "id", label: "ID", style: {width: "10%"}},
                {key: "name", label: "Nombre", style: {width: "50%"}},
                {key: "status", label: "Estado", type: "badge", style: {width: "20%"}}
            ],
            tableActions: [
                {key: "edit", label: "Editar", icon: "fa fa-pencil", class: "btn-warning"},
                {key: "delete", label: "Eliminar", icon: "fa fa-trash", class: "btn-danger"}
            ]
        };
    },
    methods: {
        handleTableAction({action, record}) {
            if (action === "edit") {
                this.modalCreateUpdateEntity({record});
            } else if (action === "delete") {
                this.deleteEntity(record);
            }
        }
    }
};
</script>
```

### Usar StatusBadge

```vue
<template>
    <StatusBadge
        :status="record.status"
        :formatted-status="record.formatted_status"/>
</template>

<script>
import StatusBadge from "@System/Components/Generics/StatusBadge.vue";
</script>
```

### Usar FormModal

```vue
<template>
    <FormModal
        :modal-id="forms.entity.createUpdate.extras.modals.default.id"
        :title="forms.entity.createUpdate.extras.modals.default.titles[isUpdate ? 'update' : 'store']"
        @submit="createUpdateEntity">
        <template v-slot:body>
            <!-- Campos del formulario -->
        </template>
    </FormModal>
</template>

<script>
import FormModal from "@System/Components/Generics/FormModal.vue";
</script>
```

## Buenas Prácticas

1. **Usar constantes**: Siempre usar `ModuleConstants` en lugar de valores hardcodeados
2. **Reutilizar componentes**: Usar componentes genéricos cuando sea posible
3. **Usar helpers**: Aprovechar los helpers para evitar código repetitivo
4. **Validación centralizada**: Usar `ValidationHelpers` para validaciones
5. **Estructura consistente**: Seguir la estructura estándar de módulos CRUD

## Migración de Módulos Existentes

Para migrar un módulo existente:

1. Reemplazar valores hardcodeados con constantes de `ModuleConstants`
2. Usar `CrudMixin` para funcionalidades comunes
3. Reemplazar tablas personalizadas con `DataTable`
4. Usar `FormModal` para modales de formularios
5. Usar `StatusBadge` para badges de estado

## Notas

- Todos los helpers están documentados con JSDoc
- Los componentes genéricos son completamente personalizables mediante props y slots
- La estructura es extensible y fácil de mantener

