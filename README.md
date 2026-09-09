# Codice

Codice is a small programming language with keywords in Italian. It has no real-world ambition — it exists purely as a study project, a way to learn how programming languages are built (lexer, parser, interpreter) while also practicing Italian vocabulary along the way.

Written in PHP, it is deliberately simple and unfinished in places. Expect rough edges.

## Example

```
stampa("Ciao, mondo!");
```

## Usage

```sh
php codice.php [FILE]
```

- `FILE`: path to a `.cod` file to run. If omitted, it starts the (not yet implemented) Codice REPL.

```sh
php codice.php                          # Starts the REPL
php codice.php examples/ciao_mondo.cod  # Runs the hello world example
```

## Grammar

The current grammar is defined in [grammar.ebnf](grammar.ebnf).

## Status

Early and experimental. Nothing here should be considered stable or production-ready.

Only the lexer is implemented so far: source code is tokenized, but there is
no parser or evaluator yet, so nothing actually runs end to end.

## License

Licensed under [GPL-3.0](LICENSE).
