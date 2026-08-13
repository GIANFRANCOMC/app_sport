# Navegación tenant

## Modelo visual

La navegación de cada tenant utiliza dos niveles permanentes en escritorio:

1. Barra primaria: muestra únicamente los módulos disponibles mediante iconos y tooltips.
2. Panel contextual: muestra las secciones y páginas del módulo activo.

La estructura visible se obtiene de los catálogos existentes de base de datos:

- `sections`: módulo principal.
- `menu_groups`: sección contextual.
- `sub_sections`: página o acción navegable.

`menu_categories` continúa disponible para ordenamiento y configuración interna, pero sus categorías no se muestran en la navegación.

## Permisos y módulos habilitados

`CompanySectionService` sigue siendo la única fuente del menú. Antes de renderizar, filtra:

- módulos y páginas activos;
- módulos habilitados para la empresa;
- permisos del rol;
- preferencias de visibilidad del usuario.

La vista no replica consultas ni reglas de autorización. Si el servicio no entrega una sección o página, la navegación no la muestra.

## Estado activo

La ruta actual es la fuente principal para determinar:

- módulo seleccionado;
- sección contextual;
- página actual.

La resolución se realiza en el servidor con el nombre almacenado en `sub_sections.dom_route`. Esto permite refrescar o abrir directamente cualquier URL sin depender del último clic.

Se conservan los `dom_id` y clases históricas para que los componentes Vue que todavía llaman a `Utils.navbarItem()` sigan funcionando. El estado activo principal se aplica únicamente a la página actual; los padres utilizan un tratamiento visual más tenue.

## Colapsado y responsive

- Escritorio expandido: barra primaria y panel contextual visibles con ancho fijo, sin cambios al pasar el cursor.
- Si el módulo activo solo ofrece una página, el icono navega directamente y el panel contextual no se renderiza.
- Escritorio colapsado: permanece únicamente la barra primaria; el hover no modifica su ancho.
- Tablet y móvil: la navegación completa funciona como drawer/offcanvas y conserva la jerarquía módulo, sección y página.

El botón existente del navbar continúa controlando el colapsado mediante la infraestructura del template y conserva su preferencia en `localStorage`.

## Archivos principales

- `resources/views/System/layouts/main.blade.php`: resolución y marcado accesible.
- `resources/css/System/br-branding/51-two-level-navigation.css`: diseño responsive de dos niveles.
- `app/Services/System/Organizations/Companies/CompanySectionService.php`: permisos y módulos disponibles.
- `database/seeders/SystemNavigationSeeder.php`: catálogo de módulos, secciones y páginas.

Después de modificar estilos ejecutar:

```bash
npm run build:css:system
```
