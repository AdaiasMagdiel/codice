# The REPL

The Codice REPL (Read-Eval-Print Loop) lets you run statements interactively, one at a time.

## Starting

```sh
php codice.php
```

## Usage

```
$ php codice.php
>>> stampa("Ciao, mondo!")
Ciao, mondo!
>>> sia x = 10
>>> stampa(x + 5)
15
>>> esci
Ciao!
```

Each statement runs immediately in the same environment, so state — variables and values — carries over between lines.

## Exiting

| Method | Description |
|--------|-------------|
| `esci` | Explicit exit command |
| `Ctrl+C` | Process interrupt |

::: tip
The REPL is the ideal place to experiment. Try expressions, assign variables, call `stampa` — everything is evaluated immediately.
:::
