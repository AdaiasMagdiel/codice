# Codice

Codice is a small programming language with keywords in Italian. It has no real-world ambition — it exists purely as a study project, a way to learn how programming languages are built (lexer, parser, interpreter) while also practicing Italian vocabulary along the way.

Written in PHP, it is deliberately simple and unfinished in places. Expect rough edges.

## Example

```
stampa("Ciao, mondo!");
```

Running it:

```sh
$ php codice.php examples/ciao_mondo.cod
Ciao, mondo!
```

Arithmetic operators are supported too — `+`, `-`, `*`, `/`, unary `+`/`-`,
with the usual precedence and parentheses for grouping. `+` also
concatenates when either operand is a `stringa`, and `*` repeats a string
when the other operand is an `intero`. See
[examples/operations.cod](examples/operations.cod):

```sh
$ php codice.php examples/operations.cod
Somma: 3
Sottrazione: 7
Moltiplicazione: 12
Divisione: 2.5
Precedenza: 14
Parentesi: 20
Meno unario: 7
Concatenazione: ciao mondo
Ripetizione: ababab
```

## Usage

```sh
php codice.php [FILE]
```

- `FILE`: path to a `.cod` file to run. If omitted, it starts the Codice REPL.

```sh
php codice.php                          # Starts the REPL
php codice.php examples/ciao_mondo.cod  # Runs the hello world example
```

### REPL

```
$ php codice.php
>>> stampa("Ciao, mondo!");
Ciao, mondo!
>>> esci
Ciao!
```

Type `esci` (or press Ctrl+C) to exit. Each statement runs immediately
against the same environment, so state carries over between lines.

## Grammar

The current grammar is defined in [grammar.ebnf](grammar.ebnf).

### Decimal separator

Italian, like most languages, normally writes decimal numbers with a comma
(`3,14`). Codice uses a period instead (`3.14`). This isn't just to follow
the common programming convention — it also avoids ambiguity in contexts
where a comma is already a separator, such as argument lists (`stampa(3,14)`
would otherwise be indistinguishable from a two-argument call). The textual
representation of numbers keeps the period as well, even though a comma
would be the linguistically correct choice in Italian.

## Status

Early and experimental. Nothing here should be considered stable or production-ready.

The pipeline runs end to end now: the lexer tokenizes source code, the
parser builds an AST from it, and a tree-walking interpreter executes that
AST directly. Values are represented by a small runtime type system
(`App\Types`): `stringa` (string), `booleano` (`vero`/`falso`), `nullo`
(null — also the default return value of any function call), `intero`
(integer) and `decimale` (float, using `.` as the decimal separator — see
[Decimal separator](#decimal-separator)). Beyond literals, identifiers,
function calls — `stampa` (print) is the only builtin — and arithmetic
expressions (`+`, `-`, `*`, `/`, unary `+`/`-`), there are no variables, no
control flow, and no user-defined functions yet.

## Tests

```sh
composer test            # Run the test suite (Pest)
composer test:coverage   # Run with coverage report (requires Xdebug)
```

## License

Licensed under [GPL-3.0](LICENSE).
