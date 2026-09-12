# Math operations

Codice supports the four fundamental arithmetic operators, with standard operator precedence and grouping via parentheses.

## Operators

| Operator | Name | Example | Result |
|----------|------|---------|--------|
| `+` | Addition | `2 + 3` | `5` |
| `-` | Subtraction | `10 - 3` | `7` |
| `*` | Multiplication | `4 * 3` | `12` |
| `/` | Division | `5 / 2` | `2.5` |
| `+x` | Unary plus | `+5` | `5` |
| `-x` | Unary minus | `-5` | `-5` |

## Examples

```cod
stampa(2 + 3);  // → 5
stampa(10 - 3);  // → 7
stampa(4 * 3);  // → 12
stampa(5 / 2);  // → 2.5
```

## Precedence

Multiplication and division take precedence over addition and subtraction — as in standard arithmetic.

```cod
stampa(2 + 3 * 4);  // → 14
stampa((2 + 3) * 4);  // → 20
```

Parentheses override any precedence.

## Division

Division always returns a `decimale`, even when both operands are integers.

```cod
stampa(10 / 4);  // → 2.5
stampa(9 / 3);  // → 3.0
```

## Unary operators

```cod
sia x = 5;
stampa(-x);  // → -5
stampa(+x);  // → 5
stampa(-(-x));  // → 5
```

## Strings

The `+` operator concatenates strings when at least one operand is a `stringa`. The `*` operator repeats a string when the other operand is an `intero`.

```cod
stampa("ciao" + " " + "mondo");  // → ciao mondo
stampa("ab" * 3);  // → ababab
```

See [Strings](/guide/strings) for more details.
