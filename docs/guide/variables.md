# Variables

Variables in Codice are declared with the `sia` keyword, followed by an identifier and a value.

## Declaration

```cod
sia name   = "Marco";
sia age    = 30;
sia pi     = 3.14;
sia active = vero;
```

A variable is created at the point of its first assignment. Codice infers the type automatically from the value — you never need to declare it explicitly.

## Inferred types

| Value | Inferred type | Keyword |
|-------|--------------|---------|
| `"text"` | String | `stringa` |
| `42` | Integer | `intero` |
| `3.14` | Float | `decimale` |
| `vero` / `falso` | Boolean | `booleano` |
| `nullo` | Null | `nullo` |

## Using variables

Variables can be used in any expression:

```cod
sia x = 10;
sia y = 3;

stampa(x + y);  // → 13
stampa(x * y);  // → 30
stampa(x / y);  // → 3.3333333333333
```

## Reassignment

A variable can be reassigned with `=`, as long as the new value has the same type:

```cod
sia counter = 0;
counter = counter + 1;
stampa(counter);  // → 1
```

::: warning Fixed types
A variable's type is set at declaration and cannot change. Assigning a value of a different type is an error.
:::

## Identifiers

An identifier is a sequence of letters, digits and underscores `_` that does not start with a digit. Snake case is the convention.

```cod
sia user_name = "luca";
sia age2      = 25;
sia _private  = nullo;
```
