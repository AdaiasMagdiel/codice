# Comments

Codice supports single-line and multi-line comments. They are ignored by the lexer and have no effect on execution.

## Single-line

Use `//` or `#` — both do the same thing.

```cod
// This is a single-line comment.
# This does the same thing.

stampa("Ciao!"); // Comments can also follow code.
```

## Multi-line

Use `/* ... */` to span multiple lines.

```cod
/*
A multi-line comment
looks like this.
*/

stampa /* even in the middle of code */ ("Arrivederci!");
```

::: warning Unterminated comments
A `/*` without a matching `*/` is a lexer error.
:::
