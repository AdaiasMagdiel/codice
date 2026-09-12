# What is Codice?

Codice is a small programming language with Italian keywords. It has no real-world ambition — it exists purely as a study project, a way to learn how programming languages are built (lexer, parser, interpreter) while also practicing Italian vocabulary along the way.

Written in PHP, it is deliberately simple and unfinished in places. Expect rough edges.

## First program

```cod
stampa("Ciao, mondo!");
```

```sh
$ php codice.php examples/ciao_mondo.cod
Ciao, mondo!
```

## How it works

Source code goes through three stages before being executed:

1. **Lexer** — transforms the source text into a flat sequence of tokens
2. **Parser** — builds an AST (abstract syntax tree) from those tokens
3. **Interpreter** — walks the AST and executes each node directly

## Current status

The project is in its early stages. The pipeline runs end-to-end: the lexer tokenizes source code, the parser builds an AST, and a tree-walking interpreter executes it.

Currently supported: literals, identifiers, variables, function calls (`stampa` is the only built-in), and arithmetic expressions.

::: info
Nothing here should be considered stable or production-ready.
:::
