# Stringhe

Una `stringa` è una sequenza di caratteri delimitata da virgolette doppie.

## Letterali

```cod
"Ciao, mondo!"
"Il valore è 42"
""
```

## Concatenazione

L'operatore `+` concatena due stringhe quando almeno uno degli operandi è una `stringa`.

```cod
sia saluto = "Ciao" + ", " + "Marco!"
stampa(saluto)  → Ciao, Marco!
```

Se uno degli operandi non è una stringa, viene convertito automaticamente:

```cod
stampa("Età: " + 30)   → Età: 30
stampa("Pi: " + 3.14)  → Pi: 3.14
```

## Ripetizione

L'operatore `*` ripete una stringa quando l'altro operando è un `intero`.

```cod
stampa("ab" * 3)   → ababab
stampa("-" * 10)   → ----------
```

::: tip
La ripetizione funziona in entrambe le direzioni: `"ab" * 3` e `3 * "ab"` producono lo stesso risultato.
:::
