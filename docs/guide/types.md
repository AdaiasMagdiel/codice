# Primitive types

Codice has five primitive types. Every value belongs to exactly one of them — there are no implicit conversions between types.

## Overview

| Type | Keyword | Example |
|------|---------|---------|
| String | `stringa` | `"hello"` |
| Integer | `intero` | `42` |
| Float | `decimale` | `3.14` |
| Boolean | `booleano` | `vero`, `falso` |
| Null | `nullo` | `nullo` |

## stringa

A sequence of characters delimited by double quotes.

```cod
"Ciao, mondo!"
"The answer is 42"
""
```

The `+` operator concatenates two strings. The `*` operator repeats a string a given number of times.

```cod
stampa("ciao" + " " + "mondo")  → ciao mondo
stampa("ab" * 3)                 → ababab
```

## intero

A whole number without a decimal part.

```cod
0
42
-7
```

## decimale

A number with a decimal part, separated by a period (`.`), not a comma.

```cod
3.14
-0.5
100.0
```

::: info Decimal separator
Italian conventionally uses a comma (`3,14`), but Codice uses a period (`3.14`) to avoid ambiguity in argument lists like `stampa(3,14)`.
:::

## booleano

A logical value: `vero` (true) or `falso` (false).

```cod
vero
falso
```

## nullo

Represents the absence of a value. It is also the default return value of any function call.

```cod
nullo
```
