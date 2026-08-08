# Convención de código PHP

## Objetivo

El código debe ser consistente, legible y fácil de revisar. La convención se aplica a `app`, `bootstrap`, `config`, `database`, PHP dentro de `resources`, `routes`, `scripts` y `tests`.

## Reglas

- Usar comillas dobles en cadenas PHP. Si la cadena contiene `$`, barras invertidas o comillas dobles, debe escaparse para conservar exactamente su valor.
- La llave de apertura permanece en la misma línea: `metodo() {`, `if(...) {`, `final class Servicio {`.
- Usar cuatro espacios y nunca tabulaciones.
- Separar con una línea en blanco bloques que cumplen responsabilidades diferentes: validación, consulta, mutación y construcción de respuesta.
- Mantener juntas las instrucciones que forman una sola operación. No insertar líneas vacías entre llamadas estrechamente relacionadas.
- Dividir expresiones largas por argumentos o por cada etapa encadenada cuando mejora la lectura; no fragmentar expresiones cortas.
- Los nombres técnicos permanecen en inglés y los textos visibles para el usuario en español.
- No realizar sustituciones textuales globales de comillas. El transformador usa tokens PHP para proteger interpolaciones y secuencias de escape.

## Automatización

Aplicar formato:

```bash
composer format:php
```

Validar sin modificar:

```bash
composer format:php-check
composer check:php-syntax
```

`scripts/format-php-double-quotes.php` normaliza literales de forma semánticamente segura y Pint aplica posición de llaves, espacios, imports y formato estructural según `pint.json`.
