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

A variable can be reassigned with `=` (no `sia` this time) after it has been declared:

```cod
sia counter = 0;
counter = counter + 1;
stampa(counter);  // → 1
```

Like `sia`, `=` is itself an expression: it evaluates to the assigned value, so it can be chained to assign the same value to several variables at once.

```cod
sia a = 0;
sia b = 0;
a = b = 10;
stampa(a, " ", b);  // → 10 10
```

::: warning Must be declared first
`=` only reassigns — it never creates a variable. Assigning to an identifier that was never declared with `sia` is an error:

```cod
sconosciuta = 1;  // Errore: Identificatore 'sconosciuta' non definito.
```

There is also no fixed type: reassigning a value of a different type from the original one is allowed.
:::

Functions are values too, so a builtin like `stampa` can be assigned to a variable and called through it:

```cod
sia dire_ciao = stampa;
dire_ciao("Fantastico!");  // → Fantastico!
```

::: warning Shadowing builtins
Reassigning a builtin's name (`stampa = 5;`) succeeds — it just overwrites that name in the environment, like any other variable. Calling it afterwards fails, since the new value is no longer a function.
:::

## Identifiers

An identifier is a sequence of letters, digits and underscores `_` that does not start with a digit. Snake case is the convention.

```cod
sia user_name = "luca";
sia age2      = 25;
sia _private  = nullo;
```
