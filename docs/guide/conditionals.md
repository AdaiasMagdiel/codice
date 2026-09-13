# Conditionals

Codice branches with `se` ("if") and, optionally, `senon` ("else").

## se / senon

```cod
se (vero) {
	stampa("Questo blocco viene sempre eseguito.");
}

se (falso) {
	stampa("Non vedrai mai questo messaggio.");
} senon {
	stampa("La condizione era falsa.");  // → La condizione era falsa.
}
```

The condition must be parenthesized, and both `se` and `senon` require a `{ }` block — there's no single-statement form without braces.

## Else-if chains

`senon` followed directly by another `se` chains conditions, just like `else if` elsewhere:

```cod
se (falso) {
	stampa("Non entra qui.");
} senon se (vero) {
	stampa("Entra in questo ramo.");  // → Entra in questo ramo.
} senon {
	stampa("Non arriva mai qui.");
}
```

Each `senon se` is itself an `IfStatement` nested inside the previous one's `senon` branch, so the chain can be as long as needed, and the final `senon` (if present) still only runs when every condition above it was false.

## Truthiness

Any value can be used as a condition, not just `booleano`. Each type has its own rule for what counts as true or false:

| Type | Falso when | Vero when |
|------|-----------|-----------|
| `booleano` | `falso` | `vero` |
| `intero` | `0` | any other value |
| `decimale` | `0.0` | any other value |
| `stringa` | `""` (empty) | any non-empty string |
| `nullo` | always | never |

```cod
se (0) { stampa("sim"); } senon { stampa("nao"); }       // → nao
se (1) { stampa("sim"); }                                 // → sim
se ("") { stampa("sim"); } senon { stampa("nao"); }      // → nao
se ("ciao") { stampa("sim"); }                            // → sim
se (nullo) { stampa("sim"); } senon { stampa("nao"); }   // → nao
```

::: warning Functions have no truthiness — on purpose
A function value (`stampa` and any built-in) used directly as a condition is a type error, not an automatic `vero`:

```cod
se (stampa) { stampa("sim"); }
// Errore: 'stampa' è una funzione, non un valore booleano. Hai dimenticato di chiamarla con '()'?
```

Languages like Python and JavaScript treat every function as truthy, so `if (miaFunzione)` silently runs the branch even when the intent was `if (miaFunzione())`. That's a common typo — forgetting the call parentheses — and in those languages it fails silently: the branch that should depend on the function's *result* runs based on the function's mere *existence* instead, and nothing tells you your logic is wrong.

Codice raises a `TypeError` instead, pointing at the exact identifier and asking the one question that matters: did you forget to call it? The trade-off is explicit: one less implicit truthiness rule, in exchange for turning a silent logic bug into an error at the moment it happens.
:::

## Blocks and scope

Every `{ }` — whether it's the body of a `se`, a `senon`, or just a bare block — introduces its own scope. A variable declared with `sia` inside a block does not exist outside of it:

```cod
se (vero) {
	sia segreto = "solo qui dentro";
	stampa(segreto);  // → solo qui dentro
}

stampa(segreto);  // Errore: Identificatore 'segreto' non definito.
```

Declaring a name that already exists in an outer scope shadows it for the rest of the block, without touching the outer variable:

```cod
sia x = 1;

se (vero) {
	sia x = 2;
	stampa(x);  // → 2
}

stampa(x);  // → 1
```

Reassigning with `=` (no `sia`) behaves differently: since it doesn't declare anything, it reaches through nested blocks to find and update the variable wherever it was originally declared:

```cod
sia x = 1;

se (vero) {
	x = 2;
}

stampa(x);  // → 2
```

A bare block, with no `se` attached, is valid on its own and follows the same scoping rules:

```cod
{
	sia locale = "solo qui";
	stampa(locale);
}
```
