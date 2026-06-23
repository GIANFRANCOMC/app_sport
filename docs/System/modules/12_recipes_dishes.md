# 12 - Recetas y platillos

## Proposito

`recipes.index` agrega una capa operativa para restaurantes y negocios de comida dentro de Catalogo comercial. Un platillo sigue siendo un `item` vendible; la receta agrega la formula, toppings, extras, sabores e insumos que permiten proyectar consumo de inventario y preparar trazabilidad de cocina.

## Flujo actual

- El usuario selecciona el item vendible que representa el platillo.
- Define rendimiento y merma esperada.
- Registra insumos base del platillo, por ejemplo pollo, masa, queso o condimentos.
- Registra toppings/extras con precio adicional y, si corresponde, insumos propios.
- Registra sabores o variantes flexibles. Esto cubre casos como pizzas de varios sabores, donde el platillo tiene insumos base y cada sabor agrega su formula.

## Tablas asociadas

- `recipe_dishes`: cabecera de la receta asociada a `items`.
- `recipe_dish_components`: insumos base de la receta.
- `recipe_toppings`: catalogo operativo de toppings o extras.
- `recipe_dish_toppings`: relacion entre platillo y topping, con limites y defaults.
- `recipe_topping_components`: insumos consumidos por un topping.
- `recipe_dish_options`: sabores o variantes de un platillo.
- `recipe_dish_option_components`: insumos consumidos por cada sabor o variante.

## Reglas de negocio

- No se crea un nuevo `items.type`; la receta se apoya en un item existente para no romper ventas, POS, compras ni reportes.
- Los insumos deben pertenecer a la misma empresa.
- La cantidad de insumo debe ser mayor a cero.
- La merma esperada permite proyectar consumo real con tolerancia operativa.
- Los toppings pueden tener precio y consumo de insumos propio.
- Los sabores permiten distribuir componentes adicionales segun eleccion del cliente.

## Impacto en POS y ventas

La base de datos ya soporta formulas y opciones. El descuento automatico de insumos debe activarse cuando POS permita seleccionar receta, toppings y sabores vendidos. No se debe descontar una receta completa sin conocer las opciones elegidas, porque generaria kardex incorrecto.

## Cierre de caja principal e inventario fisico

Se agrega `cash_session_inventory_counts` para registrar conteos fisicos al cierre de caja principal. La caja principal no debe cerrar si existen cajas secundarias abiertas en la misma sucursal. Cuando el conteo real difiere del sistema, la diferencia debe generar un movimiento de inventario con origen `physical_count`, observacion y responsable.

## Mejoras sugeridas

- Integrar selector de toppings y sabores en Venta POS.
- Generar consumo de insumos al confirmar venta, agrupando por receta y opciones elegidas.
- Crear pantalla de conteo fisico en cierre de caja principal con busqueda por producto/codigo de barras.
- Agregar reporte de merma por dia, sucursal, caja y platillo.
- Permitir costos teoricos por receta usando `warehouse_items.average_cost`.
