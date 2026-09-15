# Math operations

Codice supports the fundamental arithmetic operators, plus comparison and logical operators, with standard operator precedence and grouping via parentheses.

## Operators

| Operator | Name | Example | Result |
|----------|------|---------|--------|
| `+` | Addition | `2 + 3` | `5` |
| `-` | Subtraction | `10 - 3` | `7` |
| `*` | Multiplication | `4 * 3` | `12` |
| `/` | Division | `5 / 2` | `2.5` |
| `%` | Modulo | `10 % 3` | `1` |
| `+x` | Unary plus | `+5` | `5` |
| `-x` | Unary minus | `-5` | `-5` |
| `++x` / `x++` | Increment (prefix / postfix) | `sia x = 1; x++;` | `1`, then `x` is `2` |
| `--x` / `x--` | Decrement (prefix / postfix) | `sia x = 1; x--;` | `1`, then `x` is `0` |

## Examples

```cod
stampa(2 + 3);  // → 5
stampa(10 - 3);  // → 7
stampa(4 * 3);  // → 12
stampa(5 / 2);  // → 2.5
stampa(10 % 3);  // → 1
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

## Modulo

`%` returns the remainder of the division and, unlike `/`, only accepts `intero` operands on both sides — a `decimale` (or any other type) raises a `TypeError`. Like `/`, dividing (taking the modulo) by zero raises a `DivisionByZeroError`.

```cod
stampa(10 % 3);  // → 1
stampa(9 % 3);  // → 0
stampa(-7 % 3);  // → -1

stampa(10 % 2.5);  // Errore: Atteso intero, ma trovato decimale.
stampa(10 % 0);  // Errore: Impossibile calcolare il resto della divisione per zero.
```

## Unary operators

```cod
sia x = 5;
stampa(-x);  // → -5
stampa(+x);  // → 5
stampa(-(-x));  // → 5
```

## Increment and decrement

`++` and `--` only work on a variable (an lvalue) — using them on a literal or any other expression raises a `RuntimeError`. Both come in a prefix and a postfix form, and the difference is in what value the expression itself evaluates to:

- **Prefix** (`++x`, `--x`) updates the variable first, then evaluates to the *new* value.
- **Postfix** (`x++`, `x--`) evaluates to the *current* value first, then updates the variable.

```cod
sia a = 5;
stampa(++a);  // → 6 (a is now 6)

sia b = 5;
stampa(b++);  // → 5 (b is now 6)
```

## Comparison and logical operators

Comparison operators (`<`, `>`, `<=`, `>=`) work between two `intero`/`decimale` (mixed freely) or two `stringa` (compared lexicographically) — mixing a `stringa` with a number raises a `TypeError`. They always return a `booleano`.

```cod
stampa(1 < 2);  // → vero
stampa(5 >= 5);  // → vero
stampa("a" < "b");  // → vero

stampa(1 < "a");  // Errore: Operatore '<' non applicabile tra intero e stringa.
```

`==` and `!=` work between any two values, but a value is **never** equal to one of a different type — not even `5` and `5.0`:

```cod
stampa(5 == 5);  // → vero
stampa(5 == 5.0);  // → falso (intero and decimale are different types)
stampa(5 == "5");  // → falso
stampa(nullo == nullo);  // → vero
```

`&&` and `||` combine `booleano` values (or anything with a [truthiness](/guide/conditionals#truthiness) rule) and both **short-circuit**: the right side is only evaluated when it can actually change the result.

```cod
stampa(vero && falso);  // → falso
stampa(falso || vero);  // → vero

falso && stampa("nunca eseguito");  // right side never runs
```

## Strings

The `+` operator concatenates strings when at least one operand is a `stringa`. The `*` operator repeats a string when the other operand is an `intero`.

```cod
stampa("ciao" + " " + "mondo");  // → ciao mondo
stampa("ab" * 3);  // → ababab
```

See [Strings](/guide/strings) for more details.
