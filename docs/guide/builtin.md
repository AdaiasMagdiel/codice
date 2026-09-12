# Built-in functions

Codice currently includes a single built-in function.

## stampa

Prints a value to standard output, followed by a newline.

**Syntax**

```cod
stampa(value);
```

**Examples**

```cod
stampa("Ciao, mondo!");  // → Ciao, mondo!
stampa(42);  // → 42
stampa(3.14);  // → 3.14
stampa(vero);  // → vero
stampa(nullo);  // → nullo
```

Variables and expressions are accepted as the argument:

```cod
sia x = 10;
stampa(x);  // → 10
stampa(x * 2 + 1);  // → 21
stampa("x is " + x);  // → x is 10
```

::: info
The return value of `stampa` is always `nullo`.
:::
