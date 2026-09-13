# Variables

Variables in Codice are declared with the `sia` keyword, followed by an identifier and a value. Constants use `cost` instead — see [Constants](#constants) below.

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

## Constants

A constant is declared with `cost` instead of `sia`. Unlike `sia`, an initial value is required — there's no such thing as an uninitialized constant:

```cod
cost PI = 3.14159265358979323846;
stampa(PI);  // → 3.1415926535898
```

Once declared, a constant can never be reassigned:

```cod
cost PI = 3.14;
PI = 3;  // Errore: Impossibile riassegnare la costante 'PI'.
```

`sia` and `cost` share the same namespace, so redeclaring a name already taken by either one — including a builtin — is an error, regardless of which keyword is used the second time:

```cod
sia x = 1;
cost x = 2;  // Errore: L'identificatore 'x' è già stato dichiarato.
```

::: tip
Aside from being reassignable, a constant behaves exactly like a variable: it can appear in expressions, be passed as an argument, and even hold a function.
:::

## Identifiers

An identifier is a sequence of letters, digits and underscores `_` that does not start with a digit. Snake case is the convention.

```cod
sia user_name = "luca";
sia age2      = 25;
sia _private  = nullo;
```
