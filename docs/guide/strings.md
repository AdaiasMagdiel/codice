# Strings

A `stringa` is a sequence of characters delimited by double quotes.

## Literals

```cod
"Ciao, mondo!";
"The value is 42";
"";
```

## Concatenation

The `+` operator concatenates two strings when at least one operand is a `stringa`.

```cod
sia greeting = "Hello" + ", " + "Marco!";
stampa(greeting);  // → Hello, Marco!
```

When one operand is not a string, it is converted automatically:

```cod
stampa("Age: " + 30);  // → Age: 30
stampa("Pi: " + 3.14);  // → Pi: 3.14
```

## Repetition

The `*` operator repeats a string when the other operand is an `intero`.

```cod
stampa("ab" * 3);  // → ababab
stampa("-" * 10);  // → ----------
```

::: tip
Repetition works in both directions: `"ab" * 3` and `3 * "ab"` produce the same result.
:::
