# Tipi primitivi

Codice ha cinque tipi primitivi. Ogni valore appartiene esattamente a uno di questi tipi — non esistono conversioni implicite tra di loro.

## Panoramica

| Tipo | Parola chiave | Esempio |
|------|--------------|---------|
| Stringa | `stringa` | `"ciao"` |
| Intero | `intero` | `42` |
| Decimale | `decimale` | `3.14` |
| Booleano | `booleano` | `vero`, `falso` |
| Nullo | `nullo` | `nullo` |

## stringa

Una sequenza di caratteri delimitata da virgolette doppie.

```cod
"Ciao, mondo!"
"Il numero è 42"
""
```

L'operatore `+` concatena due stringhe. L'operatore `*` ripete una stringa per un numero intero di volte.

```cod
stampa("ciao" + " " + "mondo")  → ciao mondo
stampa("ab" * 3)                 → ababab
```

## intero

Un numero intero senza parte decimale.

```cod
0
42
-7
```

## decimale

Un numero con parte decimale separata da un punto (`.`), non da una virgola.

```cod
3.14
-0.5
100.0
```

::: info Separatore decimale
L'italiano usa normalmente la virgola (`3,14`), ma Codice usa il punto (`3.14`) per evitare ambiguità nelle liste di argomenti come `stampa(3,14)`.
:::

## booleano

Un valore logico: `vero` (vero) o `falso` (falso).

```cod
vero
falso
```

## nullo

Rappresenta l'assenza di valore. È anche il valore restituito di default da qualsiasi chiamata a funzione.

```cod
nullo
```
