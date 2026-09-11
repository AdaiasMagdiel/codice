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

Type `esci` (or press Ctrl+D) to exit. Each statement runs immediately
against the same environment, so state carries over between lines.

## Grammar

The current grammar is defined in [grammar.ebnf](grammar.ebnf).

## Status

Early and experimental. Nothing here should be considered stable or production-ready.

The pipeline runs end to end now: the lexer tokenizes source code, the
parser builds an AST from it, and a tree-walking interpreter executes that
AST directly. Values are represented by a small runtime type system
(`App\Types`): `stringa` (string), `booleano` (`vero`/`falso`), and `nullo`
(null — also the default return value of any function call). Beyond
literals, identifiers, and function calls — `stampa` (print) is the only
builtin — there are no variables, no control flow, and no user-defined
functions yet.

## Tests

```sh
composer test            # Run the test suite (Pest)
composer test:coverage   # Run with coverage report (requires Xdebug)
```

## License

Licensed under [GPL-3.0](LICENSE).
