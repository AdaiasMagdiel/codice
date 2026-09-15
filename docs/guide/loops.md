# Loops

Codice repeats a block with `per` ("for") — a setup, a test and an update, separated by `;`, exactly like a C-style `for`.

## per

```cod
per (sia i = 0; i < 5; i++) {
	stampa(i);
}
// → 0
// → 1
// → 2
// → 3
// → 4
```

The three parts run in this order on every iteration: **test** → **body** → **update**. The setup runs once, before the loop starts.

- **setup** — usually a `sia` declaration; it runs once, before the first test.
- **test** — checked before every iteration (including the first); the loop stops as soon as it's falsy, using the same [truthiness rules](/guide/conditionals#truthiness) as `se`.
- **update** — runs after the body, before the next test.

## Every part is optional

All three clauses can be omitted, but the `;` separators are always required:

```cod
sia i = 0;
per (; i < 3; i++) {
	stampa(i);
}
```

Omitting the test makes it default to `vero`, so the loop repeats forever unless something inside it stops it another way:

```cod
per (;;) {
	stampa("questo ciclo non finisce mai");
}
```

::: warning No break or continue yet
Codice has no `interrompi`/`continua` (break/continue) equivalent. An infinite loop like the one above has no way to stop from the inside — avoid it unless you really mean it.
:::

## Scope

`per` opens its own scope for the setup, shared across every iteration — so a variable declared there is visible in the test, the update, and the body:

```cod
per (sia i = 0; i < 3; i++) {
	stampa(i);
}

stampa(i);  // Errore: Identificatore 'i' non definito.
```

The body itself is a regular `{ }` block, so it gets a **fresh** scope on every iteration, nested inside the loop's own scope:

```cod
per (sia i = 0; i < 3; i++) {
	sia doppio = i * 2;
	stampa(doppio);
}

stampa(doppio);  // Errore: Identificatore 'doppio' non definito.
```

## Nesting

Loops can be nested freely — each `per` has its own scope, so the outer loop's variable stays visible inside the inner one:

```cod
per (sia riga = 0; riga < 2; riga++) {
	per (sia colonna = 0; colonna < 2; colonna++) {
		stampa("(", riga, ", ", colonna, ")");
	}
}
// → (0, 0)
// → (0, 1)
// → (1, 0)
// → (1, 1)
```

## FizzBuzz

Combining `per` with [comparison and logical operators](/guide/operations#comparison-and-logical-operators) is enough for a real program. See [examples/fizzbuzz.cod](https://github.com/AdaiasMagdiel/codice/blob/main/examples/fizzbuzz.cod):

```cod
per (sia i = 0; i < 1000; i++) {
	se (i % 3 == 0 && i % 5 == 0) {
		stampa("FizzBuzz");
	} altrimenti se (i % 3 == 0) {
		stampa("Fizz");
	} altrimenti se (i % 5 == 0) {
		stampa("Buzz");
	} altrimenti {
		stampa(i);
	}
}
```
